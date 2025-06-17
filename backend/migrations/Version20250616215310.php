<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616215310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, INDEX IDX_D4E6F81A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_F0FE25271AD5CDBF (cart_id), INDEX IDX_F0FE25274584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(7) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE digital_product (id INT NOT NULL, download_link VARCHAR(255) NOT NULL, filesize INT DEFAULT NULL, filetype VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, order_id INT DEFAULT NULL, total_amount DOUBLE PRECISION NOT NULL, pdf_path VARCHAR(255) DEFAULT NULL, INDEX IDX_90651744A76ED395 (user_id), UNIQUE INDEX UNIQ_906517448D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_52EA1F098D9F6D38 (order_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, invoice_id INT DEFAULT NULL, total NUMERIC(10, 2) NOT NULL, date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_E52FFDEEA76ED395 (user_id), UNIQUE INDEX UNIQ_E52FFDEE2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE physical_product (id INT NOT NULL, characteristics JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, default_category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, product_type VARCHAR(255) NOT NULL, INDEX IDX_D34A04ADC6B58E54 (default_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_tag (product_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_E3A6E39C4584665A (product_id), INDEX IDX_E3A6E39CBAD26311 (tag_id), PRIMARY KEY(product_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY(product_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, content LONGTEXT NOT NULL, rating INT NOT NULL, status VARCHAR(255) NOT NULL, date_publication DATETIME NOT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C64584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(7) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE digital_product ADD CONSTRAINT FK_F3C8DFA7BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_90651744A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_906517448D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE physical_product ADD CONSTRAINT FK_C7F2AC80BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC6B58E54 FOREIGN KEY (default_category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_tag ADD CONSTRAINT FK_E3A6E39C4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_tag ADD CONSTRAINT FK_E3A6E39CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD lastname VARCHAR(255) NOT NULL, ADD reset_token VARCHAR(255) DEFAULT NULL, ADD confirmation_token VARCHAR(255) DEFAULT NULL, ADD is_verified TINYINT(1) NOT NULL, DROP created_at
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE digital_product DROP FOREIGN KEY FK_F3C8DFA7BF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP FOREIGN KEY FK_90651744A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP FOREIGN KEY FK_906517448D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE2989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE physical_product DROP FOREIGN KEY FK_C7F2AC80BF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC6B58E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_tag DROP FOREIGN KEY FK_E3A6E39C4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_tag DROP FOREIGN KEY FK_E3A6E39CBAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C64584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE address
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cart
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cart_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE digital_product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orders
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE physical_product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE review
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` ADD created_at DATETIME NOT NULL, DROP lastname, DROP reset_token, DROP confirmation_token, DROP is_verified
        SQL);
    }
}
