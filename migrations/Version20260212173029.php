<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212173029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item ADD product_slug VARCHAR(255) DEFAULT NULL, ADD product_short_description VARCHAR(255) DEFAULT NULL, ADD product_image VARCHAR(255) DEFAULT NULL, DROP slug, DROP short_description, DROP image, CHANGE name product_name VARCHAR(255) NOT NULL, CHANGE sku product_sku VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item ADD slug VARCHAR(255) DEFAULT NULL, ADD short_description VARCHAR(255) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL, DROP product_slug, DROP product_short_description, DROP product_image, CHANGE product_name name VARCHAR(255) NOT NULL, CHANGE product_sku sku VARCHAR(100) NOT NULL');
    }
}
