<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410140923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color ADD name_long VARCHAR(255) DEFAULT NULL, ADD name_english VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL, CHANGE name name_short VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ref_finishing ADD name_long VARCHAR(255) DEFAULT NULL, ADD name_english VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL, CHANGE name name_short VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ref_layer ADD definition VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ref_material ADD name_long VARCHAR(255) DEFAULT NULL, ADD name_english VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL, CHANGE name name_short VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ref_technology ADD name_long VARCHAR(255) DEFAULT NULL, ADD name_english VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL, CHANGE name name_short VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ref_color DROP name_long, DROP name_english, DROP description, DROP link, CHANGE name_short name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ref_finishing DROP name_long, DROP name_english, DROP description, DROP link, CHANGE name_short name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ref_layer DROP definition, DROP description, DROP link');
        $this->addSql('ALTER TABLE ref_material DROP name_long, DROP name_english, DROP description, DROP link, CHANGE name_short name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ref_technology DROP name_long, DROP name_english, DROP description, DROP link, CHANGE name_short name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
