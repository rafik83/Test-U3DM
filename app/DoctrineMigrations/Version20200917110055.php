<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917110055 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE moderation_rules ADD created_by_id INT NOT NULL, ADD last_modified_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderation_rules ADD CONSTRAINT FK_7AC07EA8B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrator (id)');
        $this->addSql('ALTER TABLE moderation_rules ADD CONSTRAINT FK_7AC07EA8F703974A FOREIGN KEY (last_modified_by_id) REFERENCES administrator (id)');
        $this->addSql('CREATE INDEX IDX_7AC07EA8B03A8386 ON moderation_rules (created_by_id)');
        $this->addSql('CREATE INDEX IDX_7AC07EA8F703974A ON moderation_rules (last_modified_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE moderation_rules DROP FOREIGN KEY FK_7AC07EA8B03A8386');
        $this->addSql('ALTER TABLE moderation_rules DROP FOREIGN KEY FK_7AC07EA8F703974A');
        $this->addSql('DROP INDEX IDX_7AC07EA8B03A8386 ON moderation_rules');
        $this->addSql('DROP INDEX IDX_7AC07EA8F703974A ON moderation_rules');
        $this->addSql('ALTER TABLE moderation_rules DROP created_by_id, DROP last_modified_by_id');
    }
}
