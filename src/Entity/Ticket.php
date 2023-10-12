<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use App\Entity\Sector;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "ticket")]
#[ORM\UniqueConstraint(name: "unq_ticket", columns: ["code","sector_id","event_id"])]
#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il codice biglietto')]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]
    #[Assert\Length(
        min: 20,
        max: 150,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column(name:"code", length: 150)]
    private string $code;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento all\'evento')]
    #[ManyToOne(targetEntity: Event::class, inversedBy: 'tickets')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private Event $event;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento al settore')]
    #[ManyToOne(targetEntity: Sector::class, inversedBy: 'tickets')]
    #[JoinColumn(name: 'sector_id', referencedColumnName: 'id')]
    private Sector $sector;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento al posto')]
    #[ManyToOne(targetEntity: Place::class, inversedBy: 'ticket')]
    #[JoinColumn(name: 'place_id', referencedColumnName: 'id')]
    private ?Place $place;
    //---------------------------------------------------------------------------

    #[Assert\NotBlank(message: 'Inserire il riferimento all\'utente')]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;
    //---------------------------------------------------------------------------

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

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getSector(): Sector
    {
        return $this->sector;
    }

    public function setSector(Sector $sector): self
    {
        $this->sector = $sector;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
