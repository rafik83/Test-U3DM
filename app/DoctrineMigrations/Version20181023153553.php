<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181023153553 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, type_id INT NOT NULL, scan_address_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, status INT NOT NULL, description LONGTEXT NOT NULL, delivery_time VARCHAR(255) NOT NULL, closed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', scan_on_site TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', dimensions_x DOUBLE PRECISION DEFAULT NULL, dimensions_y DOUBLE PRECISION DEFAULT NULL, dimensions_z DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2FB3D0EE9395C3F3 (customer_id), INDEX IDX_2FB3D0EEC54C8C93 (type_id), UNIQUE INDEX UNIQ_2FB3D0EEDF334BEC (scan_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_field (project_id INT NOT NULL, field_id INT NOT NULL, INDEX IDX_48A04CC6166D1F9C (project_id), INDEX IDX_48A04CC6443707B0 (field_id), PRIMARY KEY(project_id, field_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_skill (project_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_4D68EDE9166D1F9C (project_id), INDEX IDX_4D68EDE95585C142 (skill_id), PRIMARY KEY(project_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_software (project_id INT NOT NULL, software_id INT NOT NULL, INDEX IDX_4A9EE314166D1F9C (project_id), INDEX IDX_4A9EE314D7452741 (software_id), PRIMARY KEY(project_id, software_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_file (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, name_original VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_B50EFE08166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_status_update (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, status INT NOT NULL, origin VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_F1FE0A33166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEC54C8C93 FOREIGN KEY (type_id) REFERENCES ref_project (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEDF334BEC FOREIGN KEY (scan_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE project_field ADD CONSTRAINT FK_48A04CC6166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_field ADD CONSTRAINT FK_48A04CC6443707B0 FOREIGN KEY (field_id) REFERENCES ref_field (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_skill ADD CONSTRAINT FK_4D68EDE9166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_skill ADD CONSTRAINT FK_4D68EDE95585C142 FOREIGN KEY (skill_id) REFERENCES ref_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_software ADD CONSTRAINT FK_4A9EE314166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_software ADD CONSTRAINT FK_4A9EE314D7452741 FOREIGN KEY (software_id) REFERENCES ref_software (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_file ADD CONSTRAINT FK_B50EFE08166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_status_update ADD CONSTRAINT FK_F1FE0A33166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_field DROP FOREIGN KEY FK_48A04CC6166D1F9C');
        $this->addSql('ALTER TABLE project_skill DROP FOREIGN KEY FK_4D68EDE9166D1F9C');
        $this->addSql('ALTER TABLE project_software DROP FOREIGN KEY FK_4A9EE314166D1F9C');
        $this->addSql('ALTER TABLE project_file DROP FOREIGN KEY FK_B50EFE08166D1F9C');
        $this->addSql('ALTER TABLE project_status_update DROP FOREIGN KEY FK_F1FE0A33166D1F9C');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_field');
        $this->addSql('DROP TABLE project_skill');
        $this->addSql('DROP TABLE project_software');
        $this->addSql('DROP TABLE project_file');
        $this->addSql('DROP TABLE project_status_update');
    }
}
