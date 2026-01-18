<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117223009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD order_shop_id INT NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81BB6C6D96 FOREIGN KEY (order_shop_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F81BB6C6D96 ON address (order_shop_id)');
        $this->addSql('ALTER TABLE order_shop DROP FOREIGN KEY FK_E19B76B5F5B7AF75');
        $this->addSql('DROP INDEX UNIQ_E19B76B5F5B7AF75 ON order_shop');
        $this->addSql('ALTER TABLE order_shop DROP address_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81BB6C6D96');
        $this->addSql('DROP INDEX UNIQ_D4E6F81BB6C6D96 ON address');
        $this->addSql('ALTER TABLE address DROP order_shop_id');
        $this->addSql('ALTER TABLE order_shop ADD address_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_shop ADD CONSTRAINT FK_E19B76B5F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19B76B5F5B7AF75 ON order_shop (address_id)');
    }
}
