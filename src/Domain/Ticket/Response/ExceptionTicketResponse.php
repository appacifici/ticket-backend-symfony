<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Response;

use App\Domain\Ticket\Exception\PurchaseDTOException;
use App\Domain\Ticket\Exception\TicketPurchaseServiceException;

class ExceptionTicketResponse 
{    
    private array $response;

    public static function createTicketPurchaseServiceException( TicketPurchaseServiceException $e ): self {
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

    public static function createPurchaseDTOException( PurchaseDTOException $e ): self {
        $self                       = new self();
        $self->response['success']  = false;

        if( !empty( $e->getUserId()) && $e->getUserId() != '0' ) {            
            $self->response['errors'][0]['message']  = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::EMPTY_USER_ID];
            $self->response['errors'][0]['code']     = PurchaseDTOException::EMPTY_USER_ID;
        }

        if( !empty( $e->getPuschases()) ) {        
            foreach( $e->getPuschases() AS $key => $puschases ) {                   
                $x = 0;
                foreach(  $puschases AS $puschase ) {                         
                    $self->response['errors'][0]['message']                                 = PurchaseDTOException::ERROR_PURCHASE;
                    $self->response['errors'][0]['code']                                    = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::ERROR_PURCHASE];
                    $self->response['errors'][0]['pushcases'][$key][$x]['code']          = $puschase;
                    $self->response['errors'][0]['pushcases'][$key][$x]['message']       = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[$puschase];
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
            $self->response['errors'][0]['code']                        = PurchaseDTOException::NOT_FOUND_ENTITY_EVENT;
            $self->response['errors'][0]['message']                     = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::NOT_FOUND_ENTITY_EVENT];
            $self->response['errors'][0]['event']['id']                 = $e->getNotFoundEntityEvent();
            $self->response['errors'][0]['event']['puschaseIndex']      = $e->getPuschaseIndex();
        }
        
        if( !empty($e->getNotFoundEntitySector()) ) {
            $self->response['errors'][0]['code']                        = PurchaseDTOException::NOT_FOUND_ENTITY_SECTOR;
            $self->response['errors'][0]['message']                     = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::NOT_FOUND_ENTITY_SECTOR];
            $self->response['errors'][0]['event']['id']                 = $e->getNotFoundEntitySector();
            $self->response['errors'][0]['event']['puschaseIndex']      = $e->getPuschaseIndex();
        }
        if( !empty( $e->getNotFoundEntityUser()) ) {
            $self->response['errors'][0]['code']                        = PurchaseDTOException::NOT_FOUND_ENTITY_USER;
            $self->response['errors'][0]['message']                     = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::NOT_FOUND_ENTITY_USER];
            $self->response['errors'][0]['user']['id']                  = $e->getNotFoundEntityUser();
            $self->response['errors'][0]['user']['puschaseIndex']       = $e->getPuschaseIndex();
        }
        if( !empty( $e->getNotFoundEntityPlace()) ) {
            $self->response['errors'][0]['code']                        = PurchaseDTOException::NOT_FOUND_ENTITY_PLACE;
            $self->response['errors'][0]['message']                     = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[PurchaseDTOException::NOT_FOUND_ENTITY_PLACE];
            $self->response['errors'][0]['place']['id']                 = $e->getNotFoundEntityPlace();
            $self->response['errors'][0]['place']['puschaseIndex']      = $e->getPuschaseIndex();
        }

        return $self;
    }

    public function serialize(): array
    {
        return $this->response;
    }
}