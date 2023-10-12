<?php

declare(strict_types=1);

namespace App\Domain\Ticket\DTO;

use App\Domain\Ticket\Interface\PurchaseInterface;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Sector;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Ticket\Exception\TicketPurchaseDTOException;

class PurchaseDTO implements PurchaseInterface
{    
    protected readonly Event    $event; /** @phpstan-ignore-line */
    protected readonly Sector   $sector; /** @phpstan-ignore-line */
    protected readonly ?Place   $place; /** @phpstan-ignore-line */
    protected readonly User     $user; /** @phpstan-ignore-line */
    protected readonly int      $placeType; /** @phpstan-ignore-line */
    /** @phpstan-ignore-end */


    public function __construct(
        private EntityManagerInterface $doctrine
    ) {
    }

    public function create(array $data, int $index): self
    {
        $ticketSeviceException =  new TicketPurchaseDTOException('Invalid get entity whit request ticket');

        $user   = $this->doctrine->getRepository(User::class)->findOneBy([ 'id' => $data['userId'] ]);
        if (empty($user)) {
            $ticketSeviceException->setNotFoundEntityUser($data['userId'], $index);
        }

        $event  = $this->doctrine->getRepository(Event::class)->findOneBy([ 'id' => $data['eventId'] ]);
        if (empty($event)) {
            $ticketSeviceException->setNotFoundEntityEvent($data['eventId'], $index);
        }

        $sector  = $this->doctrine->getRepository(Sector::class)->findOneBy([ 'id' => $data['sectorId'] ]);
        if (empty($sector)) {
            $ticketSeviceException->setNotFoundEntitySector($data['sectorId'], $index);
        }

        $place  = null;
        if ($data['placeType'] == Sector::ASSIGNED_PLACE && !empty($data['placeId'])) {
            $place  = $this->doctrine->getRepository(Place::class)->findOneBy([ 'id' => $data['placeId'] ]);
            if (empty($place)) {
                $ticketSeviceException->setNotFoundEntityPlace($data['placeId'], $index);
            }
        }

        if (
            $ticketSeviceException->getNotFoundEntityUser() !== 0 ||
            $ticketSeviceException->getNotFoundEntityEvent() !== 0 ||
            $ticketSeviceException->getNotFoundEntitySector() !== 0||
            $ticketSeviceException->getNotFoundEntityPlace() !== 0 
        ) {
            throw $ticketSeviceException;            
        }


        /** 
        * @psalm-suppress PossiblyNullPropertyAssignmentValue
        * Soppresso questo errore in quanto psalm non riesce a capire che non è possibile arrivare a queste assegnazioni se le variabili sono null
        * perche uscirebbe prima lanciando l'eccezione 
        */
        $this->event        = $event;
        /** 
        * @psalm-suppress PossiblyNullPropertyAssignmentValue
        */
        $this->sector       = $sector;
        /** 
        * @psalm-suppress PossiblyNullPropertyAssignmentValue
        */
        $this->place        = $place;
        /** 
        * @psalm-suppress PossiblyNullPropertyAssignmentValue
        */
        $this->user         = $user;
        /** 
        * @psalm-suppress PossiblyNullPropertyAssignmentValue
        */
        $this->placeType    = $data['placeType'];
        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getSector(): Sector
    {
        return $this->sector;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPlaceType(): int
    {
        return $this->placeType;
    }
}
