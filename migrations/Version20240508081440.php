<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240508081440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE worktime_special_day ADD employee_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE worktime_special_day ADD CONSTRAINT FK_8B4E5A718C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8B4E5A718C03F15C ON worktime_special_day (employee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE worktime_special_day DROP CONSTRAINT FK_8B4E5A718C03F15C');
        $this->addSql('DROP INDEX IDX_8B4E5A718C03F15C');
        $this->addSql('ALTER TABLE worktime_special_day DROP employee_id');
    }
}
