<?php

declare(strict_types=1);

namespace App\Domain\Ticket\DTO;

use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

class TicketPurchaseDTO implements TicketPurchaseInterface {

    protected readonly Event $event;
    protected readonly Place $place;
    protected readonly User  $user;

    public function __construct( 
        private EntityManagerInterface $doctrine )
    {        
    }

    public function create(array $data): void 
    {                
        Assert::notNull($data, 'eventId');
        Assert::notNull($data, 'placeId');
        Assert::notNull($data, 'userId');

        $event  = $this->doctrine->getRepository( Event::class )->findOneBy( [ 'id' => $data['userId'] ] ); 
        $place  = $this->doctrine->getRepository( Place::class )->findOneBy( [ 'id' => $data['userId'] ] ); 
        $user   = $this->doctrine->getRepository( User::class  )->findOneBy( [ 'id' => $data['userId'] ] ); 

        $this->event    = $event;
        $this->place    = $place;
        $this->user     = $user;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}