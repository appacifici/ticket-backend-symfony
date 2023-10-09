<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Domain\Ticket\DTO\TicketPurchaseDTO;
use App\Domain\Ticket\Object\TicketPurchaseSuccess;

interface TicketPurchaseServiceInterface
{
    /**
     * $ticketPurchases array of PurchaseDTO
     */
    public function purchaseTicket(TicketPurchaseDTO $ticketPurchases): TicketPurchaseSuccess;
}
