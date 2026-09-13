<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913120246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP due_date, DROP reference, DROP purchase_order, DROP payment_method');
        $this->addSql('ALTER TABLE invoice_line DROP discount_percent, DROP vat_rate');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD due_date DATETIME DEFAULT NULL, ADD reference VARCHAR(255) DEFAULT NULL, ADD purchase_order VARCHAR(100) DEFAULT NULL, ADD payment_method VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_line ADD discount_percent NUMERIC(5, 2) NOT NULL, ADD vat_rate NUMERIC(5, 2) NOT NULL');
    }
}
