<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913114416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP first_name, DROP address, DROP phone, DROP email, DROP rc, DROP rib, DROP notes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD first_name VARCHAR(100) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD email VARCHAR(180) DEFAULT NULL, ADD rc VARCHAR(100) DEFAULT NULL, ADD rib VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL');
    }
}
