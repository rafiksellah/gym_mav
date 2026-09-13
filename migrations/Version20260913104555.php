<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913104555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, address LONGTEXT DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, nif VARCHAR(100) DEFAULT NULL, nis VARCHAR(100) DEFAULT NULL, rc VARCHAR(100) DEFAULT NULL, ai VARCHAR(100) DEFAULT NULL, rib VARCHAR(100) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company_settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, tagline VARCHAR(255) DEFAULT NULL, logo_filename VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, website VARCHAR(180) DEFAULT NULL, nif VARCHAR(100) DEFAULT NULL, nis VARCHAR(100) DEFAULT NULL, rc VARCHAR(100) DEFAULT NULL, ai VARCHAR(100) DEFAULT NULL, rib VARCHAR(100) DEFAULT NULL, bank_info LONGTEXT DEFAULT NULL, capital_social VARCHAR(100) DEFAULT NULL, payment_terms LONGTEXT DEFAULT NULL, legal_mentions LONGTEXT DEFAULT NULL, default_vat_rate NUMERIC(5, 2) NOT NULL, default_currency VARCHAR(10) NOT NULL, invoice_number_prefix VARCHAR(20) NOT NULL, last_invoice_sequence INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(50) NOT NULL, invoice_date DATETIME NOT NULL, due_date DATETIME DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, purchase_order VARCHAR(100) DEFAULT NULL, payment_method VARCHAR(100) DEFAULT NULL, currency VARCHAR(10) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, client_id INT NOT NULL, UNIQUE INDEX UNIQ_9065174496901F54 (number), INDEX IDX_9065174419EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, reference VARCHAR(100) DEFAULT NULL, quantity NUMERIC(12, 3) NOT NULL, unit VARCHAR(30) DEFAULT NULL, unit_price_ht NUMERIC(14, 2) NOT NULL, discount_percent NUMERIC(5, 2) NOT NULL, vat_rate NUMERIC(5, 2) NOT NULL, position INT NOT NULL, invoice_id INT NOT NULL, INDEX IDX_D3D1D6932989F1FD (invoice_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174419EB6921');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6932989F1FD');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE company_settings');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
    }
}
