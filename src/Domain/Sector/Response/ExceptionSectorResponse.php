<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Response;

use App\Domain\Sector\Exception\SectorServiceException;

class ExceptionSectorResponse 
{
    
    private array $response;

    public static function createSectorServiceException( SectorServiceException $e ): self {

        $self                                   = new self();
        $self->response['success']              = false;

        $i = 0;
        foreach( $e->getListException() AS $code ) {            
            $self->response['errors'][$i]['message']                = SectorServiceException::SECTOR_ERROR_MESSAGE[$code];
            $self->response['errors'][$i]['code']                   = $code;
            $self->response['errors'][$i]['sector']['id']           = $e->getSector()->getId();
            $self->response['errors'][$i]['sector']['name']         = $e->getSector()->getName();
            $self->response['errors'][$i]['sector']['eventId']      = $e->getSector()->getEvent()->getId();
        }
                        
        return $self;
    }




    public function serialize(): array
    {
        return $this->response;
    }
    
}