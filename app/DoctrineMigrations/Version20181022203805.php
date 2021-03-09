<?php declare(strict_types=1);

namespace Application\Migrations;

use AppBundle\Entity\Setting;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181022203805 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add new settings
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::QUOTATION_AGREEMENT_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal du bon pour accord sur devis designer (jours)", NULL, 7, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::ONE_WEEK_PROJECT_CLOSURE_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal de réponse designer si réalisation sous 1 semaine (jours)", NULL, 3, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::FIFTEEN_DAYS_PROJECT_CLOSURE_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal de réponse designer si réalisation sous 15 jours (jours)", NULL, 5, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::ONE_MONTH_PROJECT_CLOSURE_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal de réponse designer si réalisation sous 1 mois (jours)", NULL, 7, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::THREE_MONTHS_PROJECT_CLOSURE_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal de réponse designer si réalisation sous 3 mois (jours)", NULL, 10, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::MORE_THAN_THREE_MONTHS_PROJECT_CLOSURE_TIME.'", "'.Setting::TYPE_INT.'", "Délai maximal de réponse designer si réalisation sous plus de 3 mois (jours)", NULL, 15, NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // delete the new settings
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::QUOTATION_AGREEMENT_TIME.'\'');
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::ONE_WEEK_PROJECT_CLOSURE_TIME.'\'');
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::FIFTEEN_DAYS_PROJECT_CLOSURE_TIME.'\'');
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::ONE_MONTH_PROJECT_CLOSURE_TIME.'\'');
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::THREE_MONTHS_PROJECT_CLOSURE_TIME.'\'');
        $this->addSql('DELETE FROM setting WHERE `key` = \''.Setting::MORE_THAN_THREE_MONTHS_PROJECT_CLOSURE_TIME.'\'');
    }
}
