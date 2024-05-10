<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240510182332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE configured_worktime_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE configured_worktime (id INT NOT NULL, employee_id INT DEFAULT NULL, day_name VARCHAR(255) NOT NULL, regular_start_time TIME(0) WITHOUT TIME ZONE NOT NULL, regular_end_time TIME(0) WITHOUT TIME ZONE NOT NULL, restricted_start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, restricted_end_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_80564C3D8C03F15C ON configured_worktime (employee_id)');
        $this->addSql('ALTER TABLE configured_worktime ADD CONSTRAINT FK_80564C3D8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee DROP target_working_time_begin');
        $this->addSql('ALTER TABLE employee DROP target_working_time_end');
        $this->addSql('ALTER TABLE employee DROP target_working_present');
        $this->addSql('ALTER TABLE employee DROP restricted_start_time');
        $this->addSql('ALTER TABLE employee DROP restricted_end_time');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE configured_worktime_id_seq CASCADE');
        $this->addSql('ALTER TABLE configured_worktime DROP CONSTRAINT FK_80564C3D8C03F15C');
        $this->addSql('DROP TABLE configured_worktime');
        $this->addSql('ALTER TABLE employee ADD target_working_time_begin TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ADD target_working_time_end TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ADD target_working_present BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE employee ADD restricted_start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE employee ADD restricted_end_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}
