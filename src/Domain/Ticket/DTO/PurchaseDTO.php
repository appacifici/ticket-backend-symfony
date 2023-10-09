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
    protected readonly Event $event;
    protected readonly Sector $sector;
    protected readonly ?Place $place;
    protected readonly User $user;
    protected readonly int $placeType;

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
            !empty($ticketSeviceException->getNotFoundEntityUser()) ||
            !empty($ticketSeviceException->getNotFoundEntityEvent() ||
            !empty($ticketSeviceException->getNotFoundEntitySector() ||
            !empty($ticketSeviceException->getNotFoundEntityPlace())))
        ) {
            throw $ticketSeviceException;
        }

        $this->event        = $event;
        $this->sector       = $sector;
        $this->place        = $place;
        $this->user         = $user;
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
