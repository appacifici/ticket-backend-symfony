<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Sector;
use App\Entity\Ticket;
use App\Entity\User;

interface TickerServiceInterface
{
    public function generateTicket(Event $event, Sector $sector, Place $place, User $user, int $ticketIndex): Ticket;
}
