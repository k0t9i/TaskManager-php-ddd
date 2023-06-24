<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623181715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_list_projections (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, name VARCHAR(4000) NOT NULL, finish_date TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, owner_id VARCHAR(36) NOT NULL, status INT NOT NULL, tasks_count INT NOT NULL, participants_count INT NOT NULL, pending_requests_count INT NOT NULL, PRIMARY KEY(id, user_id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE project_list_projections');
    }
}
