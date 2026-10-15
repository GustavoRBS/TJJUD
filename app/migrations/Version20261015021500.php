<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015021500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove o DEFAULT transitório da coluna Livro.Valor';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro ALTER Valor DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro ALTER Valor SET DEFAULT 0.00');
    }
}
