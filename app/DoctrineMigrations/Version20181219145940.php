<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181219145940 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_message RENAME message');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_40C799348D9F6D38');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_40C79934F675F31B');
        $this->addSql('ALTER TABLE message ADD quotation_id INT DEFAULT NULL, CHANGE order_id order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FB4EA4E60 ON message (quotation_id)');
        $this->addSql('DROP INDEX idx_40c799348d9f6d38 ON message');
        $this->addSql('CREATE INDEX IDX_B6BD307F8D9F6D38 ON message (order_id)');
        $this->addSql('DROP INDEX idx_40c79934f675f31b ON message');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_40C799348D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_40C79934F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FB4EA4E60');
        $this->addSql('DROP INDEX IDX_B6BD307FB4EA4E60 ON message');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F8D9F6D38');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE message DROP quotation_id, CHANGE order_id order_id INT NOT NULL');
        $this->addSql('DROP INDEX idx_b6bd307f8d9f6d38 ON message');
        $this->addSql('CREATE INDEX IDX_40C799348D9F6D38 ON message (order_id)');
        $this->addSql('DROP INDEX idx_b6bd307ff675f31b ON message');
        $this->addSql('CREATE INDEX IDX_40C79934F675F31B ON message (author_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message RENAME order_message');
    }
}
