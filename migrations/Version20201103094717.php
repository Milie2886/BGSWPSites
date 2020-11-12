<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201103094717 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ajout de la table Types qui contiendra toutes les versions recommandées pour chaque composant';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP INDEX name ON sites');
        $this->addSql('DROP INDEX url ON sites');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE types');
        $this->addSql('CREATE UNIQUE INDEX name ON sites (name)');
        $this->addSql('CREATE UNIQUE INDEX url ON sites (url)');
    }
}
