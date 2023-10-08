<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Service;

use App\Domain\Sector\Service\SectorService;
use App\Domain\Ticket\DTO\TicketPurchaseDTO;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Ticket\Exception\TicketPurchaseLimitException;
use App\Domain\Ticket\Exception\TicketSectorException;
use App\Domain\Ticket\Interface\TicketPurchaseServiceInterface;
use App\Domain\Place\Service\PlaceService;
use Exception;

/**
 * Classe spacifica che gestisce il solo processo di acquisti dei biglietti
 * Potendo crescere di molto ho preferito fare un servizio specifico a differenza del servizio globale per il domain Sector
 */
class TicketPurchaseService implements TicketPurchaseServiceInterface {

    /**
     * Ho messo i limiti come costanti in quanto pensando ad uno sviluppo successivo del sistema la maniera più corretta sarebbe creare un altra entita
     * che gestisca questo tipo di filtro, pensando magari che in base all'utente se base o premium possano avere diverse opzioni di scelta dove magari
     * l'utente premium puo acquistare fino a 5 biglietti, oppure quello base non può acquistare più di un evento, quindi per mancanza di tempo faccio
     * una cosa base lavorando con semplici costanti, che però inserisco nel servizio che si occupera dei controlli necessari per procedere all'acquisto
     */
    const MAX_TICKET_TRANSACTION = 4;
    const MAX_EVENT_TRANSACTION  = 2;
    	    
    private array $events;
    private array $ticketForEvent;
    private array $ticketForSectorEvent;

    public function __construct(
        private EntityManagerInterface $doctrine,
        private SectorService $sectorService,
        private PlaceService $placeService
    )
    {                
    }

    /**
     * Metodo principale che si occupa dell'acquisto dei biglietti 
     * $ticketPurchases array of PurchaseDTO
     */
    public function purchaseTicket( TicketPurchaseDTO $ticketPurchases ): bool {
        $purchases = $ticketPurchases->getPurchases();
        foreach( $purchases AS $purchase ) {
            $eventId                                            = $purchase->getEvent()->getId();
            $sectorId                                           = $purchase->getSector()->getId();
            $this->events[$eventId]                             = $purchase->getEvent();            
            $this->ticketForSectorEvent[$sectorId]['entity']    = $purchase->getSector();            
            $this->ticketForSectorEvent[$sectorId]['count']     = isset($this->ticketForSectorEvent[$sectorId]['count']) ? $this->ticketForSectorEvent[$sectorId]['count']+1 : 1;            
            $this->ticketForEvent[$eventId]                     = isset($this->ticketForEvent[$eventId]) ? $this->ticketForEvent[$eventId]+1 : 1;
        }        

        /*
            Lancia eccezione qui e non nella funzione privata in questo questo è il metodo centrale che gestisce la logica di acquisti 
            e solo lui deve sapere se lanciarla o gestirla in altro modo
        */
        if( $this->checkLimitPurchase() instanceof TicketPurchaseLimitException ) {
            throw $this->checkLimitPurchase();
        }


        /*
          Verifica se sono terminati tutti i biglietti, o i biglietti del settore richiesto in maniera separata,
          in modo fda fornire una risposta completa per permettere al frontend in caso di segnalare all'utente 
          di acquistare in un altro settore o che propro sono sold out
        */
        $sectorsSoldOut         = $this->ticketsSoldOut($purchases);
        $sectorServiceException = new TicketSectorException(); 
        foreach( $sectorsSoldOut AS $sectorSoldOut ) {
            switch( $sectorSoldOut['code'] ) {
                case SectorService::TICKET_SOLD_OUT:
                case SectorService::TICKET_SECTOR_SOLD_OUT:                          
                    $sectorServiceException->addItemListException(TicketSectorException::TICKET_SOLD_OUT);    
                    $sectorServiceException->setSector($sectorSoldOut['sector']);  
                break;
            }
        }
        if( $sectorServiceException->hasException() === true ) {
            throw $sectorServiceException;
        }

        //In caso si verifichi un eccezione durante i processi nel try e parta un eccezione generica andra ad effettuare il rallback del db per non creare inconsistenze                      
        $this->doctrine->getConnection()->beginTransaction(); 
        try{
            foreach( $purchases AS $purchase ) {
                if( !empty( $purchase->getPlace() ) ) {
                    $this->placeService->setNotFree($purchase->getPlace());
                }                        
            }

            foreach( $this->ticketForSectorEvent AS $aSector ) {            
                $this->sectorService->setPurchased( $aSector['entity'], $aSector['count'] );                
            }
            $this->doctrine->getConnection()->commit(); 
            
        } catch (\Exception $e) {
            $this->doctrine->getConnection()->rollBack();
            throw new Exception('Internal query error'. $e->getMessage());
        }
        
        



        return true;
    }

    /**
     * Controlla se vengono rispettati i limiti di acquisto per transazione dei ticket
     */
    private function checkLimitPurchase(): bool | TicketPurchaseLimitException {
        if( self::MAX_EVENT_TRANSACTION != -1 && count( $this->ticketForEvent ) > self::MAX_EVENT_TRANSACTION ) {
            $ticketPurchaseLimitException =  new TicketPurchaseLimitException('Ticket for event Limit Exceeded');        
            $ticketPurchaseLimitException->setErrorCode( self::MAX_EVENT_TRANSACTION );    
            return $ticketPurchaseLimitException;
        }

        foreach( $this->ticketForEvent AS $eventId => $total ) {
            if( self::MAX_TICKET_TRANSACTION != -1 && $total > self::MAX_TICKET_TRANSACTION ) {
                $ticketPurchaseLimitException =  new TicketPurchaseLimitException('Ticket for event Limit Exceeded');
                $ticketPurchaseLimitException->setEvent( $this->events[$eventId] );
                $ticketPurchaseLimitException->setErrorCode( self::MAX_TICKET_TRANSACTION );
                return $ticketPurchaseLimitException;
            }
        }        
        return true;
    }

    /**
     * Controlla se vengono rispettati i limiti di acquisto per transazione dei ticket
     * return $sectorSoldOut array of Sector
     */
    private function ticketsSoldOut(): array {        
        $sectorSoldOut = [];        
        $i = 0;
        foreach( $this->ticketForSectorEvent AS $sector ) {         
            $sectorSoludOutCode = $this->sectorService->sectorSoldOut( $sector['entity'], $sector['count'] );
            $sectorSoldOut[$i]['sector']    = $sector['entity'];
            $sectorSoldOut[$i]['code']      = $sectorSoludOutCode;
        }
        return $sectorSoldOut;
    }

}