<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015013109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a tabela Assunto';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Assunto (codAs INT AUTO_INCREMENT NOT NULL, Descricao VARCHAR(20) NOT NULL, PRIMARY KEY (codAs)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Assunto');
    }
}
