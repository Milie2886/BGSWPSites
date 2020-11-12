<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104093236 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ajout du site_id dans la table history afin de faire un lien avec les sites';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE history ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BF6BD1646 FOREIGN KEY (site_id) REFERENCES sites (id)');
        $this->addSql('CREATE INDEX IDX_27BA704BF6BD1646 ON history (site_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BF6BD1646');
        $this->addSql('DROP INDEX IDX_27BA704BF6BD1646 ON history');
        $this->addSql('ALTER TABLE history DROP site_id');
    }
}
