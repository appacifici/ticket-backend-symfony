<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "events")]
#[ORM\UniqueConstraint(name: "unq_event", columns: ["name","city","date_event"])]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il nome dell\'evento')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"name", length: 255)]
    private string $name;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire la città dell\'evento')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"city", length: 255)]
    private string $city;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire la data dell\'evento')]
    #[ORM\Column(name:"date_event", length: 255)]
    private DateTimeImmutable $date;
    //---------------------------------------------------------------------------

    #[OneToOne(targetEntity: Location::class, mappedBy: 'event')]
    private Location $location;
    //---------------------------------------------------------------------------

    #[OneToMany(targetEntity: Sector::class, mappedBy: 'event')]
    private Collection $sectors;
    //---------------------------------------------------------------------------

    #[OneToMany(targetEntity: Place::class, mappedBy: 'event')]
    private Collection $places;
    //---------------------------------------------------------------------------

    #[OneToMany(targetEntity: Ticket::class, mappedBy: 'event')]
    private Collection $tickets;
    //---------------------------------------------------------------------------

    public function __construct()
    {
        $this->sectors = new ArrayCollection();
        $this->places  = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        // unset the owning side of the relation if necessary
        if ($location === null && $this->location !== null) {
            $this->location->setEvent(null);
        }

        // set the owning side of the relation if necessary
        if ($location !== null && $location->getEvent() !== $this) {
            $location->setEvent($this);
        }

        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Sector>
     */
    public function getSectors(): Collection
    {
        return $this->sectors;
    }

    public function addSector(Sector $sector): self
    {
        if (!$this->sectors->contains($sector)) {
            $this->sectors->add($sector);
            $sector->setEvent($this);
        }

        return $this;
    }

    public function removeSector(Sector $sector): self
    {
        if ($this->sectors->removeElement($sector)) {
            // set the owning side to null (unless already changed)
            if ($sector->getEvent() === $this) {
                $sector->setEvent(null);
            }
        }

        return $this;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $place->setEvent($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getEvent() === $this) {
                $place->setEvent(null);
            }
        }

        return $this;
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                //$ticket->setEvent(null);
            }
        }

        return $this;
    }
}
