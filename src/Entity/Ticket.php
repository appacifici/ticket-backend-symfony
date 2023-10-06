<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

#[ORM\Table(name: "ticket")]
#[ORM\UniqueConstraint(name: "unq_ticket", columns: ["code","event_id"])]
#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"code", length: 150)]
    private string $code;

    #[ManyToOne(targetEntity: Location::class, inversedBy: 'tickets')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private $event;
    
    #[OneToOne(targetEntity: Place::class, inversedBy: 'ticket')]
    #[JoinColumn(name: 'place_id', referencedColumnName: 'id')]
    private $place;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEvent(): ?Location
    {
        return $this->event;
    }

    public function setEvent(?Location $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }


}