<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Response;

use App\Domain\Ticket\Exception\TicketPurchaseDTOException;
use App\Domain\Ticket\Exception\TicketPurchaseLimitException;
use App\Domain\Ticket\Exception\TicketPurchasePlaceException;
use App\Domain\Ticket\Exception\TicketPurchaseSectorException;
use Exception;

class ExceptionTicketResponse 
{    
    private array $response;

    public static function createTicketPurchaseLimitException( TicketPurchaseLimitException $e ): self {
        $self                                                = new self();
        $self->response['success']                           = false;        
        $self->response['errors'][0]['message']              = $e->getMessage();
        $self->response['errors'][0]['code']                 = $e->getErrorCode();
        $self->response['errors'][0]['event']['id']          = $e->getEvent()->getId();
        $self->response['errors'][0]['event']['name']        = $e->getEvent()->getName();
        $self->response['errors'][0]['event']['location']    = $e->getEvent()->getLocation()->getName();
        $self->response['errors'][0]['event']['date']        = $e->getEvent()->getDate()->format('Y-m-d H:i:s');
        return $self;
    }

    public static function createTicketPurchaseDTOException( TicketPurchaseDTOException $e ): self {
        $self                       = new self();
        $self->response['success']  = false;

        if( !empty( $e->getUserId()) && $e->getUserId() != '0' ) {            
            $self->response['errors'][0]['message']  = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::EMPTY_USER_ID];
            $self->response['errors'][0]['code']     = TicketPurchaseDTOException::EMPTY_USER_ID;
        }

        if( !empty( $e->getPuschases()) ) {        
            foreach( $e->getPuschases() AS $key => $puschases ) {                   
                $x = 0;
                foreach(  $puschases AS $puschase ) {                         
                    $self->response['errors'][0]['message']                              = TicketPurchaseDTOException::ERROR_PURCHASE;
                    $self->response['errors'][0]['code']                                 = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::ERROR_PURCHASE];
                    $self->response['errors'][0]['pushcases'][$key][$x]['code']          = $puschase;
                    $self->response['errors'][0]['pushcases'][$key][$x]['message']       = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[$puschase];
                    $x++;
                }
            }
        }
        
        /*
            Questa gestione dell'eccezione a differenza di quella sopra al primo errore blocca l'esecuzione dello script, restituendo il primo errore
            in modo tale da evitare di fare ulteriori query a db se gia presente un errore. Differnte invece dal controllo sopra che controlla tutto il formato di chiamata
            e in caso di errore risponde al frontend l'errore completo per aiutare nell'implementazione della chiamata
        */
        if( !empty($e->getNotFoundEntityEvent()) ) {
            $self->response['errors'][0]['code']                        = TicketPurchaseDTOException::NOT_FOUND_ENTITY_EVENT;
            $self->response['errors'][0]['message']                     = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::NOT_FOUND_ENTITY_EVENT];
            $self->response['errors'][0]['event']['id']                 = $e->getNotFoundEntityEvent();
            $self->response['errors'][0]['event']['puschaseIndex']      = $e->getPuschaseIndex();
        }
        
        if( !empty($e->getNotFoundEntitySector()) ) {
            $self->response['errors'][0]['code']                        = TicketPurchaseDTOException::NOT_FOUND_ENTITY_SECTOR;
            $self->response['errors'][0]['message']                     = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::NOT_FOUND_ENTITY_SECTOR];
            $self->response['errors'][0]['event']['id']                 = $e->getNotFoundEntitySector();
            $self->response['errors'][0]['event']['puschaseIndex']      = $e->getPuschaseIndex();
        }
        if( !empty( $e->getNotFoundEntityUser()) ) {
            $self->response['errors'][0]['code']                        = TicketPurchaseDTOException::NOT_FOUND_ENTITY_USER;
            $self->response['errors'][0]['message']                     = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::NOT_FOUND_ENTITY_USER];
            $self->response['errors'][0]['user']['id']                  = $e->getNotFoundEntityUser();
            $self->response['errors'][0]['user']['puschaseIndex']       = $e->getPuschaseIndex();
        }
        if( !empty( $e->getNotFoundEntityPlace()) ) {
            $self->response['errors'][0]['code']                        = TicketPurchaseDTOException::NOT_FOUND_ENTITY_PLACE;
            $self->response['errors'][0]['message']                     = TicketPurchaseDTOException::PURCHASE_ERROR_MESSAGE[TicketPurchaseDTOException::NOT_FOUND_ENTITY_PLACE];
            $self->response['errors'][0]['place']['id']                 = $e->getNotFoundEntityPlace();
            $self->response['errors'][0]['place']['puschaseIndex']      = $e->getPuschaseIndex();
        }

        return $self;
    }

    public static function createTicketPurchaseSectorException( TicketPurchaseSectorException $e ): self {

        $self                                   = new self();
        $self->response['success']              = false;

        $i = 0;
        foreach( $e->getListException() AS $item ) {            
            $sector                                                 = $item['sector'];
            $self->response['errors'][$i]['message']                = TicketPurchaseSectorException::SECTOR_ERROR_MESSAGE[$item['code']];
            $self->response['errors'][$i]['code']                   = $item['code'];
            $self->response['errors'][$i]['sector']['id']           = $sector->getId();
            $self->response['errors'][$i]['sector']['name']         = $sector->getName();
            $self->response['errors'][$i]['sector']['eventId']      = $sector->getEvent()->getId();
            $i++;
        }
                        
        return $self;
    }

    public static function createTicketPurchasePlaceException( TicketPurchasePlaceException $e ): self {

        $self                                   = new self();
        $self->response['success']              = false;

        $i = 0;

        foreach( $e->getListException() AS $item ) {          
            $place                                                  = $item['place'];
            $self->response['errors'][$i]['message']                = TicketPurchasePlaceException::PLACE_ERROR_MESSAGE[$item['code']];
            $self->response['errors'][$i]['code']                   = $item['code'];
            $self->response['errors'][$i]['place']['id']            = $place->getId();
            $self->response['errors'][$i]['place']['name']          = 'Line: '.$place->getLine().' - Number: '.$place->getNumber();            
            $self->response['errors'][$i]['place']['eventId']       = $place->getEvent()->getId();
            $i++;
        }
                        
        return $self;
    }

    public static function createTicketGenericException( Exception $e ): self {

        $self                                                   = new self();
        $self->response['success']                              = false;     
        $self->response['errors'][0]['message']                 = $e->getMessage();
        $self->response['errors'][0]['code']                    = 500;            
                        
        return $self;
    }


    public function serialize(): array
    {
        return $this->response;
    }
}