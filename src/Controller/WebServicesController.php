<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\RestService\RestManager;

class WebServicesController
{
    #[Route('/ws/user/{id}', methods: ['GET', 'POST', 'PUT', 'DELETE'], name: 'wsUser' )]
    public function wsUser( RestManager $restManager, Request $request, int $id = null ) {        
        $response = $restManager->processRequest( $request, 'wsUser', $id );        
        return new JsonResponse( $response );                        
    }

    #[Route('/ws/event/{id}', methods: ['GET', 'POST', 'PUT', 'DELETE'], name: 'wsEvent' )]
    public function wsEvent( RestManager $restManager, Request $request, int $id = null ) {        
        $response = $restManager->processRequest( $request, 'wsEvent', $id );        
        return new JsonResponse( $response );                        
    }

    #[Route('/ws/location/{id}', methods: ['GET', 'POST', 'PUT', 'DELETE'], name: 'wsLocation' )]
    public function wsLocation( RestManager $restManager, Request $request, int $id = null ) {        
        $response = $restManager->processRequest( $request, 'wsLocation', $id );        
        return new JsonResponse( $response );                        
    }

    #[Route('/ws/place/{id}', methods: ['GET', 'POST', 'PUT', 'DELETE'], name: 'wsPlace' )]
    public function wsPlace( RestManager $restManager, Request $request, int $id = null ) {        
        $response = $restManager->processRequest( $request, 'wsPlace', $id );        
        return new JsonResponse( $response );                        
    }
}
