<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TimeTrackerRepository;

#[ORM\Table(name: "time_tracker")]
#[ORM\Entity(repositoryClass: TimeTrackerRepository::class)]
class TimeTracker
{
    use GlobalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(name:"process_name", type: "string", length: 255)]
    private $processName;

    #[ORM\Column(name:"process", type: "string", length: 255)]
    private $process;

    #[ORM\Column(name:"childProcess", type: "string", length: 255, nullable: true, options: [ "default" => 0 ])]
    private $childProcess;

    #[ORM\Column(name:"start_time", type: "integer")]
    private $startTime;

    #[ORM\Column(name:"end_time", type: "integer", nullable: true)]
    private $endTime;

    #[ORM\Column(name:"memory", type: "integer")]
    private $memory;

    #[ORM\Column(name:"duration", type: "integer")]
    private $duration;

    #[ORM\Column(name:"ensure_stopped", type: "integer", nullable: true)]
    private $ensureStopped;

    #[ORM\Column(name:"origin", type: "string", length: 255)]
    private $origin;

    #[ORM\Column(name:"category", type: "string")]
    private $category;

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

    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?int
    {
        return $this->endTime;
    }

    public function setEndTime(?int $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getMemory(): ?int
    {
        return $this->memory;
    }

    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getEnsureStopped(): ?int
    {
        return $this->ensureStopped;
    }

    public function setEnsureStopped(?int $ensureStopped): self
    {
        $this->ensureStopped = $ensureStopped;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
