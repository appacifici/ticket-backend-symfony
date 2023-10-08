<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231008214149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerts (id INT AUTO_INCREMENT NOT NULL, process_name VARCHAR(255) NOT NULL, process VARCHAR(255) NOT NULL, childProcess VARCHAR(255) DEFAULT NULL, alert LONGTEXT NOT NULL, debug LONGTEXT NOT NULL, error LONGTEXT NOT NULL, general LONGTEXT NOT NULL, call_data LONGTEXT NOT NULL, call_response LONGTEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, date_event DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, UNIQUE INDEX unq_event (name, city, date_event), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE locations (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, address VARCHAR(250) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_17E64ABA71F7E88B (event_id), UNIQUE INDEX unq_location (address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE places (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, sector_id INT DEFAULT NULL, line VARCHAR(3) NOT NULL, number VARCHAR(3) NOT NULL, price SMALLINT NOT NULL, free SMALLINT DEFAULT 1 NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, INDEX IDX_FEAF6C5571F7E88B (event_id), INDEX IDX_FEAF6C55DE95C867 (sector_id), UNIQUE INDEX unq_place (line, number, event_id, sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sectors (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, location_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, total SMALLINT NOT NULL, purchased SMALLINT NOT NULL, place_type SMALLINT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, INDEX IDX_B594069871F7E88B (event_id), INDEX IDX_B594069864D218E (location_id), UNIQUE INDEX unq_sector (name, location_id, event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, sector_id INT DEFAULT NULL, place_id INT DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, code VARCHAR(150) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, INDEX IDX_97A0ADA371F7E88B (event_id), INDEX IDX_97A0ADA3DE95C867 (sector_id), UNIQUE INDEX UNIQ_97A0ADA3DA6A219 (place_id), INDEX IDX_97A0ADA3A76ED395 (user_id), UNIQUE INDEX unq_ticket (code, sector_id, event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_tracker (id INT AUTO_INCREMENT NOT NULL, process_name VARCHAR(255) NOT NULL, process VARCHAR(255) NOT NULL, childProcess VARCHAR(255) DEFAULT \'0\', start_time INT NOT NULL, end_time INT DEFAULT NULL, memory INT NOT NULL, duration INT NOT NULL, ensure_stopped INT DEFAULT NULL, origin VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(500) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, update_at DATETIME DEFAULT NULL, INDEX IDX_EAA81A4C71F7E88B (event_id), INDEX IDX_EAA81A4C700047D2 (ticket_id), INDEX IDX_EAA81A4CA76ED395 (user_id), UNIQUE INDEX unq_ticket (ticket_id, event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX unq_user_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE locations ADD CONSTRAINT FK_17E64ABA71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT FK_FEAF6C5571F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT FK_FEAF6C55DE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id)');
        $this->addSql('ALTER TABLE sectors ADD CONSTRAINT FK_B594069871F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE sectors ADD CONSTRAINT FK_B594069864D218E FOREIGN KEY (location_id) REFERENCES locations (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA371F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3DE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3DA6A219 FOREIGN KEY (place_id) REFERENCES places (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE locations DROP FOREIGN KEY FK_17E64ABA71F7E88B');
        $this->addSql('ALTER TABLE places DROP FOREIGN KEY FK_FEAF6C5571F7E88B');
        $this->addSql('ALTER TABLE places DROP FOREIGN KEY FK_FEAF6C55DE95C867');
        $this->addSql('ALTER TABLE sectors DROP FOREIGN KEY FK_B594069871F7E88B');
        $this->addSql('ALTER TABLE sectors DROP FOREIGN KEY FK_B594069864D218E');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA371F7E88B');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3DE95C867');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3DA6A219');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C71F7E88B');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C700047D2');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CA76ED395');
        $this->addSql('DROP TABLE alerts');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE places');
        $this->addSql('DROP TABLE sectors');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE time_tracker');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE user');
    }
}
