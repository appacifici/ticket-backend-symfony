<?php 

declare(strict_types=1);

namespace App\Service\ManagerService;

use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Service\UtilityService\AlertUtility;
use DateTimeImmutable;
use Exception;
use stdClass;

class SectorService extends ControlService {
           

    //Lista dei campi obbligatori richiesti nella chiamata    
    public $checkFields = [ "name", "total", "purchased", "placeType", "eventId", "locationId" ];

    public function __construct( 
        private Container               $container, 
        private EntityManagerInterface  $doctrine, 
        private TimeTracker             $timeTracker, 
        private Validator               $validator,
        private AlertUtility            $alertUtility
    )  {
        parent::__construct( $container, $doctrine, $timeTracker, $validator, $alertUtility );
        $this->response = new stdClass;        
    }
    
    /**
     * Genera il formato di risposta utilizzato sia per il recupero di un singolo utente che di tutti     
     */
    private function getDataSector( Sector $sector ) :array {
        $aSector                     = [];
        $aSector['id']               = $sector->getId();                   
        $aSector['name']             = $sector->getName();                   
        $aSector['total']            = $sector->getTotal();                   
        $aSector['purchased']        = $sector->getPurchased();                                              
        $aSector['placeType']        = $sector->getPlaceType();                                                                               
        $aSector['event']['id']      = $sector->getEvent()->getId();                           
        $aSector['event']['name']    = $sector->getEvent()->getName();                           
        $aSector['sector']['id']     = $sector->getLocation()->getId();                           
        $aSector['sector']['name']   = $sector->getLocation()->getName();                           
        return $aSector;
    }
    
    /**
     * Recupera i dati di un singolo utente
     * 
     * Esempio chiamata
     * http://ale.testapi.it/ws/sector/1
     * 
     * Risposte:
     * Nessun Risultato: {"result":false,"errorCode":5,"msg":"Not result query"}
     * OK: {"result":true,"msg":{"id":4,"surname":"Pacifici","email":"aleweb87@gmail.com","sectorname":"sandrino","password":"12qwaszx"}}
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}     
     */
    public function getOneById( int $id ) :object {                
        $this->getDataInput( 'getOneSector', $this->container->getParameter( 'ws.limit.getOne' ) );
        
        try {            
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start( "findOneBy", "findOneBy" );
            
            $sector  = $this->doctrine->getRepository( Sector::class )->findOneBy( [ 'id' => $id ] );        
            if( $this->checkResultQuery( $sector, "findOneBy" ) === false ) {
                return $this->response;
            }
            
            $aSector = $this->getDataSector( $sector );   
                        
            $this->response->result      = true;     
            $this->response->data        = $aSector;             
            
        } catch ( Exception $e ) {
            $this->setDebugException( $e, 'findOneBy' );
            $this->stopTimeTraker();
        }                      
        
        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker( true );    
        return $this->response;        
    }
    
    /**
     * Esempio chiamata
     * /ws/sector
     * 
     * Recupera tutti gli utenti 
     * Risposte:
     * {
     *   "result": true,
     *   "msg": {
     *       "1": {
     *           "id": 1,
     *           "surname": "Pacifici",
     *           "email": "adonmargotkira@cani.it",
     *           "sectorname": "aleweb87",
     *           "password": "03a845a382644cc7da75dc6fecbd52bb",x     
     *       },
     *       "4": {
     *           "id": 4,
     *           "surname": "Pacifici",
     *           "email": "aleweb87@gmail.com",
     *           "sectorname": "sandrino",
     *           "password": "12qwaszx",     
     *       }
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}     
     */
    public function getAll() :object {       
        $this->getDataInput( 'getAllSectors', $this->container->getParameter( 'ws.limit.getAll' ) );
        
        try {            
            $this->timeTracker->start( "getAll", "getAll" );
            $sectors  = $this->doctrine->getRepository( Sector::class )->findBy( [] );              
        
            $aSector = [];
            foreach( $sectors AS $sector ) {
                $aSector[$sector->getId()]        = $this->getDataSector( $sector );  
            }
            $this->timeTracker->stop( "getAll" );
            
            $this->response->result     = true;     
            $this->response->data       = $aSector;             
            
        } catch ( Exception $e ) {
            $this->setDebugException( $e, 'getAll' );
            $this->stopTimeTraker();
        }                       
        
        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker( true );     
        return $this->response;
        
    }        
    
    /**
     * Creazione di un utente
     * 
     * /ws/sector
     * Esempio chiamata:
     *  {
     *      "name" : "Alessandro", 
     *      "surname": "Pacifici",
     *      "email": "ap.pacifici@gmail.com",
     *      "password": "12qwaszx",
     *      "role": "1",
     *      "status": "1"
     *  }
     * 
     * Risposte:  
     * OK: {"result":true}
     * Campi mancanti: {"result":false,"errorCode":1,"msg":"Campi mancanti: USERNAME"}
     * Exception: {"result":false,"errorCode":2,"msg":"Message Exception"}
     * 
     */
    public function create( object $input = null ) :object  {        
        $this->getDataInput( 'createSector', $this->container->getParameter( 'ws.limit.create' ), $input );
        
        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField( ControlService::ALL_REQUIRED );
        if( $controlField === false ) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField( new Sector(), $this->input );
        if( $controlCheckTypeField === false ) {
            return $this->response;
        }        
                
        $event = $this->getRelEntity( 'Event', $this->input->eventId );
        if( $event === false ) {
            return $this->response;
        }

        $location = $this->getRelEntity( 'Location', $this->input->locationId );
        if( $location === false ) {
            return $this->response;
        }

        //Genero nuovo utente
        $sector   = new Sector();
        $sector->setName( $this->input->name );
        $sector->setTotal( $this->input->total );        
        $sector->setPurchased( $this->input->purchased );        
        $sector->setPlaceType( $this->input->placeType );             
        $sector->setEvent( $event );    
        $sector->setLocation( $location );    
        
        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity( $sector );
        if( $respValidate === false ) {
            return $this->response;
        }
                
        if( $this->flushEntity( $sector ) === true ) {
            $this->stopTimeTraker( true );
        }
        return $this->response;        
    }
    
        
    
    /**
     *   Esempio chiamata:
     * 
     *   EndPoint: http://ale.testapi.it/ws/sector/2   
     *   {
     *       "email": "aleweb87.com",     
     *       "sectorname": "sandro",     
     *   }
     * Risposte:  
     * OK: {"result":true}
     * Campi mancanti: {"result":false,"errorCode":1,"msg":"Campi mancanti: USERNAME"}
     * Exception: {"result":false,"errorCode":2,"msg":"Message Exception"}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}     
     */
    public function update( int|null $id, object $input = null ) :object {
        $this->getDataInput( 'updateSector', $this->container->getParameter( 'ws.limit.update' ), $input );        
        
        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if( $this->checkIdEndPoint( $id ) === false ) {            
            return $this->response;
        }
        
        //Controlla che tutti i campi richiesti siano presenti
        if( $this->controlField( ControlService::MIN_ONE_REQUIRED ) === false ) {
            return $this->response;
        }
        
        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField( new Sector(), $this->input );
        if( $controlCheckTypeField === false ) {
            return $this->response;
        }
        
        $this->timeTracker->start( "findSector", "findSector" ); 
        $sector  = $this->doctrine->getRepository( Sector::class )->findOneBy( [ 'id' => $id ] );             
        if( $this->checkResultQuery( $sector, "findSector" ) === false ) {                   
            return $this->response;
        }        
        
        if( !empty( $this->input->eventId ) ) {
            $event = $this->getRelEntity( 'Event', $this->input->eventId );
            if( $event === false ) {
                return $this->response;
            }
            $sector->setEvent( $event );   
        }
        

        if( !empty( $this->input->sectorId ) ) {
            $location = $this->getRelEntity( 'Sector', $this->input->locationId );
            if( $location === false ) {
                return $this->response;
            }         
            $sector->setLocation( $location );   
        }

        if( !empty( $this->input->nae )){           $sector->setLine( $this->input->line ); }
        if( !empty( $this->input->total )){         $sector->setTotal( $this->input->total ); }
        if( !empty( $this->input->purchased )){     $sector->setPurchased( $this->input->purchased ); }     
        if( !empty( $this->input->placeType )){     $sector->setPlaceType( $this->input->placeType ); }                     
                
        $respValidate = $this->validateEntity( $sector );
        if( $respValidate === false ) {
            return $this->response;
        }
        
        //Stop del tracciamento delle tempistiche
        if( $this->flushEntity( $sector ) === true ) {
            $this->stopTimeTraker( true );
        }  
        return $this->response;        
    }
    
    /**
     * Effettua la cancellazione di un record
     * 
     * Esempio chiamate:
     * DELETE: http://ale.testapi.it/ws/sector/1
     * 
     * Risposte
     * OK: {"result":true}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}
     * Not Result: {"result":false,"errorCode":5,"data":"Not result query: findSector"}
     * Exception: {"result":false,"errorCode":2,"data":"Message Exception"}
     *      
     */
    public function delete( int|null $id, object $input = null ) :object {
        $this->getDataInput( 'deleteSector', $this->container->getParameter( 'ws.limit.delete' ), $input );     
        if( $this->checkIdEndPoint( $id ) === false ) {            
            return $this->response;
        }        
        
        $this->timeTracker->start( "findSector", "findSector" );
        
        $sector  = $this->doctrine->getRepository( Sector::class )->findOneBy( [ 'id' => $id ] );    
        if( $this->checkResultQuery( $sector, "findSector" ) === false ) {                   
            return $this->response;
        }
        
        $this->response->result     = true;  
        
        //Stop del tracciamento delle tempistiche
        if( $this->removeEntity( $sector ) === true ) {
            $this->stopTimeTraker( true );
        }
        return $this->response;
    }
    
}