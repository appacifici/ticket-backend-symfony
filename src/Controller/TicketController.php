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

class TicketController
{
    #[Route('/ticket', methods: ['POST'], name: 'wsTicket')]
    public function ticketEvent(
        Request $request,
        LoggerInterface $logger,
        TicketPurchaseInterface $ticketPurchase,
        TicketService $ticketService
    ) {
        try {
            $requestData        = $request->toArray();
            $ticketPurchaseDTO  = $ticketPurchase->create($requestData);
            $ticketService->getEventTicket( $ticketPurchaseDTO );
        } catch( PurchaseDTOException $e ) {
            echo $e->getNotFoundEntityEvent();
        } catch( TicketSeviceException $e ) {
            echo $e->getEvent()->getLocation()->getName();
        }
        

        return new JsonResponse([]);
    }
}
