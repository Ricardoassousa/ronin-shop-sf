<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212112507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_address CHANGE secondary_address secondary_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_profile CHANGE secondary_address secondary_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_address CHANGE secondary_address secondary_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_profile CHANGE secondary_address secondary_address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE cart_address CHANGE secondary_address secondary_address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE order_address CHANGE secondary_address secondary_address VARCHAR(255) NOT NULL');
    }
}
