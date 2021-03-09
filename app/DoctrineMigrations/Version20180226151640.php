<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180226151640 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE administrator (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, timezone VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_58DF0651E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prospect (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', email VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, message LONGTEXT DEFAULT NULL, maker TINYINT(1) NOT NULL, printer TINYINT(1) NOT NULL, designer TINYINT(1) NOT NULL, customer_type VARCHAR(255) DEFAULT NULL, newsletter TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prospect_tag_technology (prospect_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_F4D90171D182060A (prospect_id), INDEX IDX_F4D90171BAD26311 (tag_id), PRIMARY KEY(prospect_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prospect_tag_domain (prospect_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_F1C0D5E2D182060A (prospect_id), INDEX IDX_F1C0D5E2BAD26311 (tag_id), PRIMARY KEY(prospect_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prospect_tag_technology ADD CONSTRAINT FK_F4D90171D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prospect_tag_technology ADD CONSTRAINT FK_F4D90171BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prospect_tag_domain ADD CONSTRAINT FK_F1C0D5E2D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prospect_tag_domain ADD CONSTRAINT FK_F1C0D5E2BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE prospect_tag_technology DROP FOREIGN KEY FK_F4D90171D182060A');
        $this->addSql('ALTER TABLE prospect_tag_domain DROP FOREIGN KEY FK_F1C0D5E2D182060A');
        $this->addSql('ALTER TABLE prospect_tag_technology DROP FOREIGN KEY FK_F4D90171BAD26311');
        $this->addSql('ALTER TABLE prospect_tag_domain DROP FOREIGN KEY FK_F1C0D5E2BAD26311');
        $this->addSql('DROP TABLE administrator');
        $this->addSql('DROP TABLE prospect');
        $this->addSql('DROP TABLE prospect_tag_technology');
        $this->addSql('DROP TABLE prospect_tag_domain');
        $this->addSql('DROP TABLE tag');
    }
}
