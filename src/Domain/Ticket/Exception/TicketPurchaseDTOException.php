<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use App\Domain\ErrorCodes;
use Exception;

class TicketPurchaseDTOException extends Exception
{
    const EMPTY_USER_ID                 = ErrorCodes::EMPTY_USER_ID;
    const EMPTY_PURCHASE                = ErrorCodes::EMPTY_PURCHASE;
    const ERROR_PURCHASE                = ErrorCodes::ERROR_PURCHASE;
    const PURCHASE_MISSING_EVENT_ID     = ErrorCodes::PURCHASE_MISSING_EVENT_ID;
    const PURCHASE_MISSING_PLACE_TYPE   = ErrorCodes::PURCHASE_MISSING_PLACE_TYPE;
    const PURCHASE_MISSING_PLACE_ID     = ErrorCodes::PURCHASE_MISSING_PLACE_ID;
    const PURCHASE_MISSING_SECTOR_ID    = ErrorCodes::PURCHASE_MISSING_SECTOR_ID;
    const NOT_FOUND_ENTITY_USER         = ErrorCodes::NOT_FOUND_ENTITY_USER;
    const NOT_FOUND_ENTITY_EVENT        = ErrorCodes::NOT_FOUND_ENTITY_EVENT;
    const NOT_FOUND_ENTITY_SECTOR       = ErrorCodes::NOT_FOUND_ENTITY_SECTOR;
    const NOT_FOUND_ENTITY_PLACE        = ErrorCodes::NOT_FOUND_ENTITY_PLACE;

    const PURCHASE_ERROR_MESSAGE        = [
        self::EMPTY_USER_ID                 => 'Missin userId element',
        self::EMPTY_PURCHASE                => 'Missin Purchase element',
        self::ERROR_PURCHASE                => 'Missin Field in Purchase element',
        self::PURCHASE_MISSING_EVENT_ID     => 'Missin EventId element',
        self::PURCHASE_MISSING_PLACE_TYPE   => 'Missin PlaceType element',
        self::PURCHASE_MISSING_PLACE_ID     => 'Missin PlaceId element',
        self::PURCHASE_MISSING_SECTOR_ID    => 'Missin SectorId element',
        self::NOT_FOUND_ENTITY_USER         => 'Not found user record',
        self::NOT_FOUND_ENTITY_EVENT        => 'Not found event record',
        self::NOT_FOUND_ENTITY_SECTOR       => 'Not found sector record',
        self::NOT_FOUND_ENTITY_PLACE        => 'Not found place record',
    ];

    private bool $hasException  = false;
    private mixed $userId       = null;
    private array $puschases    = [];

    private int $user           = 0;
    private int $event          = 0;
    private int $sector         = 0;
    private int $place          = 0;
    private int $puschaseIndex  = 0;

    public function hasException(): bool
    {
        return $this->hasException;
    }

    public function errorMessage(): string
    {
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
        . ': <b>' . $this->getMessage() . '</b>';
        return $errorMsg;
    }

    public function getNotFoundEntityEvent(): int
    {
        return $this->event;
    }

    public function setNotFoundEntityEvent(int $event, int $index): self
    {
        $this->hasException     = true;
        $this->event            = $event;
        $this->puschaseIndex    = $index;

        return $this;
    }

    public function getNotFoundEntitySector(): int
    {
        return $this->sector;
    }

    public function setNotFoundEntitySector(int $sector, int $index): self
    {
        $this->hasException     = true;
        $this->sector           = $sector;
        $this->puschaseIndex    = $index;

        return $this;
    }

    public function getNotFoundEntityUser(): int
    {
        return $this->user;
    }

    public function setNotFoundEntityUser(int $user, int $index): self
    {
        $this->hasException     = true;
        $this->user             = $user;
        $this->puschaseIndex    = $index;

        return $this;
    }

    public function getNotFoundEntityPlace(): int
    {
        return $this->place;
    }

    public function setNotFoundEntityPlace(int $place, int $index): self
    {
        $this->hasException     = true;
        $this->place            = $place;
        $this->puschaseIndex    = $index;

        return $this;
    }

    public function getUserId(): mixed
    {
        return $this->userId;
    }

    public function setUserId(mixed $userId, int $index): self
    {
        $this->hasException = true;
        $this->userId       = $userId;

        return $this;
    }

    public function getPuschaseIndex(): int
    {
        return $this->puschaseIndex;
    }

    public function getPuschases(): array
    {
        return $this->puschases;
    }

    public function setPuschases(int $field, int $key): self
    {
        $this->hasException         = true;
        $this->puschases[$key][]    = $field;

        return $this;
    }
}
