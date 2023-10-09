<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Ticket\Interface\TickerServiceInterface;
use App\Entity\Ticket;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Sector;
use App\Entity\User;
use Exception;

/**
 * Classe spacifica che gestisce il solo processo di acquisti dei biglietti
 * Potendo crescere di molto ho preferito fare un servizio specifico a differenza del servizio globale per il domain Sector
 */
class TicketService implements TickerServiceInterface
{
    public function __construct(
        private EntityManagerInterface $doctrine,
    ) {
    }

    /**
     * Genera un nuovo ticket e inserisce il record nel db
     */
    public function generateTicket(Event $event, Sector $sector, ?Place $place, User $user, int $ticketIndex): Ticket
    {
        $code = md5($event->getId() . $sector->getId() . $ticketIndex . time() . rand(0, 1000));

        $ticket = new Ticket();
        //Scommentare la riga sotto per testare il rollback
        //$ticket->setEvent($purchase->getEvent());
        $ticket->setEvent($event);
        $ticket->setSector($sector);
        $ticket->setPlace($place);
        $ticket->setuser($user);
        $ticket->setCode($code);
        $this->doctrine->persist($ticket);
        $this->doctrine->flush();

        return $ticket;
    }
}
