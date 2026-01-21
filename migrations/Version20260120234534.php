<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120234534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_address (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, primary_address VARCHAR(255) NOT NULL, secondary_address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) NOT NULL, country VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_F694A7861AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_address ADD CONSTRAINT FK_F694A7861AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_address ADD CONSTRAINT FK_FB34C6CABB6C6D96 FOREIGN KEY (order_shop_id) REFERENCES order_shop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_address RENAME INDEX uniq_d4e6f81bb6c6d96 TO UNIQ_FB34C6CABB6C6D96');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_address DROP FOREIGN KEY FK_F694A7861AD5CDBF');
        $this->addSql('DROP TABLE cart_address');
        $this->addSql('ALTER TABLE order_address DROP FOREIGN KEY FK_FB34C6CABB6C6D96');
        $this->addSql('ALTER TABLE order_address RENAME INDEX uniq_fb34c6cabb6c6d96 TO UNIQ_D4E6F81BB6C6D96');
    }
}
