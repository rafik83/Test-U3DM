<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180418134441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, maker_id INT NOT NULL, billing_address_id INT NOT NULL, shipping_address_id INT DEFAULT NULL, status INT NOT NULL, total_amount_tax_incl INT NOT NULL, total_amount_tax_excl INT NOT NULL, shipping_amount_tax_incl INT NOT NULL, shipping_amount_tax_excl INT NOT NULL, fee_amount_tax_incl INT NOT NULL, fee_amount_tax_excl INT NOT NULL, commission_rate DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_F52993989395C3F3 (customer_id), INDEX IDX_F529939868DA5EC3 (maker_id), INDEX IDX_F529939879D0C0E4 (billing_address_id), INDEX IDX_F52993984D4CFF2B (shipping_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, description VARCHAR(255) NOT NULL, unit_amount_tax_incl INT NOT NULL, unit_amount_tax_excl INT NOT NULL, quantity INT NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_52EA1F098D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item_print (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_status_update (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, maker_id INT DEFAULT NULL, administrator_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, status INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_880CD5E78D9F6D38 (order_id), INDEX IDX_880CD5E768DA5EC3 (maker_id), INDEX IDX_880CD5E74B09E92C (administrator_id), INDEX IDX_880CD5E79395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939868DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939879D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE3BF396750 FOREIGN KEY (id) REFERENCES order_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E768DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E74B09E92C FOREIGN KEY (administrator_id) REFERENCES administrator (id)');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E79395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_status_update DROP FOREIGN KEY FK_880CD5E78D9F6D38');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE3BF396750');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE order_item_print');
        $this->addSql('DROP TABLE order_status_update');
    }
}
