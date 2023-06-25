<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625193632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_projections (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, name VARCHAR(4000) NOT NULL, brief VARCHAR(4000) NOT NULL, description VARCHAR(4000) NOT NULL, start_date TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, finish_date TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, owner_id VARCHAR(36) NOT NULL, status INT NOT NULL, project_id VARCHAR(36) NOT NULL, PRIMARY KEY(id, user_id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE task_projections');
    }
}
