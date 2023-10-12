<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Ticket\Exception\TicketPurchaseDTOException;
use App\Domain\Ticket\Exception\TicketPurchaseLimitException;
use App\Domain\Ticket\Exception\TicketPurchasePlaceException;
use App\Domain\Ticket\Service\TicketPurchaseService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use App\Domain\Ticket\Interface\TicketPurchaseInterface;
use App\Domain\Ticket\Response\ExceptionTicketResponse;
use App\Domain\Ticket\Exception\TicketPurchaseSectorException;
use App\Domain\Ticket\Response\TicketPurchaseResponse;
use Exception;

class TicketController
{
    #[Route('/ticket/purchase', methods: ['POST'], name: 'wsTicket')]
    public function ticketEvent(
        Request $request,
        LoggerInterface $logger,
        TicketPurchaseInterface $ticketPurchase,
        TicketPurchaseService $ticketPurchaseService
    ): JsonResponse {
        try {
            $requestData            = $request->toArray();
            $ticketPurchaseDTO      = $ticketPurchase->create($requestData);
            $ticketPurchaseSuccess  = $ticketPurchaseService->purchaseTicket($ticketPurchaseDTO);
            $ticketPurchaseResponse = TicketPurchaseResponse::ticketPurchaseSuccessResponse($ticketPurchaseSuccess);
            $response               = $ticketPurchaseResponse->serialize();
        } catch (TicketPurchaseDTOException $e) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketPurchaseDTOException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch (TicketPurchaseLimitException $e) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketPurchaseLimitException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch (TicketPurchaseSectorException $e) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketPurchaseSectorException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch (TicketPurchasePlaceException $e) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketPurchasePlaceException($e);
            $response = $exceptionTicketResponse->serialize();
        } catch (Exception $e) {
            $exceptionTicketResponse = ExceptionTicketResponse::createTicketGenericException($e);
            $response = $exceptionTicketResponse->serialize();
        }
        return new JsonResponse($response);
    }
}
