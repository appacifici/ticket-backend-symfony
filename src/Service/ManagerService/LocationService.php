<?php

declare(strict_types=1);

namespace App\Service\ManagerService;

use App\Entity\Event;
use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Service\UtilityService\AlertUtility;
use DateTimeImmutable;
use Exception;
use stdClass;

class LocationService extends ControlService
{
    //Lista dei campi obbligatori richiesti nella chiamata
    public $checkFields = [ "name", "address", "eventId" ];

    public function __construct(
        private Container $container,
        private EntityManagerInterface $doctrine,
        private TimeTracker $timeTracker,
        private Validator $validator,
        private AlertUtility $alertUtility
    ) {
        parent::__construct($container, $doctrine, $timeTracker, $validator, $alertUtility);
        $this->response = new stdClass();
    }

    /**
     * Genera il formato di risposta utilizzato sia per il recupero di un singolo utente che di tutti
     */
    private function getDataLocation(Location $location): array
    {
        $aLocation                  = [];
        $aLocation['id']            = $location->getId();
        $aLocation['name']          = $location->getName();
        $aLocation['address']       = $location->getAddress();
        $aLocation['event']['id']   = $location->getEvent()->getId();
        $aLocation['event']['name'] = $location->getEvent()->getName();
        return $aLocation;
    }

    /**
     * Recupera i dati di un singolo utente
     *
     * Esempio chiamata
     * http://ale.testapi.it/ws/location/1
     *
     * Risposte:
     * Nessun Risultato: {"result":false,"errorCode":5,"msg":"Not result query"}
     * OK: {"result":true,"msg":{"id":4,"surname":"Pacifici","email":"aleweb87@gmail.com","locationname":"sandrino","password":"12qwaszx"}}
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}
     */
    public function getOneById(int $id): object
    {
        $this->getDataInput('getOneLocation', $this->container->getParameter('ws.limit.getOne'));

        try {
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start("findOneBy", "findOneBy");

            $location  = $this->doctrine->getRepository(Location::class)->findOneBy([ 'id' => $id ]);
            if ($this->checkResultQuery($location, "findOneBy") === false) {
                return $this->response;
            }

            $aLocation = $this->getDataLocation($location);

            $this->response->result      = true;
            $this->response->data        = $aLocation;
        } catch (Exception $e) {
            $this->setDebugException($e, 'findOneBy');
            $this->stopTimeTraker();
        }

        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker(true);
        return $this->response;
    }

    /**
     * Esempio chiamata
     * /ws/location
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
     *           "locationname": "aleweb87",
     *           "password": "03a845a382644cc7da75dc6fecbd52bb",x
     *       },
     *       "4": {
     *           "id": 4,
     *           "surname": "Pacifici",
     *           "email": "aleweb87@gmail.com",
     *           "locationname": "sandrino",
     *           "password": "12qwaszx",
     *       }
     * Exception : {"result":false,"errorCode":2,"msg":"Message Exception"}
     */
    public function getAll(): object
    {
        $this->getDataInput('getAllLocations', $this->container->getParameter('ws.limit.getAll'));

        try {
            $this->timeTracker->start("getAll", "getAll");
            $locations  = $this->doctrine->getRepository(Location::class)->findBy([]);

            $aLocation = [];
            foreach ($locations as $location) {
                $aLocation[$location->getId()]        = $this->getDataLocation($location);
            }
            $this->timeTracker->stop("getAll");

            $this->response->result     = true;
            $this->response->data       = $aLocation;
        } catch (Exception $e) {
            $this->setDebugException($e, 'getAll');
            $this->stopTimeTraker();
        }

        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker(true);
        return $this->response;
    }

    /**
     * Creazione di un utente
     *
     * /ws/location
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
    public function create(object $input = null): object
    {
        $this->getDataInput('createLocation', $this->container->getParameter('ws.limit.create'), $input);

        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField(ControlService::ALL_REQUIRED);
        if ($controlField === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Location(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $event = $this->getRelEntity('Event', $this->input->eventId);
        if ($event === false) {
            return $this->response;
        }

        //Genero nuovo utente
        $location   = new Location();
        $location->setName($this->input->name);
        $location->setAddress($this->input->address);
        $location->setEvent($event);

        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity($location);
        if ($respValidate === false) {
            return $this->response;
        }

        if ($this->flushEntity($location) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }



    /**
     *   Esempio chiamata:
     *
     *   EndPoint: http://ale.testapi.it/ws/location/2
     *   {
     *       "email": "aleweb87.com",
     *       "locationname": "sandro",
     *   }
     * Risposte:
     * OK: {"result":true}
     * Campi mancanti: {"result":false,"errorCode":1,"msg":"Campi mancanti: USERNAME"}
     * Exception: {"result":false,"errorCode":2,"msg":"Message Exception"}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}
     */
    public function update(int|null $id, object $input = null): object
    {
        $this->getDataInput('updateLocation', $this->container->getParameter('ws.limit.update'), $input);

        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi richiesti siano presenti
        if ($this->controlField(ControlService::MIN_ONE_REQUIRED) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Location(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $this->timeTracker->start("findLocation", "findLocation");
        $location  = $this->doctrine->getRepository(Location::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($location, "findLocation") === false) {
            return $this->response;
        }

        if (!empty($this->input->name)) {
            $location->setName($this->input->name);
        }
        if (!empty($this->input->city)) {
            $location->setCity($this->input->city);
        }
        if (!empty($this->input->date)) {
            $location->setDate(new DateTimeImmutable($this->input->date));
        }

        $respValidate = $this->validateEntity($location);
        if ($respValidate === false) {
            return $this->response;
        }

        //Stop del tracciamento delle tempistiche
        if ($this->flushEntity($location) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    /**
     * Effettua la cancellazione di un record
     *
     * Esempio chiamate:
     * DELETE: http://ale.testapi.it/ws/location/1
     *
     * Risposte
     * OK: {"result":true}
     * Id EndPoint Mancante: {"result":false,"errorCode":4,"msg":"Missing required param ID in endPoint"}
     * Not Result: {"result":false,"errorCode":5,"data":"Not result query: findLocation"}
     * Exception: {"result":false,"errorCode":2,"data":"Message Exception"}
     *
     */
    public function delete(int|null $id, object $input = null): object
    {
        $this->getDataInput('deleteLocation', $this->container->getParameter('ws.limit.delete'), $input);
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        $this->timeTracker->start("findLocation", "findLocation");

        $location  = $this->doctrine->getRepository(Location::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($location, "findLocation") === false) {
            return $this->response;
        }

        $this->response->result     = true;

        //Stop del tracciamento delle tempistiche
        if ($this->removeEntity($location) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }
}
