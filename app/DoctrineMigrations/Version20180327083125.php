<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327083125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE printer_product (id INT AUTO_INCREMENT NOT NULL, printer_id INT NOT NULL, material_id INT NOT NULL, layer_id INT NOT NULL, price_25 INT DEFAULT NULL, price_50 INT DEFAULT NULL, price_100 INT NOT NULL, available TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_F7CBFEAC46EC494A (printer_id), INDEX IDX_F7CBFEACE308AC6F (material_id), INDEX IDX_F7CBFEACEA6EFDCD (layer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE printer_product_color (printer_product_id INT NOT NULL, color_id INT NOT NULL, INDEX IDX_6473978E30AAB6C8 (printer_product_id), INDEX IDX_6473978E7ADA1FB5 (color_id), PRIMARY KEY(printer_product_id, color_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printer_product ADD CONSTRAINT FK_F7CBFEAC46EC494A FOREIGN KEY (printer_id) REFERENCES printer (id)');
        $this->addSql('ALTER TABLE printer_product ADD CONSTRAINT FK_F7CBFEACE308AC6F FOREIGN KEY (material_id) REFERENCES ref_material (id)');
        $this->addSql('ALTER TABLE printer_product ADD CONSTRAINT FK_F7CBFEACEA6EFDCD FOREIGN KEY (layer_id) REFERENCES ref_layer (id)');
        $this->addSql('ALTER TABLE printer_product_color ADD CONSTRAINT FK_6473978E30AAB6C8 FOREIGN KEY (printer_product_id) REFERENCES printer_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE printer_product_color ADD CONSTRAINT FK_6473978E7ADA1FB5 FOREIGN KEY (color_id) REFERENCES ref_color (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE printer_product_color DROP FOREIGN KEY FK_6473978E30AAB6C8');
        $this->addSql('DROP TABLE printer_product');
        $this->addSql('DROP TABLE printer_product_color');
    }
}
