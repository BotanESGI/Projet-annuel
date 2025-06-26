<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626095816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE order_address (id INT AUTO_INCREMENT NOT NULL, shipping_street VARCHAR(255) NOT NULL, shipping_city VARCHAR(255) NOT NULL, shipping_postal_code VARCHAR(10) NOT NULL, billing_street VARCHAR(255) NOT NULL, billing_city VARCHAR(255) NOT NULL, billing_postal_code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD type VARCHAR(20) NOT NULL, ADD is_default_billing TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD shipping_address_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES order_address (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_E52FFDEE4D4CFF2B ON orders (shipping_address_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4D4CFF2B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP type, DROP is_default_billing
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_E52FFDEE4D4CFF2B ON orders
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP shipping_address_id
        SQL);
    }
}
