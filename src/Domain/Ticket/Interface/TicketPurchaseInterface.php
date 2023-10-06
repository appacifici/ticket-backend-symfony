<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Interface;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Place;

interface TicketPurchaseInterface {
    public function create(array $data): void;
    public function getEvent(): Event;    
    public function getPlace(): Place;
    public function getUser(): User;
}