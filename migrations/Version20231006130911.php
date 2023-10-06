<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231006130911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO events (name,city,date_event) VALUE ('Cold Play Colorfull', 'Roma', '2024-07-22T21:00:00')");
        $this->addSql("INSERT INTO locations (name,address,event_id) VALUE ('Stadio Olimpico', 'Viale dei Gladiatori, 00135 Roma RM', 1)");

        $this->addSql("INSERT INTO sectors (name,total,purchased,place_type,event_id,location_id) VALUE (
                'Tribuna D\'onore', 
                '5', 
                0,
                2,
                1,
                1
            )
        ");

        $this->addSql("INSERT INTO sectors (name,total,purchased,place_type,event_id,location_id) VALUE (
            'Tribuna D\'onore', 
            '100', 
            0,
            1,
            1,
            1
        )
        ");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
