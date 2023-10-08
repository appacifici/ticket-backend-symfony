<?php

declare(strict_types=1);

namespace App\Domain\Sector\Interface;

use App\Entity\Sector;

interface SectorServiceInterface  {

    public function sectorSoldOut( Sector $sector, int $totalPurchaseSector ): int;    
    public function setPurchased( Sector $sector, int $purchased ): void;
}