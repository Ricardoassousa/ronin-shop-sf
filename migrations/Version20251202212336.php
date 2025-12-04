<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202212336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, country_prefix_code VARCHAR(10) NOT NULL, primary_address VARCHAR(255) NOT NULL, secondary_address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) NOT NULL, country VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_9D8A0EB1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_profile ADD CONSTRAINT FK_9D8A0EB1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_profile DROP FOREIGN KEY FK_9D8A0EB1A76ED395');
        $this->addSql('DROP TABLE customer_profile');
    }
}
