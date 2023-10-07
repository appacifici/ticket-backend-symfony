<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Domain\Ticket\DTO\TicketPurchaseDTO;

interface TicketServiceInterface {

    /**
     * $ticketPurchases array of PurchaseDTO
     */
    public function getEventTicket( TicketPurchaseDTO $ticketPurchases ):bool;
}