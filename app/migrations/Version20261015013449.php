<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015013449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a tabela Livro_Assunto (N:N entre Livro e Assunto)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Livro_Assunto (Livro_CodI INT NOT NULL, Assunto_codAs INT NOT NULL, INDEX IDX_2F01B74315E287E (Livro_CodI), INDEX IDX_2F01B743A5E1B302 (Assunto_codAs), PRIMARY KEY (Livro_CodI, Assunto_codAs)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE Livro_Assunto ADD CONSTRAINT FK_2F01B74315E287E FOREIGN KEY (Livro_CodI) REFERENCES Livro (CodI)');
        $this->addSql('ALTER TABLE Livro_Assunto ADD CONSTRAINT FK_2F01B743A5E1B302 FOREIGN KEY (Assunto_codAs) REFERENCES Assunto (codAs)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro_Assunto DROP FOREIGN KEY FK_2F01B74315E287E');
        $this->addSql('ALTER TABLE Livro_Assunto DROP FOREIGN KEY FK_2F01B743A5E1B302');
        $this->addSql('DROP TABLE Livro_Assunto');
    }
}
