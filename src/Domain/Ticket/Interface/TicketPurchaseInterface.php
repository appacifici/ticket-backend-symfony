<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

interface TicketPurchaseInterface
{
    public function create(array $data): self; 
    public function getPurchases(): array;
}
