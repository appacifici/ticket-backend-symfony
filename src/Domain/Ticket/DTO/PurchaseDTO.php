<?php

declare(strict_types=1);

namespace App\Domain\Ticket\DTO;

use App\Domain\Ticket\Interface\PurchaseInterface;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Sector;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Ticket\Exception\PurchaseDTOException;

class PurchaseDTO implements PurchaseInterface
{
    protected readonly Event $event;
    protected readonly ?Place $place;
    protected readonly User $user;

    public function __construct(
        private EntityManagerInterface $doctrine
    ) {
    }

    public function create(array $data): self
    {        
        $ticketSeviceException =  new PurchaseDTOException('Invalid get entity whit request ticket');

        $user   = $this->doctrine->getRepository(User::class)->findOneBy([ 'id' => $data['userId'] ]);
        if( empty( $user ) ) {            
            $ticketSeviceException->setNotFoundEntityUser( $data['userId'] );            
        }

        $event  = $this->doctrine->getRepository(Event::class)->findOneBy([ 'id' => $data['eventId'] ]);
        if( empty( $event ) ) {            
            $ticketSeviceException->setNotFoundEntityEvent( $data['eventId'] );            
        }                

        $place  = null;
        if( $data['placeType'] == Sector::ASSIGNED_PLACE && !empty( $data['place'] ) ) {
            $place  = $this->doctrine->getRepository(Place::class)->findOneBy([ 'id' => $data['place'] ]);         
            if( empty( $place ) ) {                   
                $ticketSeviceException->setNotFoundEntityPlace( $data['place'] );            
            }                
        }

        if( !empty( $ticketSeviceException->getNotFoundEntityUser() ) || 
            !empty( $ticketSeviceException->getNotFoundEntityEvent() || 
            !empty( $ticketSeviceException->getNotFoundEntityPlace() ))){
            throw $ticketSeviceException;
        }
        
        $this->event    = $event;
        $this->place    = $place;
        $this->user     = $user;
        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
