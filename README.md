# TJJUD — Sistema de Biblioteca

Sistema de cadastro e consulta de **Livros**, **Autores** e **Assuntos** para uma biblioteca, com relatório gerencial (tela e PDF). Desenvolvido como teste técnico.

**Stack:** Symfony 7.4 · Doctrine ORM · MySQL 8.4 · Bootstrap 5 · PHPUnit — tudo rodando em **Docker** (Nginx + PHP-FPM 8.4). Não é necessário ter PHP, Composer ou Symfony instalados na máquina: só o Docker.

## Como rodar

**Pré-requisito:** Docker Desktop instalado e rodando, com WSL2 habilitado.

> Se a instalação do Docker Desktop falhar pedindo a feature "Virtual Machine Platform", abra um PowerShell **como administrador**, rode `wsl --install`, reinicie e reinstale o Docker Desktop.

```powershell
docker compose up -d --build
```

Na primeira subida o próprio container monta o projeto Symfony, instala as dependências, cria o banco e aplica todas as migrations automaticamente — demora ~1-2 min. Acompanhe pelos logs:

```powershell
docker compose logs -f php
```

Quando terminar, acesse:

| Serviço | URL | Credenciais |
|---|---|---|
| **Aplicação** | http://localhost:8080 | — |
| Adminer (DB GUI) | http://localhost:8081 | servidor `database` · user/senha `symfony`/`symfony` · banco `tjjud` |
| MySQL (externo) | `localhost:3306` | user/senha `symfony`/`symfony` · banco `tjjud` · root `root`/`root` |

Para popular o banco com dados de exemplo (10 autores, 10 assuntos, 20 livros):

```powershell
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

Para parar tudo: `docker compose down` (adicione `-v` para também apagar o volume do MySQL).

## Funcionalidades

- **CRUD completo** de Livro, Autor e Assunto (listar, criar, ver, editar, excluir), seguindo o modelo de dados original (relacionamentos N:N Livro↔Autor e Livro↔Assunto).
- Campo de **Valor (R$)** no livro, com máscara de moeda no formulário e exibição formatada (`R$ 1.234,56`).
- **Busca** e **paginação** (10/20/50 por página) nas três listagens.
- Validações de negócio: campos obrigatórios, ano com 4 dígitos, valor não-negativo, nomes/títulos duplicados, e bloqueio de exclusão de Autor/Assunto com livros vinculados.
- **Relatório** de livros agrupados por autor (tela e exportação em **PDF**), consultando uma *view* do banco (`vw_relatorio_livros_por_autor`) — um livro com múltiplos autores aparece corretamente sob cada um deles, com subtotal por autor.
- Interface em Bootstrap 5 com CSS próprio (cores, tamanhos e ícones customizados), modal de confirmação de exclusão, seleção múltipla com busca (Tom Select).
- Suíte de testes automatizados (PHPUnit) cobrindo services, controllers, relatório e paginação.

## Estrutura do projeto

```
TJJUD/
├── app/                      # Projeto Symfony completo (entidades, controllers, services,
│                              # forms, migrations, templates, testes)
├── docker/
│   ├── php/                  # Dockerfile + php.ini do PHP-FPM
│   └── nginx/                 # Configuração do Nginx
├── skeleton/                  # Arquivos base copiados para app/ apenas num bootstrap do zero
├── docker-compose.yml
├── docker-entrypoint.sh       # Script que monta/atualiza o projeto Symfony no boot do container
└── Makefile                   # Atalhos (opcional, requer `make`)
```

## Comandos úteis

```powershell
# Shell no container PHP
docker compose exec php sh

# Console do Symfony
docker compose exec php php bin/console <comando>

# Criar/aplicar migrations
docker compose exec php php bin/console make:migration
docker compose exec php php bin/console doctrine:migrations:migrate

# Rodar a suíte de testes
docker compose exec php php bin/phpunit

# Rodar um teste específico
docker compose exec php php bin/phpunit --filter=testNomeDoMetodo

# Limpar cache
docker compose exec php php bin/console cache:clear
```

Com `make` disponível (Git Bash/WSL): `make up`, `make sh`, `make migrate`, `make mysql`, `make logs` — veja o `Makefile`.

## Observações

- O container do MySQL expõe a porta `3306`, então também é possível conectar via DBeaver/TablePlus/HeidiSQL, não só pelo Adminer.
- Para trocar a versão do PHP ou do MySQL, edite `docker/php/Dockerfile` e o `image:` do serviço `database` no `docker-compose.yml`.
