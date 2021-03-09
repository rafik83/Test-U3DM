<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180831211518 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, last_modified_by_id INT DEFAULT NULL, code VARCHAR(50) NOT NULL, type VARCHAR(255) NOT NULL, `label` VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, discount_percent DOUBLE PRECISION DEFAULT NULL, discount_amount INT DEFAULT NULL, min_order_amount INT NOT NULL, start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', max_usage_per_customer INT DEFAULT NULL, initial_stock INT DEFAULT NULL, remaining_stock INT DEFAULT NULL, launch_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', enabled TINYINT(1) NOT NULL, u3dm_percent_part INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', UNIQUE INDEX UNIQ_64BF3F0277153098 (code), INDEX IDX_64BF3F02B03A8386 (created_by_id), INDEX IDX_64BF3F02F703974A (last_modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon_user (coupon_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CDAD0A4B66C5951B (coupon_id), INDEX IDX_CDAD0A4BA76ED395 (user_id), PRIMARY KEY(coupon_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F02B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrator (id)');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F02F703974A FOREIGN KEY (last_modified_by_id) REFERENCES administrator (id)');
        $this->addSql('ALTER TABLE coupon_user ADD CONSTRAINT FK_CDAD0A4B66C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coupon_user ADD CONSTRAINT FK_CDAD0A4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD coupon_id INT DEFAULT NULL, ADD discount_amount_tax_incl INT NOT NULL, ADD discount_amount_tax_excl INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939866C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id)');
        $this->addSql('CREATE INDEX IDX_F529939866C5951B ON `order` (coupon_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939866C5951B');
        $this->addSql('ALTER TABLE coupon_user DROP FOREIGN KEY FK_CDAD0A4B66C5951B');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE coupon_user');
        $this->addSql('DROP INDEX IDX_F529939866C5951B ON `order`');
        $this->addSql('ALTER TABLE `order` DROP coupon_id, DROP discount_amount_tax_incl, DROP discount_amount_tax_excl');
    }
}
