<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612133636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_participants DROP CONSTRAINT fk_5bce94f1166d1f9c');
        $this->addSql('ALTER TABLE project_participants DROP CONSTRAINT fk_5bce94f12aadf9823d631c9d');
        $this->addSql('DROP TABLE project_participants');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE project_participants (project_id VARCHAR(36) NOT NULL, participant_project_id VARCHAR(36) NOT NULL, participant_user_id VARCHAR(36) NOT NULL, PRIMARY KEY(project_id, participant_project_id, participant_user_id))');
        $this->addSql('CREATE INDEX idx_5bce94f12aadf9823d631c9d ON project_participants (participant_project_id, participant_user_id)');
        $this->addSql('CREATE INDEX idx_5bce94f1166d1f9c ON project_participants (project_id)');
        $this->addSql('ALTER TABLE project_participants ADD CONSTRAINT fk_5bce94f1166d1f9c FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_participants ADD CONSTRAINT fk_5bce94f12aadf9823d631c9d FOREIGN KEY (participant_project_id, participant_user_id) REFERENCES participants (project_id, user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
