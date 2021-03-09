<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180530221920 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_status_update DROP FOREIGN KEY FK_880CD5E74B09E92C');
        $this->addSql('ALTER TABLE order_status_update DROP FOREIGN KEY FK_880CD5E768DA5EC3');
        $this->addSql('ALTER TABLE order_status_update DROP FOREIGN KEY FK_880CD5E79395C3F3');
        $this->addSql('DROP INDEX IDX_880CD5E768DA5EC3 ON order_status_update');
        $this->addSql('DROP INDEX IDX_880CD5E74B09E92C ON order_status_update');
        $this->addSql('DROP INDEX IDX_880CD5E79395C3F3 ON order_status_update');
        $this->addSql('ALTER TABLE order_status_update ADD origin VARCHAR(255) NOT NULL, DROP maker_id, DROP administrator_id, DROP customer_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_status_update ADD maker_id INT DEFAULT NULL, ADD administrator_id INT DEFAULT NULL, ADD customer_id INT DEFAULT NULL, DROP origin');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E74B09E92C FOREIGN KEY (administrator_id) REFERENCES administrator (id)');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E768DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
        $this->addSql('ALTER TABLE order_status_update ADD CONSTRAINT FK_880CD5E79395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_880CD5E768DA5EC3 ON order_status_update (maker_id)');
        $this->addSql('CREATE INDEX IDX_880CD5E74B09E92C ON order_status_update (administrator_id)');
        $this->addSql('CREATE INDEX IDX_880CD5E79395C3F3 ON order_status_update (customer_id)');
    }
}
