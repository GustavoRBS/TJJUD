<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015013303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria a tabela Livro_Autor (N:N entre Livro e Autor)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Livro_Autor (Livro_CodI INT NOT NULL, Autor_CodAu INT NOT NULL, INDEX IDX_4129394115E287E (Livro_CodI), INDEX IDX_41293941B44F3F36 (Autor_CodAu), PRIMARY KEY (Livro_CodI, Autor_CodAu)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE Livro_Autor ADD CONSTRAINT FK_4129394115E287E FOREIGN KEY (Livro_CodI) REFERENCES Livro (CodI)');
        $this->addSql('ALTER TABLE Livro_Autor ADD CONSTRAINT FK_41293941B44F3F36 FOREIGN KEY (Autor_CodAu) REFERENCES Autor (CodAu)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Livro_Autor DROP FOREIGN KEY FK_4129394115E287E');
        $this->addSql('ALTER TABLE Livro_Autor DROP FOREIGN KEY FK_41293941B44F3F36');
        $this->addSql('DROP TABLE Livro_Autor');
    }
}
