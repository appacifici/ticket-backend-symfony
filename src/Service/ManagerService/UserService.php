<?php

declare(strict_types=1);

namespace App\Service\ManagerService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Entity\User;
use App\Entity\Group;
use App\Service\UtilityService\AlertUtility;
use Exception;
use stdClass;

class UserService extends ControlService
{
    //Lista dei campi obbligatori richiesti nella chiamata
    public $checkFields = [ "name", "surname", "username", "email", "password" ];

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
    private function getDataUser(User $user): array
    {
        $aUser              = [];
        $aUser['id']        = $user->getId();
        $aUser['name']      = $user->getName();
        $aUser['surname']   = $user->getSurname();
        $aUser['email']     = $user->getEmail();
        $aUser['username']  = $user->getUsername();
        $aUser['password']  = $user->getPassword();
        return $aUser;
    }

    public function getOneById(int $id): object
    {
        $this->getDataInput('getOneUser', $this->container->getParameter('ws.limit.getOne'));

        try {
            //Avvia il tracciamento delle tempistiche
            $this->timeTracker->start("findOneBy", "findOneBy");

            $user  = $this->doctrine->getRepository(User::class)->findOneBy([ 'id' => $id ]);
            if ($this->checkResultQuery($user, "findOneBy") === false) {
                return $this->response;
            }

            $aUser = $this->getDataUser($user);

            $this->response->result      = true;
            $this->response->data        = $aUser;
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
        $this->getDataInput('getAllUsers', $this->container->getParameter('ws.limit.getAll'));

        try {
            $this->timeTracker->start("getAll", "getAll");
            $users  = $this->doctrine->getRepository(User::class)->findBy([]);

            $aUser = [];
            foreach ($users as $user) {
                $aUser[$user->getId()]        = $this->getDataUser($user);
            }
            $this->timeTracker->stop("getAll");

            $this->response->result     = true;
            $this->response->data       = $aUser;
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
        $this->getDataInput('createUser', $this->container->getParameter('ws.limit.create'), $input);

        //Controlla che tutti i campi richiesti siano presenti
        $controlField = $this->controlField(ControlService::ALL_REQUIRED);
        if ($controlField === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new User(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        //Genero nuovo utente
        $user   = new User();
        $user->setName($this->input->name);
        $user->setSurname($this->input->surname);
        $user->setEmail($this->input->email);
        $user->setUsername($this->input->username);
        $user->setPassword($this->input->password);

        //Avvia validatione entita di symfony
        $respValidate = $this->validateEntity($user);
        if ($respValidate === false) {
            return $this->response;
        }

        if ($this->flushEntity($user) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }

    public function update(int|null $id, object $input = null): object
    {
        $this->getDataInput('updateUser', $this->container->getParameter('ws.limit.update'), $input);

        //Controlla che sia stato passato l'id nell'endPoint della chiamata REST
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi richiesti siano presenti
        if ($this->controlField(ControlService::MIN_ONE_REQUIRED) === false) {
            return $this->response;
        }

        //Controlla che tutti i campi siano del tipo richiesto dall'entità
        $controlCheckTypeField = $this->checkExpectedTypeField(new User(), $this->input);
        if ($controlCheckTypeField === false) {
            return $this->response;
        }

        $this->timeTracker->start("findUser", "findUser");
        $user  = $this->doctrine->getRepository(User::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($user, "findUser") === false) {
            return $this->response;
        }

        if (!empty($this->input->name)) {
            $user->setName($this->input->name);
        }
        if (!empty($this->input->surname)) {
            $user->setSurname($this->input->surname);
        }
        if (!empty($this->input->email)) {
            $user->setEmail($this->input->email);
        }
        if (!empty($this->input->username)) {
            $user->setUsername($this->input->username);
        }
        if (!empty($this->input->password)) {
            $user->setPassword($this->input->password);
        }
        if (!empty($this->input->status)) {
            $user->setStatus($this->input->status);
        }

        $respValidate = $this->validateEntity($user);
        if ($respValidate === false) {
            return $this->response;
        }

        //Stop del tracciamento delle tempistiche
        if ($this->flushEntity($user) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }
    
    public function delete(int|null $id, object $input = null): object
    {
        $this->getDataInput('deleteUser', $this->container->getParameter('ws.limit.delete'), $input);
        if ($this->checkIdEndPoint($id) === false) {
            return $this->response;
        }

        $this->timeTracker->start("findUser", "findUser");

        $user  = $this->doctrine->getRepository(User::class)->findOneBy([ 'id' => $id ]);
        if ($this->checkResultQuery($user, "findUser") === false) {
            return $this->response;
        }

        $this->response->result     = true;

        //Stop del tracciamento delle tempistiche
        if ($this->removeEntity($user) === true) {
            $this->stopTimeTraker(true);
        }
        return $this->response;
    }
}
