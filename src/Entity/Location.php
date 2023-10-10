<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "locations")]
#[ORM\UniqueConstraint(name: "unq_location", columns: ["address"])]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il nome della location')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"name", length: 150)]
    private string $name;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire indirizzo della location')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 3,
        max: 250,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"address", length: 250)]
    private string $address;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento all\'evento')]
    #[OneToOne(targetEntity: Event::class, inversedBy: 'location')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private Event $event;
    //---------------------------------------------------------------------------

    #[OneToMany(targetEntity: Sector::class, mappedBy: 'location')]
    private Collection $sectors;
    //---------------------------------------------------------------------------


    public function __construct()
    {
        $this->sectors = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

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
            $sector->setLocation($this);
        }

        return $this;
    }

    public function removeSector(Sector $sector): self
    {
        if ($this->sectors->removeElement($sector)) {
            // set the owning side to null (unless already changed)
            if ($sector->getLocation() === $this) {
                $sector->setLocation(null);
            }
        }

        return $this;
    }
}

//ES: Stadio Olimpico - Viale dei Gladiatori, 00135 Roma RM - 1 ( coldPlay )
