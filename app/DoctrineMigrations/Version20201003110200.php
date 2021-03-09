<?php declare(strict_types=1);

namespace Application\Migrations;

use AppBundle\Entity\Setting;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201003110200 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add new settings
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::MESSAGE_MODERATE_TEXT.'", "'.Setting::TYPE_STRING.'", "Texte de modération par la plateforme", "(Modéré par la plateforme. Pour le bon déroulement des projets et la sécurité des informations. Merci d\'utiliser le module de communication de la plateforme)", NULL, NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // delete the new settings
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::MESSAGE_MODERATE_TEXT.'\'');
    }
}
