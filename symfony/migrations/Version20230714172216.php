<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230714172216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_link_projections DROP user_id');
        $this->addSql('ALTER TABLE task_link_projections ADD PRIMARY KEY (task_id, linked_task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX task_link_projections_pkey');
        $this->addSql('ALTER TABLE task_link_projections ADD user_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE task_link_projections ADD PRIMARY KEY (task_id, linked_task_id, user_id)');
    }
}
