<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180426085141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE order_item_print_finishing (order_item_print_id INT NOT NULL, finishing_id INT NOT NULL, INDEX IDX_AB8B9D2774FB3B5 (order_item_print_id), INDEX IDX_AB8B9D2744374D1 (finishing_id), PRIMARY KEY(order_item_print_id, finishing_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D2774FB3B5 FOREIGN KEY (order_item_print_id) REFERENCES order_item_print (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_print_finishing ADD CONSTRAINT FK_AB8B9D2744374D1 FOREIGN KEY (finishing_id) REFERENCES ref_finishing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_print ADD file_id INT NOT NULL, ADD technology_id INT NOT NULL, ADD material_id INT NOT NULL, ADD layer_id INT NOT NULL, ADD color_id INT NOT NULL, ADD volume INT NOT NULL, ADD filling_rate VARCHAR(255) DEFAULT NULL, ADD dimensions_x INT DEFAULT NULL, ADD dimensions_y INT DEFAULT NULL, ADD dimensions_z INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE393CB796C FOREIGN KEY (file_id) REFERENCES print_file (id)');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE34235D463 FOREIGN KEY (technology_id) REFERENCES ref_technology (id)');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE3E308AC6F FOREIGN KEY (material_id) REFERENCES ref_material (id)');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE3EA6EFDCD FOREIGN KEY (layer_id) REFERENCES ref_layer (id)');
        $this->addSql('ALTER TABLE order_item_print ADD CONSTRAINT FK_8CAB9AE37ADA1FB5 FOREIGN KEY (color_id) REFERENCES ref_color (id)');
        $this->addSql('CREATE INDEX IDX_8CAB9AE393CB796C ON order_item_print (file_id)');
        $this->addSql('CREATE INDEX IDX_8CAB9AE34235D463 ON order_item_print (technology_id)');
        $this->addSql('CREATE INDEX IDX_8CAB9AE3E308AC6F ON order_item_print (material_id)');
        $this->addSql('CREATE INDEX IDX_8CAB9AE3EA6EFDCD ON order_item_print (layer_id)');
        $this->addSql('CREATE INDEX IDX_8CAB9AE37ADA1FB5 ON order_item_print (color_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE order_item_print_finishing');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE393CB796C');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE34235D463');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE3E308AC6F');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE3EA6EFDCD');
        $this->addSql('ALTER TABLE order_item_print DROP FOREIGN KEY FK_8CAB9AE37ADA1FB5');
        $this->addSql('DROP INDEX IDX_8CAB9AE393CB796C ON order_item_print');
        $this->addSql('DROP INDEX IDX_8CAB9AE34235D463 ON order_item_print');
        $this->addSql('DROP INDEX IDX_8CAB9AE3E308AC6F ON order_item_print');
        $this->addSql('DROP INDEX IDX_8CAB9AE3EA6EFDCD ON order_item_print');
        $this->addSql('DROP INDEX IDX_8CAB9AE37ADA1FB5 ON order_item_print');
        $this->addSql('ALTER TABLE order_item_print DROP file_id, DROP technology_id, DROP material_id, DROP layer_id, DROP color_id, DROP volume, DROP filling_rate, DROP dimensions_x, DROP dimensions_y, DROP dimensions_z');
    }
}
