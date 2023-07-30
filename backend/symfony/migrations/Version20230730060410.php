<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230730060410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_projections ADD version INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task_projections ADD version INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_projections ADD version INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_projections DROP version');
        $this->addSql('ALTER TABLE task_projections DROP version');
        $this->addSql('ALTER TABLE user_projections DROP version');
    }
}
