<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Response;

use App\Domain\Ticket\Object\TicketPurchaseSuccess;

class TicketPurchaseResponse
{
    private array $response;

    public static function ticketPurchaseSuccessResponse(TicketPurchaseSuccess $ticketPurchaseSuccess): self
    {
        $self                                                = new self();
        $self->response['success']                           = true;
        foreach( $ticketPurchaseSuccess->getTickets() AS $ticket ) {
            $self->response['tickets'][]  = $ticket->getCode();
        }
        return $self;
    }

    public function serialize(): array
    {
        return $this->response;
    }
}