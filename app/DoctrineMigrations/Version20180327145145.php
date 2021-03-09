<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327145145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE printer_product_finishing (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, finishing_id INT NOT NULL, price INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_FB3135C84584665A (product_id), INDEX IDX_FB3135C844374D1 (finishing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printer_product_finishing ADD CONSTRAINT FK_FB3135C84584665A FOREIGN KEY (product_id) REFERENCES printer_product (id)');
        $this->addSql('ALTER TABLE printer_product_finishing ADD CONSTRAINT FK_FB3135C844374D1 FOREIGN KEY (finishing_id) REFERENCES ref_finishing (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE printer_product_finishing');
    }
}
