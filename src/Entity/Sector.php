<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SectorRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "sectors")]
#[ORM\UniqueConstraint(name: "unq_sector", columns: ["name","location_id","event_id"])]
#[ORM\Entity(repositoryClass: SectorRepository::class)]
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
    private int $id;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire la fila del posto')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"name", length: 150)]
    private string $name;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il totale di posto')]
    #[Assert\Type(
        type: 'int',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 2,
        max: 5,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"total", type:"smallint", length: 5)]
    private int $total;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il numero di posti prenotati')]
    #[Assert\Type(
        type: 'int',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 2,
        max: 5,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"purchased", type:"smallint", length: 5)]
    private int $purchased;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il numero il tipo di settore')]
    #[Assert\Type(
        type: 'int',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 1,
        max: 1,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"place_type", type:"smallint", length: 1)]
    private int $placeType;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento all\'evento')]
    #[ManyToOne(targetEntity: Event::class, inversedBy: 'sectors')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private Event $event;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento alla location')]
    #[ManyToOne(targetEntity: Location::class, inversedBy: 'sectors')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private Location $location;
    //---------------------------------------------------------------------------

    #[OneToMany(targetEntity: Place::class, mappedBy: 'sector')]
    private Collection $places;
    //---------------------------------------------------------------------------

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

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPurchased(): ?int
    {
        return $this->purchased;
    }

    public function setPurchased(int $purchased): self
    {
        $this->purchased = $purchased;

        return $this;
    }

    public function getPlaceType(): ?int
    {
        return $this->placeType;
    }

    public function setPlaceType(int $placeType): self
    {
        $this->placeType = $placeType;

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

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
            $place->setSector($this);
        }

        return $this;
    }
}

//ES: Tribuna D'onore - 5000 - 0 - ASSIGNED_PLACE - 1 ( coldplay ) - 1 ( Stadio Olimpico )
//ES: Prato - 25000 - 0 - FREE_PLACE - 1 ( coldplay ) - 1 ( Stadio Olimpico )
