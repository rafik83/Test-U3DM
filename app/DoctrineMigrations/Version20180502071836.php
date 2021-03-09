<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180502071836 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker ADD pickup_address_id INT DEFAULT NULL, ADD pickup TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE maker ADD CONSTRAINT FK_C6197FB4A72D874B FOREIGN KEY (pickup_address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_C6197FB4A72D874B ON maker (pickup_address_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE maker DROP FOREIGN KEY FK_C6197FB4A72D874B');
        $this->addSql('DROP INDEX IDX_C6197FB4A72D874B ON maker');
        $this->addSql('ALTER TABLE maker DROP pickup_address_id, DROP pickup');
    }
}
