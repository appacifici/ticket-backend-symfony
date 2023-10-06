<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SectorAreaRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[ORM\Table(name: "sectors")]
#[ORM\UniqueConstraint(name: "unq_sector", columns: ["name","location_id","event_id"])]
#[ORM\Entity(repositoryClass: SectorAreaRepository::class)]
class Sector
{
    const FREE_PLACE        = 1;
    const ASSIGNED_PLACE    = 2;
    const PLACE = [
        'Libero'    => self::FREE_PLACE,
        'Assegnato' => self::ASSIGNED_PLACE,
    ];

    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"name", length: 150)]
    private string $name;

    #[ORM\Column(name:"total", type:"smallint", length: 255)]
    private $total;

    #[ORM\Column(name:"purchased",type:"smallint", length: 255)]
    private $purchased;

    #[ORM\Column(name:"place_type",type:"smallint", length: 1)]
    private $placeType;

    #[ManyToOne(targetEntity: Event::class, inversedBy: 'sectors')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private $event;

    #[ManyToOne(targetEntity: Location::class, inversedBy: 'sectors')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private $location;

    #[OneToMany(targetEntity: Place::class, mappedBy: 'sector')]
    private Collection $places;

    public function __construct()
    {
        $this->places = new ArrayCollection();
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

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPurchased(): ?string
    {
        return $this->purchased;
    }

    public function setPurchased(string $purchased): self
    {
        $this->purchased = $purchased;

        return $this;
    }

    public function getPlaceType(): ?string
    {
        return $this->placeType;
    }

    public function setPlaceType(string $placeType): self
    {
        $this->placeType = $placeType;

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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Place>
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $place->setSector($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getSector() === $this) {
                $place->setSector(null);
            }
        }

        return $this;
    }
}

//ES: Tribuna D'onore - 5000 - 0 - ASSIGNED_PLACE - 1 ( coldplay ) - 1 ( Stadio Olimpico )
//ES: Prato - 25000 - 0 - FREE_PLACE - 1 ( coldplay ) - 1 ( Stadio Olimpico )