<?php

namespace App\Service\ManagerRestService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use App\Service\UtilityService\TimeTracker;
use App\Service\RestService\RestManager;
use App\Entity\PushEvent;
use App\Entity\PushReport;
use App\Service\UtilityService\AlertUtility;

/**
 * Classe che gestisce centralizzando i vari controlli necessari ai servizi e ne traccia tempistiche e debug alert
 * @author Alessandro Pacifici
 */
class ControlService
{
    //Tutti i campi richiesti
    const ALL_REQUIRED          = 1;

    //Almeno un campo richiesto
    const MIN_ONE_REQUIRED      = 2;

    public $†;
    public ?object $input       = null;
    public object $response;
    public string $processName;
    public string $process;
    public array $processes    = [];
    public array $checkFields   = [ "" ];
    public string $responseMessage;


    /**
     * AlertUtility constructor
     */
    public function __construct(
        public Container $container,
        public EntityManagerInterface $doctrine,
        public TimeTracker $timeTracker,
        public Validator $validator,
        public AlertUtility $alertUtility
    ) {
        $this->container        = $container;
        $this->doctrine         = $doctrine;
        $this->timeTracker      = $timeTracker;
        $this->validator        = $validator;
        $this->alertUtility     = $alertUtility;
        $this->processes        = [];
        $this->response         = (object)[];
    }

    /**
     * Avvia il debub e il tracciamento delle tempistiche
     * @param string $processName
     * @param int $limit
     * @param object $input
     * @return void
     */
    public function getDataInput(string $processName, int $limit, object $input = null): void
    {
        $this->timeTracker          = $this->alertUtility->getTimeTracker();
        $this->processName          = $processName;
        $this->process              = $this->alertUtility->initProcess($this->processName . date('YmdHis'));
        $this->alertUtility->setLimitWrite($limit);

        //Avvio sezione per tracciamento tempo con trick per lavorare con open section sempre con il nome
        $this->timeTracker->openSection();

        //Recupero i dati della chiamata o dall'oggetto passato di default
        $this->input  = $input !== null ? $input : json_decode(file_get_contents('php://input'), false);

        $this->alertUtility->setCallData($this->process, print_r($this->input, true), 'getInputData');
    }


    /**
     * Utilizza metodo statico del RestManager per controllare se i tipi di campi passati sono quelli aspettati settati sull'entità
     * @param type $entity
     * @param object $input
     * @return bool
     */
    protected function checkExpectedTypeField($entity, object $input): bool
    {
        $this->timeTracker->start("checkTypeInfoFieldEntity", "checkTypeInfoFieldEntity");
        $expected = RestManager::checkTypeInfoFieldEntity($this->doctrine, $entity, $input);
        if (!empty($expected)) {
            $this->response->result     = false;
            $this->response->errorCode  = $this->container->getParameter('ws.code.errorTypeFields');
            $this->response->data       = $expected;

            $this->timeTracker->stop("checkTypeInfoFieldEntity");
            return false;
        }
        $this->timeTracker->stop("checkTypeInfoFieldEntity");
        return true;
    }

    /**
     * Controlla se negli endPoint dove necessario inserire l'id che questo sia presente
     * @param int|null $id
     * @return bool
     */
    protected function checkIdEndPoint(int|null $id): bool
    {
        $this->timeTracker->start("checkIdEndPoint", "checkIdEndPoint");

        if ($id === null) {
            $this->response->result      = false;
            $this->response->errorCode   = $this->container->getParameter('ws.code.resMissingParamEndPoint');
            $this->response->data        = "Missing required param ID in endPoint";
            $this->timeTracker->stop("checkIdEndPoint");
            return false;
        }
        $this->timeTracker->stop("checkIdEndPoint");
        return true;
    }

    /**
     * Verifica che siano stati passati tutti i parametri necessari alla chiamata
     * @param int $typeCheck
     * @return array|bool
     */
    public function controlField(int $typeCheck): array|bool
    {
        $emptyField = [];
        $this->timeTracker->start("controlField", "controlField");

        try {
            //Se è richiesto almeno uno dei campi
            if ($typeCheck == $this::MIN_ONE_REQUIRED) {
                $msg = 'Inserire almeno uno dei seguenti campi: ';

                $allEmpty = true;
                foreach ($this->checkFields as $field) {
                    if (!empty($this->input->{$field})) {
                        $allEmpty = false;
                    }
                }

                //Se tutti i campi sono vuoti genera il messaggio di risposta con i campi richiesti
                if ($allEmpty === true) {
                    $emptyField = $this->checkFields;
                    $msg       .= implode(' - ', $this->checkFields);
                }

            //Se sono richiesti tutti i campi
            } if ($typeCheck == $this::ALL_REQUIRED) {
                $msg = 'Campi mancanti: ';

                foreach ($this->checkFields as $field) {
                    if (empty($this->input->{$field})) {
                        $emptyField[$field] = 0;
                        $msg .= strtoupper($field) . " - ";
                    }
                }
                $msg = trim($msg, '- ');
            }

            //Se mancano dei campi genera la risposta di errore
            if (!empty($emptyField)) {
                $this->response->result     = false;
                $this->response->errorCode  = $this->container->getParameter('ws.code.resMissingFields');
                $this->response->data       = $msg;
                $this->timeTracker->stop("controlField");

                $this->alertUtility->setCallResponse($this->process, print_r($this->response, true), 'Parametri chiamata mancanti');

                $this->stopTimeTraker();
                return false;
            }
        } catch (\Exception $e) {
            $this->setDebugException($e, 'controlField');
        }

        $this->timeTracker->stop("controlField");

        return $emptyField;
    }

    /**
     * Metodo che avvia la validazione dell'entità
     * @param type $entity
     * @return boolean
     */
    protected function validateEntity($entity)
    {
        $this->timeTracker->start("validateEntity", "validateEntity");

        try {
            //Utilizza validatore entita di symfony per verificare correttezza dati
            $errors = $this->validator->validate($entity);

            //Se ci sono errori prepara l'array con tutte le risposte
            if (count($errors) > 0) {
                $this->responseMessage =  '';

                $aErrors = [];
                foreach ($errors as $error) {
                    $aErrors[$error->getPropertyPath()][] = $error->getMessage();
                }

                $this->response->result     = 0;
                $this->response->errorCode  = $this->container->getParameter('ws.code.errorValidateEntity');
                $this->response->data       = 'Error in fields';
                $this->response->error      = $aErrors;

                $this->alertUtility->setCallResponse($this->process, print_r($this->response, true), 'Errore validazione entità');

                $this->timeTracker->stop("validateEntity");

                $this->stopTimeTraker();

                return false;
            }
        } catch (\Exception $e) {
            $this->setDebugException($e, 'validateEntity');
        }

        $this->timeTracker->stop("validateEntity");
        return true;
    }

    /**
     * Effettua il flush dell'entita passata
     * @param Entity $entity
     * @return bool
     */
    protected function flushEntity($entity): bool
    {
        $this->timeTracker->start("flushEntity", "flushEntity");

        try {
            $this->doctrine->persist($entity);
            $this->doctrine->flush();
            $this->doctrine->clear();
        } catch (\Throwable $e) {
            $this->setDebugException($e, 'flushEntity');
            return false;
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->setDebugException($e, 'flushEntity');
            return false;
        } catch (\Exception $e) {
            $this->setDebugException($e, 'flushEntity');
            return false;
        }

        $this->response->result = true;
        $this->timeTracker->stop("flushEntity");

        return true;
    }

    /**
     * Effettua il flush dell'entita passata
     * @param Entity $entity
     * @return bool
     */
    protected function removeEntity($entity): bool
    {
        $this->timeTracker->start("removeRecord", "removeRecord");

        try {
            $this->doctrine->remove($entity);
            $this->doctrine->flush();
            $this->doctrine->clear();
        } catch (\Throwable $e) {
            $this->setDebugException($e, 'removeRecord');
            return false;
        } catch (\Exception $e) {
            $this->setDebugException($e, 'removeRecord');
            return false;
        }

        $this->response->result = true;
        $this->timeTracker->stop("removeRecord");

        return true;
    }

    /**
     * Inserisce nel debug i dati dell'eccezione
     */
    protected function setDebugException(\Exception | \Throwable $e, string $section): void
    {

        $this->response->result     = false;
        $this->response->errorCode  = $this->container->getParameter('ws.code.respInternalError');
        $this->response->data       = $e->getMessage();

        $this->alertUtility->setError($this->process, print_r($this->response, true), 'Resp');
        $this->alertUtility->setDebug($this->process, $e->getTraceAsString(), 'Resp');
        $this->timeTracker->stop($section);

        $this->stopTimeTraker();
    }

    /**
     * Verifica se l'oggetto recuperato esiste o e vuoto in caso
     */
    protected function checkResultQuery(PushEvent | PushReport | array | null | bool $entity, string $section): bool
    {
        if (empty($entity)) {
            $this->response->result      = false;
            $this->response->errorCode   = $this->container->getParameter('ws.code.notResultQuery');
            $this->response->data        = "Not result query: $section";

            $this->alertUtility->setCallResponse($this->process, print_r($this->response, true), 'Resp');

            $this->timeTracker->stop($section);

            $this->stopTimeTraker();

            return false;
        }
        $this->timeTracker->stop($section);
        return true;
    }

    /**
     * Chiude il time tracker e il log utility
     */
    protected function stopTimeTraker(bool $setResponseCall = false): void
    {
        if ($setResponseCall === true) {
            $this->alertUtility->setCallResponse($this->process, print_r($this->response, true), 'Successo');
        }

        $this->timeTracker->stopSection($this->process);
        $this->alertUtility->write($this->process, $this->processName);
    }
}
