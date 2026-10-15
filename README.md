# TJJUD — Sistema de Biblioteca

Sistema de cadastro e consulta de **Livros**, **Autores** e **Assuntos** para uma biblioteca, com relatório gerencial (tela e PDF). Desenvolvido como teste técnico.

**Stack:** Symfony 7.4 · Doctrine ORM · MySQL 8.4 · Bootstrap 5 · PHPUnit — tudo rodando em **Docker** (Nginx + PHP-FPM 8.4). Não é necessário ter PHP, Composer ou Symfony instalados na máquina: só o Docker.

## Como rodar

**Pré-requisito:** Docker instalado e rodando — Docker Desktop no Windows/macOS, ou Docker Engine + Docker Compose no Linux.

> No Windows, o Docker Desktop requer o WSL2 habilitado. Se a instalação falhar pedindo a feature "Virtual Machine Platform", abra um PowerShell **como administrador**, rode `wsl --install`, reinicie e reinstale o Docker Desktop.

```bash
docker compose up -d --build
```

Na primeira subida o próprio container monta o projeto Symfony, instala as dependências, cria o banco e aplica todas as migrations automaticamente — demora ~1-2 min. Acompanhe pelos logs:

```bash
docker compose logs -f php
```

Quando terminar, acesse:

| Serviço | URL | Credenciais |
|---|---|---|
| **Aplicação** | http://localhost:8080 | — |
| Adminer (DB GUI) | http://localhost:8081 | servidor `database` · user/senha `symfony`/`symfony` · banco `tjjud` |
| MySQL (externo) | `localhost:3306` | user/senha `symfony`/`symfony` · banco `tjjud` · root `root`/`root` |

Para popular o banco com dados de exemplo (10 autores, 10 assuntos, 20 livros):

```bash
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
├── docker-compose.yml
├── docker-entrypoint.sh       # Script executado no boot do container PHP (composer install,
│                              # aguarda o banco, aplica migrations em dev e test)
└── Makefile                   # Atalhos (opcional, requer `make`)
```

## Comandos úteis

```bash
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

Com `make` disponível (Git Bash/WSL/Linux/macOS): `make up`, `make sh`, `make migrate`, `make mysql`, `make logs` — veja o `Makefile`.

## Decisões técnicas

- **Symfony 7.4 (LTS)** — versão com suporte de longo prazo, escolhida deliberadamente em vez da `7.x` mais recente.
- **Camada de serviço**: cada entidade tem um `Service` (`App\Service\{Livro,Autor,Assunto,Relatorio}Service`) que concentra toda a regra de negócio — persistência, paginação, proteção contra exclusão de registros vinculados, tratamento de erros de banco. Os `Controller`s ficam finos: só lidam com HTTP (formulário, CSRF, flash messages, redirecionamento).
- **Relatório via VIEW do banco**: `vw_relatorio_livros_por_autor` é criada por migration (não é uma entidade Doctrine) e consultada via DBAL puro (`Connection::fetchAllAssociative`), deliberadamente bypassando o ORM só nesse ponto de leitura. Um livro com múltiplos autores aparece uma vez sob cada autor, por design.
- **PDF com Dompdf**: o relatório em PDF usa um template Twig HTML independente (sem Bootstrap/CDN, já que o Dompdf não busca estilos externos de forma confiável) renderizado para HTML e convertido pelo Dompdf — sem depender de um serviço externo de geração de PDF.
- **Validação de unicidade só na camada de aplicação**: duplicidade de nome/título é bloqueada via `#[UniqueEntity]` do Symfony Validator, sem índice `UNIQUE` no banco. Isso é intencional — o desafio pede para seguir o modelo de dados do diagrama original integralmente, e um índice de unicidade é regra de negócio, não ajuste de performance.
- **Testes com Foundry + DAMA Doctrine Test Bundle**: as factories (`App\Factory\*`) geram dados de teste realistas (Faker, locale `pt_BR`), e cada teste roda isolado numa transação revertida ao final, sem precisar recriar o schema a cada execução.

## Scripts de implantação

- **Migrations**: cada mudança de schema tem sua própria migration incremental em `app/migrations/` (uma tabela/alteração por arquivo), aplicadas automaticamente no boot do container (`docker-entrypoint.sh`) tanto no banco de desenvolvimento (`tjjud`) quanto no de teste (`tjjud_test`).
- **A VIEW do relatório** é criada pela migration `app/migrations/Version20261015020500.php` — não existe script manual separado para isso, ela nasce junto com o restante do schema.
- **Recriar o banco do zero**: `docker compose down -v` (apaga o volume do MySQL) seguido de `docker compose up -d --build` recria o container do banco vazio e reaplica todas as migrations automaticamente, sem passo manual.
- **Dados de exemplo**: `docker compose exec php php bin/console doctrine:fixtures:load --no-interaction` popula o banco via as factories do Foundry (ver "Como rodar" acima).

## Observações

- O container do MySQL expõe a porta `3306`, então também é possível conectar via DBeaver/TablePlus/HeidiSQL, não só pelo Adminer.
- Para trocar a versão do PHP ou do MySQL, edite `docker/php/Dockerfile` e o `image:` do serviço `database` no `docker-compose.yml`.
