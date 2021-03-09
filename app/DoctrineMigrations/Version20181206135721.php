<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181206135721 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE order_item_design (id INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item_design ADD CONSTRAINT FK_50045079BF396750 FOREIGN KEY (id) REFERENCES order_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD quotation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id)');
        $this->addSql('CREATE INDEX IDX_F5299398B4EA4E60 ON `order` (quotation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE order_item_design');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398B4EA4E60');
        $this->addSql('DROP INDEX IDX_F5299398B4EA4E60 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP quotation_id');
    }
}
