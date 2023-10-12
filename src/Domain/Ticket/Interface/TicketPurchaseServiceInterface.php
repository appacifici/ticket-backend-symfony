<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Domain\Ticket\Object\TicketPurchaseSuccess;

interface TicketPurchaseServiceInterface
{
    /**
     * $ticketPurchases array of PurchaseDTO
     */
    public function purchaseTicket(TicketPurchaseInterface $ticketPurchases): TicketPurchaseSuccess;
}
