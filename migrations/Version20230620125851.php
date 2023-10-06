<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230620125851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX exist_push_id ON push_events (push_id, processed, to_be_processed)');
        $this->addSql('ALTER TABLE push_report CHANGE view view INT DEFAULT 0');
        $this->addSql('CREATE INDEX exist_push_id ON push_report (push_id, processed, to_be_processed)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX exist_push_id ON push_events');
        $this->addSql('DROP INDEX exist_push_id ON push_report');
        $this->addSql('ALTER TABLE push_report CHANGE view view INT DEFAULT NULL');
    }
}
