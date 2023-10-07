<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Service;

use App\Domain\Ticket\DTO\TicketPurchaseDTO;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Ticket\Exception\TicketSeviceException;
use App\Domain\Ticket\Interface\TicketServiceInterface;

class TicketService implements TicketServiceInterface {

    /**
     * Ho messo i limiti come costanti in quanto pensando ad uno sviluppo successivo del sistema la maniera più corretta sarebbe creare un altra entita
     * che gestisca questo tipo di filtro, pensando magari che in base all'utente se base o premium possano avere diverse opzioni di scelta dove magari
     * l'utente premium puo acquistare fino a 5 biglietti, oppure quello base non può acquistare più di un evento, quindi per mancanza di tempo faccio
     * una cosa base lavorando con semplici costanti, che però inserisco nel servizio che si occupera dei controlli necessari per procedere all'acquisto
     */
    const MAX_TICKET_TRANSACTION = 2;
    const MAX_EVENT_TRANSACTION  = 2;

    private array $events;
    private array $ticketFromEvent;

    public function __construct(
        private EntityManagerInterface $doctrine
    )
    {                
    }

    /**
     * $ticketPurchases array of PurchaseDTO
     */
    public function getEventTicket( TicketPurchaseDTO $ticketPurchases ): bool {
        $purchases = $ticketPurchases->getPurchases();
        foreach( $purchases AS $purchase ) {
            $eventId                 = $purchase->getEvent()->getId();
            $this->events[$eventId]  = $purchase->getEvent();            
            $this->ticketFromEvent[$eventId] = isset($this->ticketFromEvent[$eventId]) ? $this->ticketFromEvent[$eventId]+1 : 1;
        }        

        $this->checkLimitPurchase();
        return true;
    }

    private function checkLimitPurchase(): bool {
        if( self::MAX_EVENT_TRANSACTION != -1 && count( $this->ticketFromEvent ) > self::MAX_EVENT_TRANSACTION ) {
            $customException =  new TicketSeviceException();            
            throw $customException;
        }

        foreach( $this->ticketFromEvent AS $eventId => $total )
        if( self::MAX_TICKET_TRANSACTION != -1 && $total > self::MAX_TICKET_TRANSACTION ) {
            // throw new CustomException('Ticket for event Limit Exceeded');
            $ticketSeviceException =  new TicketSeviceException();
            $ticketSeviceException->setEvent( $this->events[$eventId] );
            throw $ticketSeviceException;
        }

        return true;
    }

}