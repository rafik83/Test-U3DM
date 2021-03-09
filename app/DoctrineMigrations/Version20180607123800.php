<?php declare(strict_types = 1);

namespace Application\Migrations;

use AppBundle\Entity\Setting;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180607123800 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add new settings
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::DEFAULT_PRODUCTION_TIME.'", "'.Setting::TYPE_INT.'", "Délai de production (jours ouvrés)", NULL, 2, NULL)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // delete the new settings
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::DEFAULT_PRODUCTION_TIME.'\'');
    }
}
