<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Domain\Ticket\DTO\TicketPurchaseDTO;

interface TicketPurchaseServiceInterface {

    /**
     * $ticketPurchases array of PurchaseDTO
     */
    public function purchaseTicket( TicketPurchaseDTO $ticketPurchases ):bool;
}