<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180522223126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color ADD enabled TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE ref_finishing ADD enabled TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE ref_layer ADD enabled TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE ref_material ADD enabled TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE ref_technology ADD enabled TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color DROP enabled');
        $this->addSql('ALTER TABLE ref_finishing DROP enabled');
        $this->addSql('ALTER TABLE ref_layer DROP enabled');
        $this->addSql('ALTER TABLE ref_material DROP enabled');
        $this->addSql('ALTER TABLE ref_technology DROP enabled');
    }
}
