<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827120231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD planner_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA75346EAE1 FOREIGN KEY (planner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA75346EAE1 ON event (planner_id)');
        $this->addSql('ALTER TABLE "user" ADD site_id INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649F6BD1646 ON "user" (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA75346EAE1');
        $this->addSql('DROP INDEX IDX_3BAE0AA75346EAE1');
        $this->addSql('ALTER TABLE event DROP planner_id');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649F6BD1646');
        $this->addSql('DROP INDEX IDX_8D93D649F6BD1646');
        $this->addSql('ALTER TABLE "user" DROP site_id');
    }
}
