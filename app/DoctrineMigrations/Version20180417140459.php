<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180417140459 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, street1 VARCHAR(255) NOT NULL, street2 VARCHAR(255) DEFAULT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE maker ADD address_id INT DEFAULT NULL, DROP phone_number, DROP address_street1, DROP address_street2, DROP address_zipcode, DROP address_city, DROP address_country');
        $this->addSql('ALTER TABLE maker ADD CONSTRAINT FK_C6197FB4F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_C6197FB4F5B7AF75 ON maker (address_id)');
        $this->addSql('ALTER TABLE user ADD default_billing_address_id INT DEFAULT NULL, ADD default_shipping_address_id INT DEFAULT NULL, DROP company, DROP phone_number, DROP address_street1, DROP address_street2, DROP address_zipcode, DROP address_city, DROP address_country');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491995CE08 FOREIGN KEY (default_billing_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E4901476 FOREIGN KEY (default_shipping_address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491995CE08 ON user (default_billing_address_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649E4901476 ON user (default_shipping_address_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker DROP FOREIGN KEY FK_C6197FB4F5B7AF75');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491995CE08');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E4901476');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP INDEX IDX_C6197FB4F5B7AF75 ON maker');
        $this->addSql('ALTER TABLE maker ADD phone_number VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_street1 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_street2 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_zipcode VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_city VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_country VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, DROP address_id');
        $this->addSql('DROP INDEX IDX_8D93D6491995CE08 ON user');
        $this->addSql('DROP INDEX IDX_8D93D649E4901476 ON user');
        $this->addSql('ALTER TABLE user ADD company VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD phone_number VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_street1 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_street2 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_zipcode VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_city VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD address_country VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, DROP default_billing_address_id, DROP default_shipping_address_id');
    }
}
