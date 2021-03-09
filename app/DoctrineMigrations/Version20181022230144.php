<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181022230144 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE scanner (id INT AUTO_INCREMENT NOT NULL, technology_id INT NOT NULL, precision_id INT NOT NULL, resolution_id INT NOT NULL, maker_id INT NOT NULL, name VARCHAR(255) NOT NULL, visible TINYINT(1) NOT NULL, brand VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', dimensions_min_x DOUBLE PRECISION DEFAULT NULL, dimensions_min_y DOUBLE PRECISION DEFAULT NULL, dimensions_min_z DOUBLE PRECISION DEFAULT NULL, dimensions_max_x DOUBLE PRECISION DEFAULT NULL, dimensions_max_y DOUBLE PRECISION DEFAULT NULL, dimensions_max_z DOUBLE PRECISION DEFAULT NULL, INDEX IDX_55EFDF294235D463 (technology_id), INDEX IDX_55EFDF29CFB58DC6 (precision_id), INDEX IDX_55EFDF2912A1C43A (resolution_id), INDEX IDX_55EFDF2968DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE scanner ADD CONSTRAINT FK_55EFDF294235D463 FOREIGN KEY (technology_id) REFERENCES ref_technology_scanner (id)');
        $this->addSql('ALTER TABLE scanner ADD CONSTRAINT FK_55EFDF29CFB58DC6 FOREIGN KEY (precision_id) REFERENCES ref_precision (id)');
        $this->addSql('ALTER TABLE scanner ADD CONSTRAINT FK_55EFDF2912A1C43A FOREIGN KEY (resolution_id) REFERENCES ref_resolution (id)');
        $this->addSql('ALTER TABLE scanner ADD CONSTRAINT FK_55EFDF2968DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE scanner');
    }
}
