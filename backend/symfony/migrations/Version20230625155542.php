<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625155542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list_projections (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, name VARCHAR(4000) NOT NULL, start_date TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, finish_date TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, owner_id VARCHAR(36) NOT NULL, owner_email VARCHAR(4000) NOT NULL, owner_firstname VARCHAR(4000) NOT NULL, owner_lastname VARCHAR(4000) NOT NULL, status INT NOT NULL, project_id VARCHAR(36) NOT NULL, links_count INT NOT NULL, PRIMARY KEY(id, user_id))');
        $this->addSql('ALTER TABLE participants ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE participants ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_tasks ALTER task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_tasks ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_tasks ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_users ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE projects ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE projects ALTER status TYPE INT');
        $this->addSql('ALTER TABLE projects ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE projects ALTER description TYPE VARCHAR(4000)');
        $this->addSql('ALTER TABLE projects ALTER finish_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE projects ALTER owner_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE requests ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER status TYPE INT');
        $this->addSql('ALTER TABLE requests ALTER change_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE requests ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE task_links ALTER task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE task_links ALTER linked_task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE tasks ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE tasks ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE tasks ALTER status TYPE INT');
        $this->addSql('ALTER TABLE tasks ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE tasks ALTER brief TYPE VARCHAR(2000)');
        $this->addSql('ALTER TABLE tasks ALTER description TYPE VARCHAR(4000)');
        $this->addSql('ALTER TABLE tasks ALTER start_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tasks ALTER finish_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tasks ALTER owner_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE users ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE users ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER firstname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER lastname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER password TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE task_list_projections');
        $this->addSql('ALTER TABLE project_tasks ALTER task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_tasks ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_tasks ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE participants ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE participants ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE users ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE users ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER firstname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER lastname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE users ALTER password TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE tasks ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE tasks ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE tasks ALTER status TYPE INT');
        $this->addSql('ALTER TABLE tasks ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE tasks ALTER brief TYPE VARCHAR(2000)');
        $this->addSql('ALTER TABLE tasks ALTER description TYPE VARCHAR(4000)');
        $this->addSql('ALTER TABLE tasks ALTER start_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tasks ALTER finish_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE tasks ALTER owner_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE project_users ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE projects ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE projects ALTER status TYPE INT');
        $this->addSql('ALTER TABLE projects ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE projects ALTER description TYPE VARCHAR(4000)');
        $this->addSql('ALTER TABLE projects ALTER finish_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE projects ALTER owner_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE task_links ALTER task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE task_links ALTER linked_task_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER project_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE requests ALTER status TYPE INT');
        $this->addSql('ALTER TABLE requests ALTER change_date TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
    }
}
