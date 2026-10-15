<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015013026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a tabela Livro';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Livro (CodI INT AUTO_INCREMENT NOT NULL, Titulo VARCHAR(40) NOT NULL, Editora VARCHAR(40) NOT NULL, Edicao INT NOT NULL, AnoPublicacao VARCHAR(4) NOT NULL, PRIMARY KEY (CodI)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Livro');
    }
}
