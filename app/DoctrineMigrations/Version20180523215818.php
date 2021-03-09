<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180523215818 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE maker DROP type');
        $this->addSql('ALTER TABLE user ADD company VARCHAR(255) DEFAULT NULL, ADD company_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE maker ADD company_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker DROP company_type');
        $this->addSql('ALTER TABLE user DROP company, DROP company_type');
        $this->addSql('ALTER TABLE maker ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user DROP type');
    }
}
