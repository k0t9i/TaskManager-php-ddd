<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612141847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_requests DROP CONSTRAINT fk_46cb5d8a166d1f9c');
        $this->addSql('ALTER TABLE project_requests DROP CONSTRAINT fk_46cb5d8a427eb8a5');
        $this->addSql('DROP TABLE project_requests');
        $this->addSql('ALTER TABLE requests ADD project_id VARCHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE project_requests (project_id VARCHAR(36) NOT NULL, request_id VARCHAR(36) NOT NULL, PRIMARY KEY(project_id, request_id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_46cb5d8a427eb8a5 ON project_requests (request_id)');
        $this->addSql('CREATE INDEX idx_46cb5d8a166d1f9c ON project_requests (project_id)');
        $this->addSql('ALTER TABLE project_requests ADD CONSTRAINT fk_46cb5d8a166d1f9c FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_requests ADD CONSTRAINT fk_46cb5d8a427eb8a5 FOREIGN KEY (request_id) REFERENCES requests (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requests DROP project_id');
    }
}
