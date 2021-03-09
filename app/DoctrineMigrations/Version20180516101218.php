<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180516101218 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item_print_finishing DROP FOREIGN KEY FK_AB8B9D2774FB3B5');
        $this->addSql('ALTER TABLE order_item_print_finishing DROP FOREIGN KEY FK_AB8B9D2744374D1');
        $this->addSql('DROP INDEX IDX_AB8B9D2774FB3B5 ON order_item_print_finishing');
        $this->addSql('ALTER TABLE order_item_print_finishing DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD print_item_id INT NOT NULL, CHANGE order_item_print_id id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D27370C4780 FOREIGN KEY (print_item_id) REFERENCES order_item_print (id)');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D27BF396750 FOREIGN KEY (id) REFERENCES order_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D2744374D1 FOREIGN KEY (finishing_id) REFERENCES ref_finishing (id)');
        $this->addSql('CREATE INDEX IDX_AB8B9D27370C4780 ON order_item_print_finishing (print_item_id)');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item_print_finishing DROP FOREIGN KEY FK_AB8B9D27370C4780');
        $this->addSql('ALTER TABLE order_item_print_finishing DROP FOREIGN KEY FK_AB8B9D27BF396750');
        $this->addSql('ALTER TABLE order_item_print_finishing DROP FOREIGN KEY FK_AB8B9D2744374D1');
        $this->addSql('DROP INDEX IDX_AB8B9D27370C4780 ON order_item_print_finishing');
        $this->addSql('ALTER TABLE order_item_print_finishing DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD order_item_print_id INT NOT NULL, DROP id, DROP print_item_id');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D2774FB3B5 FOREIGN KEY (order_item_print_id) REFERENCES order_item_print (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D2744374D1 FOREIGN KEY (finishing_id) REFERENCES ref_finishing (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_AB8B9D2774FB3B5 ON order_item_print_finishing (order_item_print_id)');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD PRIMARY KEY (order_item_print_id, finishing_id)');
    }
}
