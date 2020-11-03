<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029151813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ajout relation entre les sites et la table User';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sites ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE sites ADD CONSTRAINT FK_BC00AA63A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_BC00AA63A76ED395 ON sites (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sites DROP FOREIGN KEY FK_BC00AA63A76ED395');
        $this->addSql('DROP INDEX IDX_BC00AA63A76ED395 ON sites');
        $this->addSql('ALTER TABLE sites DROP user_id');
    }
}
