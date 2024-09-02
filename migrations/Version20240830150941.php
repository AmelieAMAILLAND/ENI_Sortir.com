<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830150941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE place_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE place (id INT NOT NULL, name VARCHAR(50) NOT NULL, street VARCHAR(50) NOT NULL, zip_code VARCHAR(5) NOT NULL, city VARCHAR(50) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_741D53CD5E237E06 ON place (name)');
        $this->addSql('ALTER TABLE event ADD place_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD annulation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7DA6A219 FOREIGN KEY (place_id) REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA75E237E06 ON event (name)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7DA6A219 ON event (place_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E45E237E06 ON site (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64986CC499D ON "user" (pseudo)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7DA6A219');
        $this->addSql('DROP SEQUENCE place_id_seq CASCADE');
        $this->addSql('DROP TABLE place');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA75E237E06');
        $this->addSql('DROP INDEX IDX_3BAE0AA7DA6A219');
        $this->addSql('ALTER TABLE event DROP place_id');
        $this->addSql('ALTER TABLE event DROP annulation');
        $this->addSql('DROP INDEX UNIQ_8D93D64986CC499D');
        $this->addSql('DROP INDEX UNIQ_694309E45E237E06');
    }
}
