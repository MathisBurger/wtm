<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240510124416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE worktime_period DROP CONSTRAINT FK_E4660C938C03F15C');
        $this->addSql('ALTER TABLE worktime_period ALTER employee_id SET NOT NULL');
        $this->addSql('ALTER TABLE worktime_period ADD CONSTRAINT FK_E4660C938C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE worktime_special_day DROP CONSTRAINT FK_8B4E5A718C03F15C');
        $this->addSql('ALTER TABLE worktime_special_day ALTER employee_id SET NOT NULL');
        $this->addSql('ALTER TABLE worktime_special_day ADD CONSTRAINT FK_8B4E5A718C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE worktime_special_day DROP CONSTRAINT fk_8b4e5a718c03f15c');
        $this->addSql('ALTER TABLE worktime_special_day ALTER employee_id DROP NOT NULL');
        $this->addSql('ALTER TABLE worktime_special_day ADD CONSTRAINT fk_8b4e5a718c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE worktime_period DROP CONSTRAINT fk_e4660c938c03f15c');
        $this->addSql('ALTER TABLE worktime_period ALTER employee_id DROP NOT NULL');
        $this->addSql('ALTER TABLE worktime_period ADD CONSTRAINT fk_e4660c938c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
