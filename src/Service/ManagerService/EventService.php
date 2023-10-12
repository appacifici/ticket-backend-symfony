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

class EventService extends ControlService
{
    //Lista dei campi obbligatori richiesti nella chiamata
    public $checkFields = [ "name", "city", "date" ];

    public function __construct(
        private Container               $container,
        private EntityManagerInterface  $doctrine,
        private TimeTracker             $timeTracker,
        private Validator               $validator,
        private AlertUtility            $alertUtility
    ) {
        parent::__construct($container, $doctrine, $timeTracker, $validator, $alertUtility);
        $this->response = new stdClass();
    }

    /**
     * Genera il formato di risposta utilizzato sia per il recupero di un singolo utente che di tutti
     */
    private function getDataEvent(Event $event): array
    {
        $aEvent                  = [];
        $aEvent['id']            = $event->getId();
        $aEvent['name']          = $event->getName();
        $aEvent['city']          = $event->getCity();
        $aEvent['date']          = $event->getDate();
        return $aEvent;
    }

    public function getOneById(int $id): object
    {
        $this->getDataInput('getOneEvent', $this->container->getParameter('ws.limit.getOne'));

        try {
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start("findOneBy", "findOneBy");

            $event  = $this->doctrine->getRepository(Event::class)->findOneBy([ 'id' => $id ]);
            if ($this->checkResultQuery($event, "findOneBy") === false) {
                return $this->response;
            }

            $aEvent = $this->getDataEvent($event);

            $this->response->result      = true;
            $this->response->data        = $aEvent;
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
        $this->getDataInput('getAllEvents', $this->container->getParameter('ws.limit.getAll'));

        try {
            $this->timeTracker->start("getAll", "getAll");
            $events  = $this->doctrine->getRepository(Event::class)->findBy([]);            
            $aEvent = [];
            foreach ($events as $event) {
                $aEvent[$event->getId()]        = $this->getDataEvent($event);
            }
            $this->timeTracker->stop("getAll");

            $this->response->result     = true;
            $this->response->data       = $aEvent;
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
        $this->getDataInput('createEvent', $this->container->getParameter('ws.limit.create'), $input);

        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField(ControlService::ALL_REQUIRED);
        if ($controlField === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Event(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        //Genero nuovo utente
        $event   = new Event();
        $event->setName($this->input->name);
        $event->setCity($this->input->city);
        $event->setDate(new DateTimeImmutable($this->input->date));

        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity($event);
        if ($respValidate === false) {
            return $this->response;
        }

        if ($this->flushEntity($event) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    public function update(int|null $id, object $input = null): object
    {
        $this->getDataInput('updateEvent', $this->container->getParameter('ws.limit.update'), $input);

        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi richiesti siano presenti
        if ($this->controlField(ControlService::MIN_ONE_REQUIRED) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new Event(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $this->timeTracker->start("findEvent", "findEvent");
        $event  = $this->doctrine->getRepository(Event::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($event, "findEvent") === false) {
            return $this->response;
        }

        if (!empty($this->input->name)) {
            $event->setName($this->input->name);
        }
        if (!empty($this->input->city)) {
            $event->setCity($this->input->city);
        }
        if (!empty($this->input->date)) {
            $event->setDate(new DateTimeImmutable($this->input->date));
        }

        $respValidate = $this->validateEntity($event);
        if ($respValidate === false) {
            return $this->response;
        }

        //Stop del tracciamento delle tempistiche
        if ($this->flushEntity($event) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    public function delete(int|null $id, object $input = null): object
    {
        $this->getDataInput('deleteEvent', $this->container->getParameter('ws.limit.delete'), $input);
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        $this->timeTracker->start("findEvent", "findEvent");

        $event  = $this->doctrine->getRepository(Event::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($event, "findEvent") === false) {
            return $this->response;
        }

        $this->response->result     = true;

        //Stop del tracciamento delle tempistiche
        if ($this->removeEntity($event) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }
}
