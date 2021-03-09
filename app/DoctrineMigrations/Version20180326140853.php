<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180326140853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE printer (id INT AUTO_INCREMENT NOT NULL, technology_id INT NOT NULL, maker_id INT NOT NULL, model VARCHAR(255) NOT NULL, volume_min INT NOT NULL, price_setup INT NOT NULL, available TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', dimensions_max_x INT DEFAULT NULL, dimensions_max_y INT DEFAULT NULL, dimensions_max_z INT DEFAULT NULL, INDEX IDX_8D4C79ED4235D463 (technology_id), INDEX IDX_8D4C79ED68DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printer ADD CONSTRAINT FK_8D4C79ED4235D463 FOREIGN KEY (technology_id) REFERENCES ref_technology (id)');
        $this->addSql('ALTER TABLE printer ADD CONSTRAINT FK_8D4C79ED68DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE printer');
    }
}
