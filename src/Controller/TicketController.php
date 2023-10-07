<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Ticket\Exception\PurchaseDTOException;
use App\Domain\Ticket\Exception\TicketPurchaseServiceException;
use App\Domain\Ticket\Service\TicketPurchaseService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Domain\Ticket\Response\ExceptionTicketResponse;

class TicketController
{
    #[Route('/ticket/purchase', methods: ['POST'], name: 'wsTicket')]
    public function ticketEvent(
        Request                 $request,
        LoggerInterface         $logger,
        TicketPurchaseInterface $ticketPurchase,
        TicketPurchaseService   $ticketPurchaseService        
    ) {
        try {
            $requestData        = $request->toArray();
            $ticketPurchaseDTO  = $ticketPurchase->create($requestData);
            $ticketPurchaseService->purchaseTicket( $ticketPurchaseDTO );
        } catch( PurchaseDTOException $e ) {
            $exceptionTicketResponse = ExceptionTicketResponse::createPurchaseDTOException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch( TicketPurchaseServiceException $e ) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketPurchaseServiceException($e);
            $response = $exceptionTicketResponse->serialize();
        }
        
        return new JsonResponse($response);
    }
}
