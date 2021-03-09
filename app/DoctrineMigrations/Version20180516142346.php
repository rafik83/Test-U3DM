<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180516142346 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item_print CHANGE volume volume DOUBLE PRECISION NOT NULL, CHANGE dimensions_x dimensions_x DOUBLE PRECISION DEFAULT NULL, CHANGE dimensions_y dimensions_y DOUBLE PRECISION DEFAULT NULL, CHANGE dimensions_z dimensions_z DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE printer CHANGE volume_min volume_min DOUBLE PRECISION NOT NULL, CHANGE dimensions_max_x dimensions_max_x DOUBLE PRECISION DEFAULT NULL, CHANGE dimensions_max_y dimensions_max_y DOUBLE PRECISION DEFAULT NULL, CHANGE dimensions_max_z dimensions_max_z DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item_print CHANGE volume volume INT NOT NULL, CHANGE dimensions_x dimensions_x INT DEFAULT NULL, CHANGE dimensions_y dimensions_y INT DEFAULT NULL, CHANGE dimensions_z dimensions_z INT DEFAULT NULL');
        $this->addSql('ALTER TABLE printer CHANGE volume_min volume_min INT NOT NULL, CHANGE dimensions_max_x dimensions_max_x INT DEFAULT NULL, CHANGE dimensions_max_y dimensions_max_y INT DEFAULT NULL, CHANGE dimensions_max_z dimensions_max_z INT DEFAULT NULL');
    }
}
