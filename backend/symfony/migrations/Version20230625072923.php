<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625072923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_list_projections ADD owner_email VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_list_projections ADD owner_firstname VARCHAR(4000) NOT NULL');
        $this->addSql('ALTER TABLE project_list_projections ADD owner_lastname VARCHAR(4000) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_email');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_firstname');
        $this->addSql('ALTER TABLE project_list_projections DROP owner_lastname');
    }
}
