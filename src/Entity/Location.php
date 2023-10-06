<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

#[ORM\Table(name: "locations")]
#[ORM\UniqueConstraint(name: "unq_location", columns: ["address"])]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"name", length: 150)]
    private string $name;

    #[ORM\Column(name:"address", length: 250)]
    private string $address;

    #[OneToOne(targetEntity: Event::class, inversedBy: 'location')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private $event;

    #[OneToMany(targetEntity: Sector::class, mappedBy: 'location')]
    private Collection $sectors;

    public function __construct() {
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

    public function setEvent(?Event $event): self
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