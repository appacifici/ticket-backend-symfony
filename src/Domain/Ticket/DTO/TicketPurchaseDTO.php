<?php

declare(strict_types=1);

namespace App\Domain\Ticket\DTO;

use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Domain\Ticket\Interface\PurchaseInterface;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Sector;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Domain\Ticket\DTO\PurchaseDTO;
use App\Domain\Ticket\Exception\PurchaseDTOException;

class TicketPurchaseDTO implements TicketPurchaseInterface
{
    private array $purchaseInterfaces;    
    protected readonly array $finalPurchaseInterfaces;    

    public function __construct(
        private EntityManagerInterface $doctrine
    ) {
    }
    
    public function create(array $data): self
    {        
        
        $ticketSeviceException =  new PurchaseDTOException('Invalid format request ticket');

        if( empty( $data['userId'] ) || !is_int($data['userId']) ) {
            throw new Exception('Invalid field userId ( missing or error type )');            
        }
        if( empty( $data['puschase'] ) || !is_array( $data['puschase'] ) ) {
            throw new Exception('Missing field purchase ( missing or error type )');
        }

        foreach($data['puschase'] AS $puschase) {
            if( empty( $puschase['placeType'] ) || !is_int($puschase['placeType']) ) {
                throw new Exception('Missing field placeType in puschase record ( missing or error type )');                
            }
            if( empty( $puschase['eventId'] ) || !is_int($puschase['eventId']) ) {
                throw new Exception('Missing field eventId in puschase record ( missing or error type )');
            }
            if( ( $puschase['placeType'] == Sector::ASSIGNED_PLACE && empty( $puschase['placeId'] ) ) ||  $puschase['placeType'] == Sector::ASSIGNED_PLACE && !is_int($puschase['placeId']) ) {
                throw new Exception('Missing field placeId in puschase record ( missing or error type )');
            }
        }
        
        $purchaseData = [];
        foreach($data['puschase'] AS $puschase) {            
            $purchaseDTO                  = new PurchaseDTO($this->doctrine);
            $purchaseData['userId']       = $data['userId'];
            $purchaseData['eventId']      = $puschase['eventId'];
            $purchaseData['placeId']      = $this->checkPurchasePlaceIdRequired($puschase['placeType']) === true ? $puschase['placeId'] : null;
            $purchaseData['placeType']    = $puschase['placeType'];            
            $this->purchaseInterfaces[]   = $purchaseDTO->create($purchaseData);        
        }
        
        $this->finalPurchaseInterfaces = $this->purchaseInterfaces;

        return $this;
    }

    private function  checkPurchasePlaceIdRequired( int $placeType ): bool {
        if( $placeType == Sector::ASSIGNED_PLACE ) {
            return true;
        }
        return false;
    }

    //TODO: per rendere più chiaro il codice a chi lo implementa potrei creare un altro oggetto che ritorna un array di PurchaseDTO in modo da rendere trasparente l'impelentazione
    /**
     * @return array of PurchaseDTO
     */
    public function getPurchases(): array {
        return $this->finalPurchaseInterfaces;
    }

}