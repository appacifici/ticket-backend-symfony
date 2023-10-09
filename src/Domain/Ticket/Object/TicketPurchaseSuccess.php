<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Object;

use App\Entity\Ticket;

class TicketPurchaseSuccess {

    private $tickets = [];

    public function __construct(         
    )
    {                
    }

    public function getTickets() {
        return $this->tickets;
    }

    public function addTicket( Ticket $ticket ) {
        return $this->tickets[] = $ticket;
    }
    

}