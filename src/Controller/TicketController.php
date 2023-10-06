<?php

declare(strict_types=1);

namespace App\Controller;

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
        TicketPurchaseInterface $ticketPurchase
    ) {
        $requestData        = $request->toArray();
        $ticketPurchaseDTO  = $ticketPurchase->create($requestData);
        
        return new JsonResponse([]);
    }
}
