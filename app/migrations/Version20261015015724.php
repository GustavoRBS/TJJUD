<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015015724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona o campo Valor (R$) em Livro';
    }

    public function up(Schema $schema): void
    {
        // DEFAULT 0.00 só para as linhas já existentes passarem na constraint NOT NULL;
        // novos registros sempre informam um valor real via formulário/fixtures.
        $this->addSql('ALTER TABLE Livro ADD Valor NUMERIC(10, 2) NOT NULL DEFAULT 0.00');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro DROP Valor');
    }
}
