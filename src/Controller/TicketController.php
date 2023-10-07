<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Ticket\Exception\PurchaseDTOException;
use App\Domain\Ticket\Exception\TicketSeviceException;
use App\Domain\Ticket\Service\TicketService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Domain\Ticket\Response\ExceptionTicketResponse;

class TicketController
{
    #[Route('/ticket', methods: ['POST'], name: 'wsTicket')]
    public function ticketEvent(
        Request                 $request,
        LoggerInterface         $logger,
        TicketPurchaseInterface $ticketPurchase,
        TicketService           $ticketService        
    ) {
        try {
            $requestData        = $request->toArray();
            $ticketPurchaseDTO  = $ticketPurchase->create($requestData);
            $ticketService->getEventTicket( $ticketPurchaseDTO );
        } catch( PurchaseDTOException $e ) {
            $exceptionTicketResponse = ExceptionTicketResponse::createPurchaseDTOException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch( TicketSeviceException $e ) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketSeviceException($e);
            $response = $exceptionTicketResponse->serialize();
        }
        
        return new JsonResponse($response);
    }
}
