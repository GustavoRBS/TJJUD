<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015180926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomeia a PK de Livro de CodI para CodL (conferindo o diagrama original: segue o mesmo padrão de CodAu/codAs, derivado do próprio nome da entidade) e recria a view do relatório com as novas colunas';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro RENAME COLUMN CodI TO CodL');
        $this->addSql('ALTER TABLE Livro_Autor RENAME COLUMN Livro_CodI TO Livro_CodL');
        $this->addSql('ALTER TABLE Livro_Assunto RENAME COLUMN Livro_CodI TO Livro_CodL');

        $this->addSql('ALTER TABLE Livro_Autor RENAME INDEX idx_4129394115e287e TO IDX_412939417134DCF1');
        $this->addSql('ALTER TABLE Livro_Assunto RENAME INDEX idx_2f01b74315e287e TO IDX_2F01B7437134DCF1');

        $this->addSql('DROP VIEW vw_relatorio_livros_por_autor');
        $this->addSql(<<<'SQL'
            CREATE VIEW vw_relatorio_livros_por_autor AS
            SELECT
                a.CodAu AS autor_codigo,
                a.Nome AS autor_nome,
                l.CodL AS livro_codigo,
                l.Titulo AS livro_titulo,
                l.Editora AS livro_editora,
                l.Edicao AS livro_edicao,
                l.AnoPublicacao AS livro_ano,
                l.Valor AS livro_valor,
                GROUP_CONCAT(DISTINCT s.Descricao ORDER BY s.Descricao SEPARATOR ', ') AS livro_assuntos
            FROM Autor a
            INNER JOIN Livro_Autor la ON la.Autor_CodAu = a.CodAu
            INNER JOIN Livro l ON l.CodL = la.Livro_CodL
            LEFT JOIN Livro_Assunto ls ON ls.Livro_CodL = l.CodL
            LEFT JOIN Assunto s ON s.codAs = ls.Assunto_codAs
            GROUP BY a.CodAu, a.Nome, l.CodL, l.Titulo, l.Editora, l.Edicao, l.AnoPublicacao, l.Valor
            ORDER BY a.Nome, l.Titulo
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW vw_relatorio_livros_por_autor');
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

        $this->addSql('ALTER TABLE Livro_Assunto RENAME INDEX idx_2f01b7437134dcf1 TO IDX_2F01B74315E287E');
        $this->addSql('ALTER TABLE Livro_Autor RENAME INDEX idx_412939417134dcf1 TO IDX_4129394115E287E');

        $this->addSql('ALTER TABLE Livro_Assunto RENAME COLUMN Livro_CodL TO Livro_CodI');
        $this->addSql('ALTER TABLE Livro_Autor RENAME COLUMN Livro_CodL TO Livro_CodI');
        $this->addSql('ALTER TABLE Livro RENAME COLUMN CodL TO CodI');
    }
}
