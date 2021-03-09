<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180905085216 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, maker_id INT NOT NULL, order_id INT NOT NULL, comment LONGTEXT DEFAULT NULL, rate INT NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_D88926229395C3F3 (customer_id), INDEX IDX_D889262268DA5EC3 (maker_id), UNIQUE INDEX UNIQ_D88926228D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926229395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262268DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926228D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE `order` ADD rating_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A32EFC6 FOREIGN KEY (rating_id) REFERENCES rating (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398A32EFC6 ON `order` (rating_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A32EFC6');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP INDEX UNIQ_F5299398A32EFC6 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP rating_id');
    }
}
