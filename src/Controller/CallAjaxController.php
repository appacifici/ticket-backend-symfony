<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Alert;
use App\Entity\TimeTracker;

class CallAjaxController extends TemplateController
{
    /**
     * @Route( "/getDataBoxOverlayByEntity/{section}/{id}", name="getDataBoxOverlayByEntity" )
     */
    public function getDataBoxOverlayByEntityAction(ManagerRegistry $doctrine, Request $request, string $section, int $id)
    {
        $this->baseParameters();

         //in base alla sezione passata gestisco quale entita è chiamata in causa e quela file twig
        switch ($section) {
            default:
                $entity                 = $section;
                $nameEntity             = ucfirst($entity);
                $nameTwig               = $nameEntity;
                $fileBasePath           = "";
                $imageBasePath          = "";
                break;
        }

        $em         = $doctrine;
        $extraParam = [];

        //Se fosse entita alert faccio il replace di alcuni caratteri
        switch ($nameEntity) {
            case 'Alert':
                $entity     = $em->getRepository(Alert::class)->findOneBy([ 'id' => $id ]);
                $entity->setDebug(str_replace("\n", "</br>", $entity->getDebug()));
                $entity->setError(str_replace("\n", "</br>", $entity->getError()));
                $entity->setAlert(str_replace("\n", "</br>", $entity->getAlert()));
                $entity->setGeneral(str_replace("\n", "</br>", $entity->getGeneral()));
                $entity->setCallData(str_replace("\n", "</br>", $entity->getCallData()));
                $entity->setCallResponse(str_replace("\n", "</br>", $entity->getCallResponse()));
                $timeTrackers     = $em->getRepository(TimeTracker::class)->findBy([ 'childProcess' => $entity->getProcess() ]);
                break;
        }

        $html       = $this->container->get('twig')->render(
            $this->versionSite . "/data_Overlay$nameTwig.html.twig",
            [ "data" => $entity, 'imageBasePath' => $imageBasePath, 'baseFilePath' => $fileBasePath, 'extraParam' => $extraParam, 'timeTrackers' => $timeTrackers ]
        );

        return new Response($html);
    }
}
