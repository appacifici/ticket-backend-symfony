<?php

declare(strict_types=1);

namespace App\Domain\Place\Service;

use App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Place\Interface\PlaceServiceInterface;

/**
 * Creato servizio non specifico in quanto ipoteticamente questo domain non dovrebbe avere molte funzioni e quindi tutti i servizi di questa entita
 * saranno messi in questa classe
 */
class PlaceService implements PlaceServiceInterface {

    const PLACE_FREE        = 1;
    const PLACE_NOT_FREE    = 0;

    public function __construct(
        private EntityManagerInterface $doctrine
    )
    {                
    }

    public function setNotFree( Place $place ) {
        $place->setFree(self::PLACE_NOT_FREE);
        $this->doctrine->persist($place);
        $this->doctrine->flush();
    }

}