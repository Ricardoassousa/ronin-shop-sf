<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212180514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item CHANGE unit_price unit_price NUMERIC(10, 2) NOT NULL, CHANGE subtotal subtotal NUMERIC(10, 2) NOT NULL, CHANGE discount discount NUMERIC(5, 2) DEFAULT NULL, CHANGE final_price final_price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE order_shop CHANGE total total NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE discount_price discount_price NUMERIC(5, 2) DEFAULT NULL, CHANGE final_price final_price NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item CHANGE unit_price unit_price DOUBLE PRECISION NOT NULL, CHANGE subtotal subtotal DOUBLE PRECISION NOT NULL, CHANGE discount discount DOUBLE PRECISION DEFAULT NULL, CHANGE final_price final_price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE order_shop CHANGE total total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE discount_price discount_price DOUBLE PRECISION DEFAULT NULL, CHANGE final_price final_price DOUBLE PRECISION NOT NULL');
    }
}
