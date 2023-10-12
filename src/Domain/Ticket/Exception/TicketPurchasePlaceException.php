<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use App\Domain\ErrorCodes;
use App\Entity\Place;
use Exception;

class TicketPurchasePlaceException extends Exception
{
    const PLACE_NOT_FREE            = ErrorCodes::PLACE_NOT_FREE;

    const PLACE_ERROR_MESSAGE       = [
        self::PLACE_NOT_FREE        => 'Ticket place not free'
    ];

    private bool    $hasException       = false;
    private array   $listExceptions     = [];
    private int     $index              = 0;
    private Place   $place;

    public function hasException(): bool
    {
        return $this->hasException;
    }

    public function getListException(): array
    {
        return $this->listExceptions;
    }

    public function addItemListException(int $typeError, Place $place): void
    {
        $this->hasException     = true;
        $this->listExceptions[$this->index]['code']  = $typeError;
        $this->listExceptions[$this->index]['place'] = $place;
        $this->index++;
    }

    public function errorMessage(): string
    {
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
        . ': <b>' . $this->getMessage() . '</b>';
        return $errorMsg;
    }
}
