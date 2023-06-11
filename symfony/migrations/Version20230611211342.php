<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230611211342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participants (project_id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, PRIMARY KEY(project_id, user_id))');
        $this->addSql('CREATE TABLE project_users (id VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE projects (id VARCHAR(36) NOT NULL, status INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(4000) NOT NULL, finish_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE project_participants (project_id VARCHAR(36) NOT NULL, participant_project_id VARCHAR(36) NOT NULL, participant_user_id VARCHAR(36) NOT NULL, PRIMARY KEY(project_id, participant_project_id, participant_user_id))');
        $this->addSql('CREATE INDEX IDX_5BCE94F1166D1F9C ON project_participants (project_id)');
        $this->addSql('CREATE INDEX IDX_5BCE94F12AADF9823D631C9D ON project_participants (participant_project_id, participant_user_id)');
        $this->addSql('CREATE TABLE project_requests (project_id VARCHAR(36) NOT NULL, request_id VARCHAR(36) NOT NULL, PRIMARY KEY(project_id, request_id))');
        $this->addSql('CREATE INDEX IDX_46CB5D8A166D1F9C ON project_requests (project_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_46CB5D8A427EB8A5 ON project_requests (request_id)');
        $this->addSql('CREATE TABLE requests (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, status INT NOT NULL, change_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, email VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE project_participants ADD CONSTRAINT FK_5BCE94F1166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_participants ADD CONSTRAINT FK_5BCE94F12AADF9823D631C9D FOREIGN KEY (participant_project_id, participant_user_id) REFERENCES participants (project_id, user_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_requests ADD CONSTRAINT FK_46CB5D8A166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_requests ADD CONSTRAINT FK_46CB5D8A427EB8A5 FOREIGN KEY (request_id) REFERENCES requests (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_participants DROP CONSTRAINT FK_5BCE94F1166D1F9C');
        $this->addSql('ALTER TABLE project_participants DROP CONSTRAINT FK_5BCE94F12AADF9823D631C9D');
        $this->addSql('ALTER TABLE project_requests DROP CONSTRAINT FK_46CB5D8A166D1F9C');
        $this->addSql('ALTER TABLE project_requests DROP CONSTRAINT FK_46CB5D8A427EB8A5');
        $this->addSql('DROP TABLE participants');
        $this->addSql('DROP TABLE project_users');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE project_participants');
        $this->addSql('DROP TABLE project_requests');
        $this->addSql('DROP TABLE requests');
        $this->addSql('DROP TABLE users');
    }
}
