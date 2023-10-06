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
    
}
