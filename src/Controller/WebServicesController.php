<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\RestService\RestManager;

class WebServicesController
{
    //EndPoint Rest CRUD seplice per la gestione degli utenti
    #[Route('/user', methods: ['GET', 'POST', 'PUT', 'DELETE'], name: 'wsuser')]
    public function userEvent(
        RestManager $restManager        
    ) {

        $response = $restManager->customProcessRequest('POST', 'user');
        return new JsonResponse($response);
    }
}
