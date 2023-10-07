<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Response;

use App\Domain\Ticket\Exception\PurchaseDTOException;
use App\Domain\Ticket\Exception\TicketSeviceException;

class ExceptionTicketResponse 
{
    
    private array $response;

    public static function createTicketSeviceException( TicketSeviceException $e ): self {
        $self                                   = new self();
        $self->response['success']              = false;
        $self->response['error']['event']       = $e->getEvent()->getName().' - '.$e->getEvent()->getLocation()->getName().' - '.$e->getEvent()->getDate()->format('Y-m-d H:i:s');
        $self->response['error']['message']     = $e->getMessage();
        $self->response['error']['code']        = $e->getErrorCode();

        return $self;
    }

    public static function createPurchaseDTOException( PurchaseDTOException $e ): self {
        $self                       = new self();
        $self->response['success']  = false;

        if( !empty( $e->getUserId()) ) {
            $self->response['error']['userId'] = 'Missing userdId Field';
        }

        if( !empty( $e->getPuschases()) ) {        
            foreach( $e->getPuschases() AS $key => $puschases ) {                   
                $x = 0;
                foreach(  $puschases AS $puschase ) {     
                    //+1 perche quando fa il JsonEncode mi leva gli indici con 0, non mi era mai capitato o non ci avevo mai fatto caso?                 
                    $self->response['error']['pushcase'][$key+1][$x+1]['code']          = $puschase;
                    $self->response['error']['pushcase'][$key+1][$x+1]['message']       = PurchaseDTOException::PURCHASE_ERROR_MESSAGE[$puschase];
                    $x++;
                }
            }
        }
        
        /*
            Questa gestione dell'eccezione a differenza di quella sopra al primo errore blocca l'esecuzione dello script, restituendo il primo errore
            in modo tale da evitare di fare ulteriori query a db se gia presente un errore. Differnte invece dal controllo sopra che controlla tutto il formato di chiamata
            e in caso di errore risponde al frontend l'errore completo per aiutare nell'implementazione della chiamata
        */
        if( !empty( $e->getNotFoundEntityEvent()) ) {
            $self->response['error']['event'] = 'Event not found. '.$e->getNotFoundEntityEvent();
        }
        if( !empty( $e->getNotFoundEntityUser()) ) {
            $self->response['error']['event'] = 'User not found. '.$e->getNotFoundEntityUser();
        }
        if( !empty( $e->getNotFoundEntityPlace()) ) {
            $self->response['error']['event'] = 'Place not found. '.$e->getNotFoundEntityPlace();
        }

        return $self;
    }

    public function serialize(): array
    {
        return $this->response;
    }
}