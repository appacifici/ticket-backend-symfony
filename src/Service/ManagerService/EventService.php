<?php 

declare(strict_types=1);

namespace App\Service\ManagerService;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Service\UtilityService\AlertUtility;
use DateTimeImmutable;
use Exception;
use stdClass;

class EventService extends ControlService {
           

    //Lista dei campi obbligatori richiesti nella chiamata    
    public $checkFields = [ "name", "city", "date" ];

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
    private function getDataEvent( Event $event ) :array {
        $aEvent                  = [];
        $aEvent['id']            = $event->getId();                   
        $aEvent['name']          = $event->getName();                   
        $aEvent['city']          = $event->getCity();                   
        $aEvent['date']          = $event->getDate();                           
        return $aEvent;
    }
    
    /**
     * Recupera i dati di un singolo utente
     * 
     * Esempio chiamata
     * http://ale.testapi.it/ws/event/1
     * 
     * Risposte:
     * Nessun Risultato: {"result":false,"errorCode":5,"msg":"Not result query"}
     * OK: {"result":true,"msg":{"id":4,"surname":"Pacifici","email":"aleweb87@gmail.com","eventname":"sandrino","password":"12qwaszx"}}
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}     
     */
    public function getOneById( int $id ) :object {                
        $this->getDataInput( 'getOneEvent', $this->container->getParameter( 'ws.limit.getOne' ) );
        
        try {            
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start( "findOneBy", "findOneBy" );
            
            $event  = $this->doctrine->getRepository( Event::class )->findOneBy( [ 'id' => $id ] );        
            if( $this->checkResultQuery( $event, "findOneBy" ) === false ) {
                return $this->response;
            }
            
            $aEvent = $this->getDataEvent( $event );   
                        
            $this->response->result      = true;     
            $this->response->data        = $aEvent;             
            
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
     * /ws/event
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
     *           "eventname": "aleweb87",
     *           "password": "03a845a382644cc7da75dc6fecbd52bb",x     
     *       },
     *       "4": {
     *           "id": 4,
     *           "surname": "Pacifici",
     *           "email": "aleweb87@gmail.com",
     *           "eventname": "sandrino",
     *           "password": "12qwaszx",     
     *       }
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}     
     */
    public function getAll() :object {       
        $this->getDataInput( 'getAllEvents', $this->container->getParameter( 'ws.limit.getAll' ) );
        
        try {            
            $this->timeTracker->start( "getAll", "getAll" );
            $events  = $this->doctrine->getRepository( Event::class )->findBy( [] );              
        
            $aEvent = [];
            foreach( $events AS $event ) {
                $aEvent[$event->getId()]        = $this->getDataEvent( $event );  
            }
            $this->timeTracker->stop( "getAll" );
            
            $this->response->result     = true;     
            $this->response->data       = $aEvent;             
            
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
     * /ws/event
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
        $this->getDataInput( 'createEvent', $this->container->getParameter( 'ws.limit.create' ), $input );
        
        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField( ControlService::ALL_REQUIRED );
        if( $controlField === false ) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField( new Event(), $this->input );
        if( $controlCheckTypeField === false ) {
            return $this->response;
        }        
        
        //Genero nuovo utente
        $event   = new Event();
        $event->setName( $this->input->name );
        $event->setCity( $this->input->city );        
        $event->setDate( new DateTimeImmutable( $this->input->date ) );    
        
        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity( $event );
        if( $respValidate === false ) {
            return $this->response;
        }
                
        if( $this->flushEntity( $event ) === true ) {
            $this->stopTimeTraker( true );
        }
        return $this->response;        
    }
    
        
    
    /**
     *   Esempio chiamata:
     * 
     *   EndPoint: http://ale.testapi.it/ws/event/2   
     *   {
     *       "email": "aleweb87.com",     
     *       "eventname": "sandro",     
     *   }
     * Risposte:  
     * OK: {"result":true}
     * Campi mancanti: {"result":false,"errorCode":1,"msg":"Campi mancanti: USERNAME"}
     * Exception: {"result":false,"errorCode":2,"msg":"Message Exception"}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}     
     */
    public function update( int|null $id, object $input = null ) :object {
        $this->getDataInput( 'updateEvent', $this->container->getParameter( 'ws.limit.update' ), $input );        
        
        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if( $this->checkIdEndPoint( $id ) === false ) {            
            return $this->response;
        }
        
        //Controlla che tutti i campi richiesti siano presenti
        if( $this->controlField( ControlService::MIN_ONE_REQUIRED ) === false ) {
            return $this->response;
        }
        
        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField( new Event(), $this->input );
        if( $controlCheckTypeField === false ) {
            return $this->response;
        }
        
        $this->timeTracker->start( "findEvent", "findEvent" ); 
        $event  = $this->doctrine->getRepository( Event::class )->findOneBy( [ 'id' => $id ] );             
        if( $this->checkResultQuery( $event, "findEvent" ) === false ) {                   
            return $this->response;
        }        
        
        if( !empty( $this->input->name )){      $event->setName( $this->input->name ); }
        if( !empty( $this->input->city )){      $event->setCity( $this->input->city ); }
        if( !empty( $this->input->date )){      $event->setDate( new DateTimeImmutable( $this->input->date ) ); }                  
                
        $respValidate = $this->validateEntity( $event );
        if( $respValidate === false ) {
            return $this->response;
        }
        
        //Stop del tracciamento delle tempistiche
        if( $this->flushEntity( $event ) === true ) {
            $this->stopTimeTraker( true );
        }  
        return $this->response;        
    }
    
    /**
     * Effettua la cancellazione di un record
     * 
     * Esempio chiamate:
     * DELETE: http://ale.testapi.it/ws/event/1
     * 
     * Risposte
     * OK: {"result":true}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}
     * Not Result: {"result":false,"errorCode":5,"data":"Not result query: findEvent"}
     * Exception: {"result":false,"errorCode":2,"data":"Message Exception"}
     *      
     */
    public function delete( int|null $id, object $input = null ) :object {
        $this->getDataInput( 'deleteEvent', $this->container->getParameter( 'ws.limit.delete' ), $input );     
        if( $this->checkIdEndPoint( $id ) === false ) {            
            return $this->response;
        }        
        
        $this->timeTracker->start( "findEvent", "findEvent" );
        
        $event  = $this->doctrine->getRepository( Event::class )->findOneBy( [ 'id' => $id ] );    
        if( $this->checkResultQuery( $event, "findEvent" ) === false ) {                   
            return $this->response;
        }
        
        $this->response->result     = true;  
        
        //Stop del tracciamento delle tempistiche
        if( $this->removeEntity( $event ) === true ) {
            $this->stopTimeTraker( true );
        }
        return $this->response;
    }
    
}