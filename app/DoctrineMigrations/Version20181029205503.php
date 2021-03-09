<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181029205503 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quotation (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, maker_id INT NOT NULL, status INT NOT NULL, description LONGTEXT NOT NULL, reference VARCHAR(255) NOT NULL, production_time INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_474A8DB9166D1F9C (project_id), INDEX IDX_474A8DB968DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quotation_line (id INT AUTO_INCREMENT NOT NULL, quotation_id INT NOT NULL, number INT NOT NULL, description VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, price INT NOT NULL, INDEX IDX_4CE011BAB4EA4E60 (quotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quotation_status_update (id INT AUTO_INCREMENT NOT NULL, quotation_id INT NOT NULL, status INT NOT NULL, origin VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_CEAD9F99B4EA4E60 (quotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB9166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB968DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id)');
        $this->addSql('ALTER TABLE quotation_line ADD CONSTRAINT FK_4CE011BAB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id)');
        $this->addSql('ALTER TABLE quotation_status_update ADD CONSTRAINT FK_CEAD9F99B4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quotation_line DROP FOREIGN KEY FK_4CE011BAB4EA4E60');
        $this->addSql('ALTER TABLE quotation_status_update DROP FOREIGN KEY FK_CEAD9F99B4EA4E60');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('DROP TABLE quotation_line');
        $this->addSql('DROP TABLE quotation_status_update');
    }
}
