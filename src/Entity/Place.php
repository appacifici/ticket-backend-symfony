<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "places")]
#[ORM\UniqueConstraint(name: "unq_place", columns: ["line","number","event_id","sector_id"])]
#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire la fila del posto')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 1,
        max: 3,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"line", length: 3)]
    private string $line;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire numero del posto')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 1,
        max: 3,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"number", length: 3)]
    private string $number;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il prezzo del posto')]
    #[Assert\Type(
        type: 'smallint',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[ORM\Column(name:"price", type:"smallint")]
    private int $price;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire se il posto è libero')]
    #[Assert\Type(
        type: 'smallint',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[ORM\Column(name:"free", type:"smallint", length: 1, options: ["default" => 1])]
    private int $free;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento all\'evento')]
    #[ManyToOne(targetEntity: Event::class, inversedBy: 'places')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private Event $event;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento del settore')]
    #[ManyToOne(targetEntity: Sector::class, inversedBy: 'places')]
    #[JoinColumn(name: 'sector_id', referencedColumnName: 'id')]
    private Sector $sector;
    //---------------------------------------------------------------------------

    #[OneToOne(targetEntity: Ticket::class, mappedBy: 'place')]
    private Ticket $ticket;
    //---------------------------------------------------------------------------

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

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
