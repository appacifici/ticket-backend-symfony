<?php

declare(strict_types=1);

namespace App\Service\RestService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use App\Service\UtilityService\TimeTracker;
use Symfony\Component\HttpFoundation\Request;

class RestManager
{
    //Mappatura tipologie symfony con tipologie dela chiamata REST
    const MAPPING_TYPE      = [
        'bigint'            => 'integer',
        'integer'           => 'integer',
        'tinytint'          => 'integer',
        'smallint'          => 'integer',
        'float'             => 'double',
        'datetime'          => 'string',
        'string'            => 'string',
        'datetime_immutable'=> 'string'
    ];
 
    private $serviceClass   = null;
    private $processes      = [];

    public function __construct(
        private Container $container, 
        private EntityManagerInterface $doctrine, 
        private TimeTracker $timeTracker)
    {                
    }

    public function processRequest(Request $request, string $endPoint, int $id = null): object
    {
        $this->generateClass($endPoint);

        switch ($request->getMethod()) {
            case 'GET':
                if (!empty($id)) {
                    return $this->serviceClass->getOneById($id);
                } else {
                    if (method_exists($this->serviceClass, 'getAll')) {
                        return $this->serviceClass->getAll();
                    }
                }
                break;
            case 'POST':
                return $this->serviceClass->create();
            break;
            case 'PUT':
                return $this->serviceClass->update($id);
            break;
            case 'DELETE':
                return $this->serviceClass->delete($id);
            break;
            default:
                echo "Not Found";
                break;
        }
        return new \stdClass();
    }

    public function customProcessRequest($method, string $endPoint, int $id = null): object
    {
        $this->generateClass($endPoint);

        switch ($method) {
            case 'POST':
                parse_str($_SERVER['argv'][0], $queryArray);
                $input = ( (object)$queryArray);
                return $this->serviceClass->insertPushReport($input);
            break;
            default:
                echo "Not Found";
                break;
        }
        return new \stdClass();
    }

    /**
     * Instanzia il servizio in base all'end point chiamato
     */
    public function generateClass(string $endPoint): void
    {
        switch ($endPoint) {
            case 'wsUser':
                $this->serviceClass = $this->container->get( 'app.userService' );
            break;
            case 'wsEvent':
                $this->serviceClass = $this->container->get( 'app.eventService' );
            break;
            case 'wsLocation':
                $this->serviceClass = $this->container->get( 'app.locationService' );
            break;
            case 'wsPlace':
                $this->serviceClass = $this->container->get( 'app.placeService' );
            break;
            case 'wsSector':
                $this->serviceClass = $this->container->get( 'app.sectorService' );
            break;
        }
    }

     /**
     * Metodo che effettua la mappatura tra i tipi di symfony entity e quelli ricevuti dalla chiamata REST
     */
    static function checkTypeInfoFieldEntity(EntityManagerInterface $doctrine, $entity, object $inputs): array
    {

        //Recupera la class name dell'entita
        $className = $doctrine->getClassMetadata(get_class($entity))->getName();

        //Recupera tutte le colonne dell'entità
        $columns = $doctrine->getClassMetadata($className)->getFieldNames();

        //genera array con tutte le colonne e il tipo settato nelle annotazioni
        $entityColums = [];
        foreach ($columns as $key => $column) {
            $typeColumns = $doctrine->getClassMetadata(get_class($entity))->getTypeOfField($column);
            $entityColums[$column] = $typeColumns;
        }

        //Recupera tutte le relazioni dell'entita e le setta come parametri richiesti in integer
        $entityRelColumns = $doctrine->getClassMetadata($className)->getAssociationNames();
        foreach ($entityRelColumns as $key => $column) {
            $entityColums[$column] = 'integer';
        }

        $expected = [];
        foreach ($inputs as $key => $input) {
            if (!isset($entityColums[ $key ])) {
                continue;
            }

            $typeColumnsEntity  = $entityColums[ $key ];
            $typeColumnInput    = gettype($input);

            //verifica se il tipo ricevuto è diverso a quello aspettato da symfony entity in caso popola l'array con tutti i campi errati
            if (RestManager::MAPPING_TYPE[$typeColumnsEntity] != $typeColumnInput) {
                $expected[$key]['expected'] = RestManager::MAPPING_TYPE[$typeColumnsEntity];
                $expected[$key]['recived']  = $typeColumnInput;
            }
        }

        return $expected;
    }
}
