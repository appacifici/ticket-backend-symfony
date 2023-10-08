<?php

declare(strict_types=1);

namespace App\Domain\Sector\Service;

use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Sector\Exception\SectorServiceException;

/**
 * Creato servizio non specifico in quanto ipoteticamente questo domain non dovrebbe avere molte funzioni e quindi tutti i servizi di questa entita
 * saranno messi in questa classe
 */
class SectorService  {

    public function __construct(
        private EntityManagerInterface $doctrine
    )
    {                
    }

    /**
     * Controlla se vengono rispettati i limiti di acquisto per transazione dei ticket
     */
    public function ticketsSoldOut( Sector $sector, int $totalPurchaseSector ): bool {        
        $sectorServiceException = new SectorServiceException();                

        if( $sector->getPurchased() == $sector->getTotal()  ) {
            $sectorServiceException->addItemListException(SectorServiceException::TICKET_SOLD_OUT);
        }

        $ticketAvalabilities = $sector->getTotal() - $sector->getPurchased();
        if( $totalPurchaseSector > $ticketAvalabilities ) {
            $sectorServiceException->addItemListException(SectorServiceException::TICKET_SECTOR_SOLD_OUT);
            $sectorServiceException->setSector($sector);
        }

        if( $sectorServiceException->hasException() === true ) {
            throw $sectorServiceException;
        }
        
        return true;
    }

}