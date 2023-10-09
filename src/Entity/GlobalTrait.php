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

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt( \DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt( \Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
