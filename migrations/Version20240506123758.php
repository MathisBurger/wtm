<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240506123758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates worktime periods with relation to employee';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE worktime_period_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE worktime_period (id INT NOT NULL, employee_id INT DEFAULT NULL, start_time DOUBLE PRECISION NOT NULL, end_time DOUBLE PRECISION NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E4660C938C03F15C ON worktime_period (employee_id)');
        $this->addSql('ALTER TABLE worktime_period ADD CONSTRAINT FK_E4660C938C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE worktime_period_id_seq CASCADE');
        $this->addSql('ALTER TABLE worktime_period DROP CONSTRAINT FK_E4660C938C03F15C');
        $this->addSql('DROP TABLE worktime_period');
    }
}
