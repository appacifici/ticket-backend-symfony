<?php

declare(strict_types=1);

namespace App\Service\ManagerService;

use App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Service\UtilityService\AlertUtility;
use DateTimeImmutable;
use Exception;
use stdClass;

class PlaceService extends ControlService
{
    //Lista dei campi obbligatori richiesti nella chiamata
    public $checkFields = [ "line", "number", "price", "free", "eventId", "sectorId" ];

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
    private function getDataPlace(Place $place): array
    {
        $aPlace                     = [];
        $aPlace['id']               = $place->getId();
        $aPlace['line']             = $place->getLine();
        $aPlace['number']           = $place->getNumber();
        $aPlace['price']            = $place->getPrice();
        $aPlace['free']             = $place->getFree();
        $aPlace['event']['id']      = $place->getEvent()->getId();
        $aPlace['event']['name']    = $place->getEvent()->getName();
        $aPlace['sector']['id']     = $place->getSector()->getId();
        $aPlace['sector']['name']   = $place->getSector()->getName();
        return $aPlace;
    }

    public function getOneById(int $id): object
    {
        $this->getDataInput('getOnePlace', $this->container->getParameter('ws.limit.getOne'));

        try {
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start("findOneBy", "findOneBy");

            $place  = $this->doctrine->getRepository(Place::class)->findOneBy([ 'id' => $id ]);
            if ($this->checkResultQuery($place, "findOneBy") === false) {
                return $this->response;
            }

            $aPlace = $this->getDataPlace($place);

            $this->response->result      = true;
            $this->response->data        = $aPlace;
        } catch (Exception $e) {
            $this->setDebugException($e, 'findOneBy');
            $this->stopTimeTraker();
        }

        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker(true);
        return $this->response;
    }
    
    public function getAll(): object
    {
        $this->getDataInput('getAllPlaces', $this->container->getParameter('ws.limit.getAll'));

        try {
            $this->timeTracker->start("getAll", "getAll");
            $places  = $this->doctrine->getRepository(Place::class)->findBy([]);

            $aPlace = [];
            foreach ($places as $place) {
                $aPlace[$place->getId()]        = $this->getDataPlace($place);
            }
            $this->timeTracker->stop("getAll");

            $this->response->result     = true;
            $this->response->data       = $aPlace;
        } catch (Exception $e) {
            $this->setDebugException($e, 'getAll');
            $this->stopTimeTraker();
        }

        //Stop del tracciamento delle tempistiche
        $this->stopTimeTraker(true);
        return $this->response;
    }

    public function create(object $input = null): object
    {
        $this->getDataInput('createPlace', $this->container->getParameter('ws.limit.create'), $input);

        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField(ControlService::ALL_REQUIRED);
        if ($controlField === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Place(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $event = $this->getRelEntity('Event', $this->input->eventId);
        if ($event === false) {
            return $this->response;
        }

        $sector = $this->getRelEntity('Sector', $this->input->sectorId);
        if ($sector === false) {
            return $this->response;
        }

        //Genero nuovo utente
        $place   = new Place();
        $place->setLine($this->input->line);
        $place->setNumber($this->input->number);
        $place->setPrice($this->input->price);
        $place->setFree($this->input->free);
        $place->setEvent($event);
        $place->setSector($sector);

        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity($place);
        if ($respValidate === false) {
            return $this->response;
        }

        if ($this->flushEntity($place) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    public function update(int|null $id, object $input = null): object
    {
        $this->getDataInput('updatePlace', $this->container->getParameter('ws.limit.update'), $input);

        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi richiesti siano presenti
        if ($this->controlField(ControlService::MIN_ONE_REQUIRED) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Place(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $this->timeTracker->start("findPlace", "findPlace");
        $place  = $this->doctrine->getRepository(Place::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($place, "findPlace") === false) {
            return $this->response;
        }

        if (!empty($this->input->eventId)) {
            $event = $this->getRelEntity('Event', $this->input->eventId);
            if ($event === false) {
                return $this->response;
            }
            $place->setEvent($event);
        }


        if (!empty($this->input->sectorId)) {
            $sector = $this->getRelEntity('Sector', $this->input->sectorId);
            if ($sector === false) {
                return $this->response;
            }
            $place->setSector($sector);
        }

        if (!empty($this->input->line)) {
            $place->setLine($this->input->line);
        }
        if (!empty($this->input->number)) {
            $place->setNumber($this->input->number);
        }
        if (!empty($this->input->price)) {
            $place->setPrice($this->input->price);
        }
        if (!empty($this->input->free)) {
            $place->setFree($this->input->free);
        }

        $respValidate = $this->validateEntity($place);
        if ($respValidate === false) {
            return $this->response;
        }

        //Stop del tracciamento delle tempistiche
        if ($this->flushEntity($place) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    public function delete(int|null $id, object $input = null): object
    {
        $this->getDataInput('deletePlace', $this->container->getParameter('ws.limit.delete'), $input);
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        $this->timeTracker->start("findPlace", "findPlace");

        $place  = $this->doctrine->getRepository(Place::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($place, "findPlace") === false) {
            return $this->response;
        }

        $this->response->result     = true;

        //Stop del tracciamento delle tempistiche
        if ($this->removeEntity($place) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }
}
