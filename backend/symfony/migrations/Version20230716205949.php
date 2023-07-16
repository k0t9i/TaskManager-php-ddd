<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230716205949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_list_projections ADD owner_full_name VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_email');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_firstname');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_lastname');
        $this->addSql('ALTER TABLE project_list_projections RENAME COLUMN is_participating TO is_involved');
        $this->addSql('ALTER TABLE project_request_projections ADD user_full_name VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_request_projections DROP user_email');
        $this->addSql('ALTER TABLE project_request_projections DROP user_firstname');
        $this->addSql('ALTER TABLE project_request_projections DROP user_lastname');
        $this->addSql('ALTER TABLE task_list_projections ADD owner_full_name VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE task_list_projections DROP owner_email');
        $this->addSql('ALTER TABLE task_list_projections DROP owner_firstname');
        $this->addSql('ALTER TABLE task_list_projections DROP owner_lastname');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_list_projections ADD owner_firstname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_list_projections ADD owner_lastname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_list_projections RENAME COLUMN owner_full_name TO owner_email');
        $this->addSql('ALTER TABLE project_list_projections RENAME COLUMN is_involved TO is_participating');
        $this->addSql('ALTER TABLE task_list_projections ADD owner_firstname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE task_list_projections ADD owner_lastname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE task_list_projections RENAME COLUMN owner_full_name TO owner_email');
        $this->addSql('ALTER TABLE project_request_projections ADD user_firstname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_request_projections ADD user_lastname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_request_projections RENAME COLUMN user_full_name TO user_email');
    }
}
