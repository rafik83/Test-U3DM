<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180322150019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE prospect_tag_domain DROP FOREIGN KEY FK_F1C0D5E2BAD26311');
        $this->addSql('ALTER TABLE prospect_tag_technology DROP FOREIGN KEY FK_F4D90171BAD26311');
        $this->addSql('RENAME TABLE tag TO ref_tag');
        $this->addSql('ALTER TABLE prospect_tag_technology ADD CONSTRAINT FK_F4D90171BAD26311 FOREIGN KEY (tag_id) REFERENCES ref_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prospect_tag_domain ADD CONSTRAINT FK_F1C0D5E2BAD26311 FOREIGN KEY (tag_id) REFERENCES ref_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE prospect_tag_technology DROP FOREIGN KEY FK_F4D90171BAD26311');
        $this->addSql('ALTER TABLE prospect_tag_domain DROP FOREIGN KEY FK_F1C0D5E2BAD26311');
        $this->addSql('RENAME TABLE ref_tag TO tag');
        $this->addSql('ALTER TABLE prospect_tag_domain ADD CONSTRAINT FK_F1C0D5E2BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prospect_tag_technology ADD CONSTRAINT FK_F4D90171BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }
}
