<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015020500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a view vw_relatorio_livros_por_autor para o relatório de livros agrupados por autor';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE VIEW vw_relatorio_livros_por_autor AS
            SELECT
                a.CodAu AS autor_codigo,
                a.Nome AS autor_nome,
                l.CodI AS livro_codigo,
                l.Titulo AS livro_titulo,
                l.Editora AS livro_editora,
                l.Edicao AS livro_edicao,
                l.AnoPublicacao AS livro_ano,
                l.Valor AS livro_valor,
                GROUP_CONCAT(DISTINCT s.Descricao ORDER BY s.Descricao SEPARATOR ', ') AS livro_assuntos
            FROM Autor a
            INNER JOIN Livro_Autor la ON la.Autor_CodAu = a.CodAu
            INNER JOIN Livro l ON l.CodI = la.Livro_CodI
            LEFT JOIN Livro_Assunto ls ON ls.Livro_CodI = l.CodI
            LEFT JOIN Assunto s ON s.codAs = ls.Assunto_codAs
            GROUP BY a.CodAu, a.Nome, l.CodI, l.Titulo, l.Editora, l.Edicao, l.AnoPublicacao, l.Valor
            ORDER BY a.Nome, l.Titulo
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW vw_relatorio_livros_por_autor');
    }
}
