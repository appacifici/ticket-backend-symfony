<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\AlertRepository;

#[ORM\Table(name: "alerts")]
#[ORM\Entity(repositoryClass: AlertRepository::class)]
class Alert
{
    use GlobalTrait;

    const COUNT_QUERY  = 1;
    const RESULT_QUERY = 2;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"process_name", type: "string", length: 255)]
    private $processName;

    #[ORM\Column(name:"process", type: "string", length: 255)]
    private $process;

    #[ORM\Column(name:"childProcess", type: "string", length: 255, nullable:true)]
    private $childProcess;

    #[ORM\Column(name:"alert", type: "text")]
    private $alert;

    #[ORM\Column(name:"debug", type: "text")]
    private $debug;

    #[ORM\Column(name:"error", type: "text")]
    private $error;

    #[ORM\Column(name:"general", type: "text")]
    private $general;

    #[ORM\Column(name:"call_data", type: "text")]
    private $callData;

    #[ORM\Column(name:"call_response", type: "text")]
    private $callResponse;

    public function getProcessName(): ?string
    {
        return $this->processName;
    }

    public function setProcessName(string $processName): self
    {
        $this->processName = $processName;

        return $this;
    }

    public function getProcess(): ?string
    {
        return $this->process;
    }

    public function setProcess(string $process): self
    {
        $this->process = $process;

        return $this;
    }

    public function getChildProcess(): ?string
    {
        return $this->childProcess;
    }

    public function setChildProcess(?string $childProcess): self
    {
        $this->childProcess = $childProcess;

        return $this;
    }

    public function getAlert(): ?string
    {
        return $this->alert;
    }

    public function setAlert(string $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getDebug(): ?string
    {
        return $this->debug;
    }

    public function setDebug(string $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getGeneral(): ?string
    {
        return $this->general;
    }

    public function setGeneral(string $general): self
    {
        $this->general = $general;

        return $this;
    }

    public function getCallData(): ?string
    {
        return $this->callData;
    }

    public function setCallData(string $callData): self
    {
        $this->callData = $callData;

        return $this;
    }

    public function getCallResponse(): ?string
    {
        return $this->callResponse;
    }

    public function setCallResponse(string $callResponse): self
    {
        $this->callResponse = $callResponse;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
