<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Table(name: "transactions")]
#[ORM\UniqueConstraint(name: "unq_ticket", columns: ["ticket_id","event_id"])]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    //Codice identificativo transazione
    #[ORM\Column(name:"transaction_hash", length: 500)]
    private string $transactionHash;

    //Evento della transazione
    #[ManyToOne(targetEntity: Event::class)]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    private $event;

    //Biglietto della transazione
    #[ManyToOne(targetEntity: Ticket::class)]
    #[JoinColumn(name: 'ticket_id', referencedColumnName: 'id')]
    private $ticket;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionHash(): ?string
    {
        return $this->transactionHash;
    }

    public function setTransactionHash(string $transactionHash): self
    {
        $this->transactionHash = $transactionHash;

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

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}

// ES: 320897dchw0er740r - 1 ( coldplay ) - 1544
// ES: 320897dchw0er740r - 1 ( coldplay ) - 1545
// ES: 320897dchw0er740r - 1 ( jovanotti ) - 24532
// ES: 320897dchw0er740r - 1 ( jovanotti ) - 24533
// ES: 320897dchw0er740r - 1 ( jovanotti ) - 24534
