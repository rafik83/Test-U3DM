<?php declare(strict_types = 1);

namespace Application\Migrations;

use AppBundle\Entity\Setting;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180424083929 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_9F74B8984E645A7E (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        // initialize new settings
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::DEFAULT_TAX_RATE.'", "'.Setting::TYPE_PERCENT.'", "TVA par défaut", NULL, NULL, 20.0)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::DEFAULT_COMMISSION_RATE.'", "'.Setting::TYPE_PERCENT.'", "Commission par défaut", NULL, NULL, 5.0)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::FEE_PERCENT.'", "'.Setting::TYPE_PERCENT.'", "Frais de service variables", NULL, NULL, 5.0)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::FEE_THRESHOLD.'", "'.Setting::TYPE_MONEY.'", "Seuil pour frais de service fixes", NULL, 10000, NULL)');
        $this->addSql('INSERT INTO setting (`key`, `type`, `name`, `string_value`, `int_value`, `float_value`) VALUES ("'.Setting::FEE_AMOUNT.'", "'.Setting::TYPE_MONEY.'", "Frais de service fixes", NULL, 1000, NULL)');

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE setting');
    }
}
