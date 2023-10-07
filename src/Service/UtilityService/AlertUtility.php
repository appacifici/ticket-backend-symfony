<?php

namespace App\Service\UtilityService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Alert;

class AlertUtility
{
    protected array $processes;
    protected int $limitWrite;

    /**
     * AlertUtility constructor.
     * @param Container $container
     * @param ObjectManager $doctrine
     * @param \App\Service\UtilityService\TimeTracker $timeTracker
     */
    public function __construct(
        protected Container $container,
        protected EntityManagerInterface $doctrine,
        protected TimeTracker $timeTracker
    ) {
        $this->processes    = [];
    }

    /**
     * Metodo che inizializza l'array contenente i messaggi di alert cestibili per code processo
     * @param string $sCode
     * @return string
     */
    public function initProcess(string $sCode): string
    {
        $code = md5($sCode);
        $this->processes[$code]                     = [];
        $this->processes[$code]['child']            =  null;
        $this->processes[$code]['alert']            = [];
        $this->processes[$code]['debug']            = [];
        $this->processes[$code]['error']            = [];
        $this->processes[$code]['general']          = [];
        $this->processes[$code]['callData']         = [];
        $this->processes[$code]['callResponse']     = [];
        return $code;
    }


    /**
     * Metodo che scrive a db i dati dell'alert e del timetracker
     * @param string $process
     * @param string $processName
     * @param Strument $strument
     * @return void
     */
    public function write(string $process, string $processName): bool
    {

        //Verifica che non si sia chiusa la connessione con l'EntityManagerInterface in tal caso la riavvia
        if (!$this->doctrine->isOpen()) {
            $this->doctrine = $this->doctrine->create(
                $this->doctrine->getConnection(),
                $this->doctrine->getConfiguration()
            );
        }

        $this->doctrine->getConnection()->beginTransaction();
        $this->doctrine->getConnection()->getConfiguration()->setSQLLogger(null);

        //Recupera tutti gli eventi delle sezioni e li scrive a db per il processo corrente
        $events =  $this->timeTracker->getSectionEvents($process);

        $totalDuration = 0;
        foreach ($events as $event) {
            if ($event->getCategory() == 'default') {
                $totalDuration = $event->getDuration();
                break;
            }
        }

        //Scrive il log in caso ci sia un errore, o che venga superato il limite di tempo concesso
        if (empty($this->processes[$process]['error']) && $totalDuration < $this->limitWrite) {
            return true;
        }

        if ($totalDuration > $this->limitWrite) {
            $this->setAlert($process, "Superato il limite di tempo di $this->limitWrite ms, tempo totale esecuzione script: $totalDuration ms ", 'limitWrite');
        }

        foreach ($events as $event) {
            if ($event->getCategory() == 'doctrine') {
                continue;
            }

            $timeTracker = new \App\Entity\TimeTracker();
            $timeTracker->setCategory($event->getCategory()); // returns the category the event was started in
            $timeTracker->setOrigin($event->getOrigin()); // returns the event start time in milliseconds
            $timeTracker->setEnsureStopped($event->ensureStopped()); // stops all periods not already stopped
//            $timeTracker->setStartTime( $event->getStartTime() ); // returns the start time of the very first period
//            $timeTracker->setEndTime( $event->getEndTime() ); // returns the end time of the very last period

            foreach ($event->getPeriods() as $period) {
                $timeTracker->setStartTime($period->getStartTime());
                $timeTracker->setEndTime((int)$period->getEndTime());
            }

            $timeTracker->setDuration((int)$event->getDuration()); // returns the event duration, including all periods
            $timeTracker->setMemory($event->getMemory()); // returns the max memory usage of all periods
            $timeTracker->setProcessName($processName);
            $timeTracker->setProcess($process);
            $timeTracker->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            if ($event->getCategory() !== 'Procedura completa') {
                $timeTracker->setChildProcess($process);
                $timeTracker->setProcess('');
            }
            $this->doctrine->persist($timeTracker);
        }
        $this->doctrine->flush();
        $this->doctrine->clear();

        $alert = new Alert();
        $alert->setProcessName($processName);
        $alert->setProcess($process);
        $alert->setDebug($this->setFormatArrayText($this->processes[$process]['debug']));
        $alert->setAlert($this->setFormatArrayText($this->processes[$process]['alert']));
        $alert->setError($this->setFormatArrayText($this->processes[$process]['error']));
        $alert->setGeneral($this->setFormatArrayText($this->processes[$process]['general']));
        $alert->setCallData($this->setFormatArrayText($this->processes[$process]['callData']));
        $alert->setCallResponse($this->setFormatArrayText($this->processes[$process]['callResponse']));
        $alert->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $this->doctrine->persist($alert);
        $this->doctrine->flush();
        $this->doctrine->clear();

        $this->doctrine->getConnection()->commit();

        return true;
    }

 /**
     * Setta il limite in millisecondo oltre il quale vanno scritti i log
     * @param int $limit
     * @return void
     */
    public function setLimitWrite(int $limit): void
    {
        $this->limitWrite = $limit;
    }


    /**
     * Crea la stringa con il print_r di un array
     * @param type $results
     * @return string
     */
    protected function setFormatArrayText($results): string
    {
        if (!is_array($results)) {
            return $results;
        }

        $text = '';
        foreach ($results as $result) {
            $text .= $this->getSeparetor();
            $text .= print_r($result, true);
        }
        return $text;
    }

    /**
     * @return string
     */
    protected function getSeparetor(): string
    {
        $text = '#';
        for ($x = 0; $x < 100; $x++) {
            $text .= '#';
        }
        return "\n$text\n";
    }

    /**
     * @param string $code
     * @return array
     */
    public function getAlert(string $code): array
    {
        return $this->processes[$code]['alert'];
    }

    /**
     * @param string $code
     * @param string|array $alert
     */
    public function setAlert(string $code, $alert): void
    {
        if (is_array($alert)) {
            $alert = print_r($alert, true);
        }
        $this->processes[$code]['alert'][] = $alert;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getDebug(string $code): array
    {
        return $this->processes[$code]['debug'];
    }

    /**
     * @param string $code
     * @param string|array $debug
     * @param string|null $label
     */
    public function setDebug(string $code, $debug, $label = null): void
    {
        if ($label !== null) {
            $this->processes[$code]['debug'][] = $label;
        }

        if (is_array($debug)) {
            $debug = print_r($debug, true);
        }
        $this->processes[$code]['debug'][] = $debug;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getError(string $code): array
    {
        return $this->processes[$code]['error'];
    }

    /**
     * @param string $code
     * @param string|array $error
     */
    public function setError(string $code, $error): void
    {
        if (is_array($error)) {
            $error = print_r($error, true);
        }
        $this->processes[$code]['error'][] = $error;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getGeneral(string $code): array
    {
        return $this->processes[$code]['general'];
    }

    /**
     * @param string $code
     * @param string|array $error
     */
    public function setGeneral(string $code, $general): void
    {
        if (is_array($general)) {
            $general = print_r($general, true);
        }
        $this->processes[$code]['general'][] = $general;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getCallData(string $code): array
    {
        return $this->processes[$code]['callData'];
    }

    /**
     * @param string $code
     * @param string|array $error
     */
    public function setCallData(string $code, $general): void
    {
        if (is_array($general)) {
            $general = print_r($general, true);
        }
        $this->processes[$code]['callData'][] = $general;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getCallResponse(string $code): array
    {
        return $this->processes[$code]['callResponse'];
    }

    /**
     * @param string $code
     * @param string|array $error
     */
    public function setCallResponse(string $code, $general): void
    {
        if (is_array($general)) {
            $general = print_r($general, true);
        }
        $this->processes[$code]['callResponse'][] = $general;
    }

    /**
     * @return TimeTracker
     */
    public function getTimeTracker()
    {
        return $this->timeTracker;
    }
}//End Class
