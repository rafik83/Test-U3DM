<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180523143142 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color CHANGE enabled enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ref_finishing CHANGE enabled enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ref_layer CHANGE enabled enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ref_material CHANGE enabled enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ref_technology CHANGE enabled enabled TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE ref_finishing CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE ref_layer CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE ref_material CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE ref_technology CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
