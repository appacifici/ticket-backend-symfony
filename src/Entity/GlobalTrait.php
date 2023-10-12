<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait GlobalTrait
{
    #[ORM\Column(name:"created_at", type: "datetime", length: 10, options: [ "default" => "CURRENT_TIMESTAMP" ], nullable:true)]
    private $createdAt;

    #[ORM\Column(name:"update_at", type: "datetime", length: 10, nullable:true)]
    private $updatedAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
