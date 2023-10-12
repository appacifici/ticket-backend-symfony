<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use App\Domain\ErrorCodes;
use App\Entity\Sector;
use Exception;

class TicketPurchaseSectorException extends Exception
{
    const TICKET_SOLD_OUT                   = ErrorCodes::TICKET_SOLD_OUT;
    const TICKET_SECTOR_SOLD_OUT            = ErrorCodes::TICKET_SECTOR_SOLD_OUT;

    const SECTOR_ERROR_MESSAGE = [
        self::TICKET_SOLD_OUT               => 'Ticket sold out',
        self::TICKET_SECTOR_SOLD_OUT        => 'Ticket sector sold out'
    ];

    private bool    $hasException             = false;
    private array   $listExceptions           = [];
    private int     $index                    = 0;

    public function hasException(): bool
    {
        return $this->hasException;
    }

    public function getListException(): array
    {
        return $this->listExceptions;
    }

    public function addItemListException(int $typeError, Sector $sector): void
    {
        $this->hasException     = true;
        $this->listExceptions[$this->index]['code']  = $typeError;
        $this->listExceptions[$this->index]['sector'] = $sector;
        $this->index++;
    }

    public function errorMessage(): string
    {
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
        . ': <b>' . $this->getMessage() . '</b>';
        return $errorMsg;
    }
}
