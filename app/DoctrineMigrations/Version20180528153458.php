<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180528153458 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker DROP INDEX IDX_C6197FB4F5B7AF75, ADD UNIQUE INDEX UNIQ_C6197FB4F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE maker DROP INDEX IDX_C6197FB4A72D874B, ADD UNIQUE INDEX UNIQ_C6197FB4A72D874B (pickup_address_id)');
        $this->addSql('ALTER TABLE `order` DROP INDEX IDX_F529939879D0C0E4, ADD UNIQUE INDEX UNIQ_F529939879D0C0E4 (billing_address_id)');
        $this->addSql('ALTER TABLE `order` DROP INDEX IDX_F52993984D4CFF2B, ADD UNIQUE INDEX UNIQ_F52993984D4CFF2B (shipping_address_id)');
        $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D6491995CE08, ADD UNIQUE INDEX UNIQ_8D93D6491995CE08 (default_billing_address_id)');
        $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D649E4901476, ADD UNIQUE INDEX UNIQ_8D93D649E4901476 (default_shipping_address_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker DROP INDEX UNIQ_C6197FB4F5B7AF75, ADD INDEX IDX_C6197FB4F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE maker DROP INDEX UNIQ_C6197FB4A72D874B, ADD INDEX IDX_C6197FB4A72D874B (pickup_address_id)');
        $this->addSql('ALTER TABLE `order` DROP INDEX UNIQ_F529939879D0C0E4, ADD INDEX IDX_F529939879D0C0E4 (billing_address_id)');
        $this->addSql('ALTER TABLE `order` DROP INDEX UNIQ_F52993984D4CFF2B, ADD INDEX IDX_F52993984D4CFF2B (shipping_address_id)');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D6491995CE08, ADD INDEX IDX_8D93D6491995CE08 (default_billing_address_id)');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D649E4901476, ADD INDEX IDX_8D93D649E4901476 (default_shipping_address_id)');
    }
}
