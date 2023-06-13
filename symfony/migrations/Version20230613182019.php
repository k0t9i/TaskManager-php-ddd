<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613182019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id VARCHAR(36) NOT NULL, project_id VARCHAR(36) NOT NULL, status INT NOT NULL, is_draft BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, brief VARCHAR(2000) NOT NULL, description VARCHAR(4000) NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finish_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE tasks');
    }
}
