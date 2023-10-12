<?php

declare(strict_types=1);

namespace App\Domain;

class ErrorCodes
{
    const EMPTY_USER_ID                 = 1;
    const EMPTY_PURCHASE                = 2;
    const ERROR_PURCHASE                = 3;
    const PURCHASE_MISSING_EVENT_ID     = 4;
    const PURCHASE_MISSING_PLACE_TYPE   = 5;
    const PURCHASE_MISSING_PLACE_ID     = 6;
    const PURCHASE_MISSING_SECTOR_ID    = 7;
    const NOT_FOUND_ENTITY_USER         = 8;
    const NOT_FOUND_ENTITY_EVENT        = 9;
    const NOT_FOUND_ENTITY_SECTOR       = 10;
    const NOT_FOUND_ENTITY_PLACE        = 11;
    const PLACE_NOT_FREE                = 12;
    const TICKET_SOLD_OUT               = 13;
    const TICKET_SECTOR_SOLD_OUT        = 14;
    const TICKET_SECTOR_AVAILABLE       = 15;
    const INTERNAL_SERVER_ERROR         = 16;
    const NO_QUERY_RESULT               = 17;
}
