<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015012825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a tabela Autor';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Autor (CodAu INT AUTO_INCREMENT NOT NULL, Nome VARCHAR(40) NOT NULL, PRIMARY KEY (CodAu)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Autor');
    }
}
