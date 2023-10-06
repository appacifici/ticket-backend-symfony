<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612175147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerts (id INT AUTO_INCREMENT NOT NULL, process_name VARCHAR(255) NOT NULL, process VARCHAR(255) NOT NULL, childProcess VARCHAR(255) DEFAULT NULL, alert LONGTEXT NOT NULL, debug LONGTEXT NOT NULL, error LONGTEXT NOT NULL, general LONGTEXT NOT NULL, call_data LONGTEXT NOT NULL, call_response LONGTEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE push_events (id BIGINT AUTO_INCREMENT NOT NULL, push_id INT NOT NULL, site VARCHAR(45) NOT NULL, push_type VARCHAR(45) NOT NULL, push_name VARCHAR(45) NOT NULL, processed SMALLINT DEFAULT 0, to_be_processed SMALLINT DEFAULT 0, process_name VARCHAR(255) DEFAULT NULL, view INT DEFAULT NULL, start_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, channel VARCHAR(255) DEFAULT NULL, url VARCHAR(1024) DEFAULT NULL, t1 VARCHAR(255) DEFAULT NULL, t2 VARCHAR(255) DEFAULT NULL, delivered INT DEFAULT 0, click INT DEFAULT 0, INDEX toBeProcessed (to_be_processed), INDEX processed (processed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE push_report (id BIGINT AUTO_INCREMENT NOT NULL, push_id BIGINT NOT NULL, site VARCHAR(45) NOT NULL, push_type VARCHAR(45) NOT NULL, processed SMALLINT DEFAULT 0, to_be_processed SMALLINT DEFAULT 0, delivered INT DEFAULT 0, click INT DEFAULT 0, view INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, INDEX pushId (push_id), INDEX toBeProcessed (to_be_processed), INDEX processed (processed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_tracker (id INT AUTO_INCREMENT NOT NULL, process_name VARCHAR(255) NOT NULL, process VARCHAR(255) NOT NULL, childProcess VARCHAR(255) DEFAULT \'0\', start_time INT NOT NULL, end_time INT DEFAULT NULL, memory INT NOT NULL, duration INT NOT NULL, ensure_stopped INT DEFAULT NULL, origin VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE alerts');
        $this->addSql('DROP TABLE push_events');
        $this->addSql('DROP TABLE push_report');
        $this->addSql('DROP TABLE time_tracker');
    }
}
