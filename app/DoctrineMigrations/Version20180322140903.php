<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180322140903 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ref_technology (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ref_technology_material (technology_id INT NOT NULL, material_id INT NOT NULL, INDEX IDX_2873D0764235D463 (technology_id), INDEX IDX_2873D076E308AC6F (material_id), PRIMARY KEY(technology_id, material_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ref_color (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ref_finishing (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ref_layer (id INT AUTO_INCREMENT NOT NULL, height INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ref_material (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ref_technology_material ADD CONSTRAINT FK_2873D0764235D463 FOREIGN KEY (technology_id) REFERENCES ref_technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ref_technology_material ADD CONSTRAINT FK_2873D076E308AC6F FOREIGN KEY (material_id) REFERENCES ref_material (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_technology_material DROP FOREIGN KEY FK_2873D0764235D463');
        $this->addSql('ALTER TABLE ref_technology_material DROP FOREIGN KEY FK_2873D076E308AC6F');
        $this->addSql('DROP TABLE ref_technology');
        $this->addSql('DROP TABLE ref_technology_material');
        $this->addSql('DROP TABLE ref_color');
        $this->addSql('DROP TABLE ref_finishing');
        $this->addSql('DROP TABLE ref_layer');
        $this->addSql('DROP TABLE ref_material');
    }
}
