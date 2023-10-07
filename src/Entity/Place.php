<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlaceRepository;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

#[ORM\Table(name: "places")]
#[ORM\UniqueConstraint(name: "unq_place", columns: ["line","number","event_id","sector_id"])]
#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"line", length: 3)]
    private string $line;

    #[ORM\Column(name:"number", length: 3)]
    private string $number;

    #[ORM\Column(name:"price", type:"smallint", length: 255)]
    private $total;

    #[ORM\Column(name:"free", type:"smallint", length: 1)]
    private $free;

    #[ManyToOne(targetEntity: Event::class, inversedBy: 'places')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private $event;

    #[ManyToOne(targetEntity: Sector::class, inversedBy: 'places')]
    #[JoinColumn(name: 'sector_id', referencedColumnName: 'id')]
    private $sector;

    #[OneToOne(targetEntity: Ticket::class, mappedBy: 'place')]
    private $ticket;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLine(): ?string
    {
        return $this->line;
    }

    public function setLine(string $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getFree(): ?int
    {
        return $this->free;
    }

    public function setFree(int $free): self
    {
        $this->free = $free;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        // unset the owning side of the relation if necessary
        if ($ticket === null && $this->ticket !== null) {
            $this->ticket->setPlace(null);
        }

        // set the owning side of the relation if necessary
        if ($ticket !== null && $ticket->getPlace() !== $this) {
            $ticket->setPlace($this);
        }

        $this->ticket = $ticket;

        return $this;
    }
}

//ES: Fila 1 - Posto 5 - 50 - 1 ( colplay ) - 1 ( Tibuna D'onore )
