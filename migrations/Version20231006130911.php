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

        //Settore Tribuna ID 1 - Posti Totali 5
        $this->addSql("INSERT INTO sectors (name,total,purchased,place_type,event_id,location_id) VALUE (
                'Tribuna D\'onore', 
                '5', 
                0,
                2,
                1,
                1
            )
        ");

        //Settore Prato ID 2 - Posti totali 100
        $this->addSql("INSERT INTO sectors (name,total,purchased,place_type,event_id,location_id) VALUE (
            'Prato', 
            '100', 
            0,
            1,
            1,
            1
        )
        ");

        //Tribuna Fila 1 Posto 5A
        $this->addSql("INSERT INTO places (line,number,price,free,event_id,sector_id) VALUE (
            '1', 
            '5A', 
            50,
            0,
            1,
            1
        )
        ");

        //Tribuna Fila 1 Posto 6A
        $this->addSql("INSERT INTO places (line,number,price,free,event_id,sector_id) VALUE (
            '1', 
            '6A', 
            50,
            0,
            1,
            1
        )
        ");

        //Tribuna Fila 1 Posto 7A
        $this->addSql("INSERT INTO places (line,number,price,free,event_id,sector_id) VALUE (
            '1', 
            '7A', 
            50,
            0,
            1,
            1
        )
        ");
         
        //Tribuna Fila 1 Posto 8A
        $this->addSql("INSERT INTO places (line,number,price,free,event_id,sector_id) VALUE (
            '1', 
            '8A', 
            50,
            0,
            1,
            1
        )
        ");

        //Tribuna Fila 1 Posto 9A
        $this->addSql("INSERT INTO places (line,number,price,free,event_id,sector_id) VALUE (
            '1', 
            '9A', 
            50,
            0,
            1,
            1
        )
        ");

        $this->addSql("INSERT INTO user (id,name,surname,email,username,password) VALUE (
            1,
            'Alessandro', 
            'Pacifici', 
            'aleweb87@gmail.com',
            'aleweb87',
            'd6abe0fa83460ca820337f6db4fe3403'
        )
        ");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("set foreign_key_checks=0");
        $this->addSql("TRUNCATE places");
        $this->addSql("TRUNCATE sectors");
        $this->addSql("TRUNCATE locations");
        $this->addSql("TRUNCATE events");
        $this->addSql("set foreign_key_checks=1");
    }
}
