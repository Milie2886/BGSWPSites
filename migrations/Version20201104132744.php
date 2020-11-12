<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104132744 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ajout des champs created_at et updated_at grace au trait';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE history ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE history DROP created_at, DROP updated_at');
    }
}
