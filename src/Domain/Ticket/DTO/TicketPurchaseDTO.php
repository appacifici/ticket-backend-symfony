<?php

declare(strict_types=1);

namespace App\Domain\Ticket\DTO;

use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Domain\Ticket\DTO\PurchaseDTO;
use App\Domain\Ticket\Exception\TicketPurchaseDTOException;

class TicketPurchaseDTO implements TicketPurchaseInterface
{
    private array $purchaseInterfaces;
    protected readonly array $finalPurchaseInterfaces; /** @phpstan-ignore-line */

    public function __construct(
        private EntityManagerInterface $doctrine
    ) {
    }

    public function create(array $data): self
    {

        $ticketSeviceException =  new TicketPurchaseDTOException('Invalid format request ticket');

        if (empty($data['userId']) || !is_int($data['userId'])) {
            $userId = $data['userId'] ?? '-';
            $ticketSeviceException->setUserId($userId, 0);
        }
        if (empty($data['puschase']) || !is_array($data['puschase'])) {
            $ticketSeviceException->setPuschases(TicketPurchaseDTOException::EMPTY_PURCHASE, -1);
        }

        foreach ($data['puschase'] as $key => $puschase) {
            if (empty($puschase['placeType']) || !is_int($puschase['placeType'])) {
                $ticketSeviceException->setPuschases(TicketPurchaseDTOException::PURCHASE_MISSING_PLACE_TYPE, $key);
            } else {
                if (( $puschase['placeType'] == Sector::ASSIGNED_PLACE && empty($puschase['placeId']) ) ||  $puschase['placeType'] == Sector::ASSIGNED_PLACE && !is_int($puschase['placeId'])) {
                    $ticketSeviceException->setPuschases(TicketPurchaseDTOException::PURCHASE_MISSING_PLACE_ID, $key);
                }
            }
            if (empty($puschase['eventId']) || !is_int($puschase['eventId'])) {
                $ticketSeviceException->setPuschases(TicketPurchaseDTOException::PURCHASE_MISSING_EVENT_ID, $key);
            }
            if (empty($puschase['sectorId']) || !is_int($puschase['sectorId'])) {
                $ticketSeviceException->setPuschases(TicketPurchaseDTOException::PURCHASE_MISSING_SECTOR_ID, $key);
            }
        }

        if ($ticketSeviceException->hasException()) {
            throw $ticketSeviceException;
        }

        $purchaseData = [];
        foreach ($data['puschase'] as $index => $puschase) {
            $purchaseDTO                  = new PurchaseDTO($this->doctrine);
            $purchaseData['userId']       = $data['userId'];
            $purchaseData['eventId']      = $puschase['eventId'];
            $purchaseData['sectorId']     = $puschase['sectorId'];
            $purchaseData['placeId']      = $this->checkPurchasePlaceIdRequired($puschase['placeType']) === true ? $puschase['placeId'] : null;
            $purchaseData['placeType']    = $puschase['placeType'];
            $this->purchaseInterfaces[]   = $purchaseDTO->create($purchaseData, $index);
        }

        
        $this->finalPurchaseInterfaces = $this->purchaseInterfaces;

        return $this;
    }

    private function checkPurchasePlaceIdRequired(int $placeType): bool
    {
        if ($placeType == Sector::ASSIGNED_PLACE) {
            return true;
        }
        return false;
    }

    //TODO: per rendere più chiaro il codice a chi lo implementa potrei creare un altro oggetto che ritorna un array di PurchaseDTO in modo da rendere trasparente l'impelentazione
    /**
     * @return array of PurchaseDTO
     */
    public function getPurchases(): array
    {
        return $this->finalPurchaseInterfaces;
    }
}
