<?php

declare(strict_types=1);

namespace App\Domain\Sector\Service;

use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Sector\Interface\SectorServiceInterface;

/**
 * Creato servizio non specifico in quanto ipoteticamente questo domain non dovrebbe avere molte funzioni e quindi tutti i servizi di questa entita
 * saranno messi in questa classe
 */
class SectorService implements SectorServiceInterface {

    const TICKET_SOLD_OUT 	 				= 1;	
	const TICKET_SECTOR_SOLD_OUT			= 2;	
	const TICKET_SECTOR_AVAILABLE			= 3;

    public function __construct(
        private EntityManagerInterface $doctrine
    )
    {                
    }

    public function setPurchased( Sector $sector, int $purchased ): void {
        $sector->setPurchased( $sector->getPurchased() + $purchased );
        $this->doctrine->persist($sector);
        $this->doctrine->flush();
    }

    /**
     * Controlla se vengono rispettati i limiti di acquisto per transazione dei ticket
     */
    public function sectorSoldOut( Sector $sector, int $totalPurchaseSector ): int {                
        if( $sector->getPurchased() == $sector->getTotal()  ) {            
            return self::TICKET_SOLD_OUT;
        }

        $ticketAvalabilities = $sector->getTotal() - $sector->getPurchased();
        if( $totalPurchaseSector > $ticketAvalabilities ) {
            return self::TICKET_SECTOR_SOLD_OUT;
        }
        return self::TICKET_SECTOR_AVAILABLE;
    }

}