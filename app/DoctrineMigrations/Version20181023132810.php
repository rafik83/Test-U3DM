<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181023132810 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE maker_design_project_type (maker_id INT NOT NULL, project_type_id INT NOT NULL, INDEX IDX_F885D95068DA5EC3 (maker_id), INDEX IDX_F885D950535280F6 (project_type_id), PRIMARY KEY(maker_id, project_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maker_design_skill (maker_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_33E9CF7E68DA5EC3 (maker_id), INDEX IDX_33E9CF7E5585C142 (skill_id), PRIMARY KEY(maker_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maker_design_software (maker_id INT NOT NULL, software_id INT NOT NULL, INDEX IDX_8FCFB88968DA5EC3 (maker_id), INDEX IDX_8FCFB889D7452741 (software_id), PRIMARY KEY(maker_id, software_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE maker_design_project_type ADD CONSTRAINT FK_F885D95068DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_design_project_type ADD CONSTRAINT FK_F885D950535280F6 FOREIGN KEY (project_type_id) REFERENCES ref_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_design_skill ADD CONSTRAINT FK_33E9CF7E68DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_design_skill ADD CONSTRAINT FK_33E9CF7E5585C142 FOREIGN KEY (skill_id) REFERENCES ref_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_design_software ADD CONSTRAINT FK_8FCFB88968DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_design_software ADD CONSTRAINT FK_8FCFB889D7452741 FOREIGN KEY (software_id) REFERENCES ref_software (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker ADD design_available TINYINT(1) NOT NULL, ADD design_auto_moderation TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE maker_design_project_type');
        $this->addSql('DROP TABLE maker_design_skill');
        $this->addSql('DROP TABLE maker_design_software');
        $this->addSql('ALTER TABLE maker DROP design_available, DROP design_auto_moderation');
    }
}
