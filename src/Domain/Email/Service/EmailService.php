<?php

declare(strict_types=1);

namespace App\Domain\Email\Service;

use App\Domain\Ticket\DTO\TicketPurchaseDTO;
use App\Domain\Ticket\Object\TicketPurchaseSuccess;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailService  {

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig        
    )
    {                
    }

    public function sendTicketPusrchaseEmail(
        TicketPurchaseDTO $ticketPurchases,
        TicketPurchaseSuccess $ticketPurchaseSuccess
    ): void {

        $purchases  = $ticketPurchases->getPurchases();
        $user       = $purchases[0]->getUser();
        $html       = $this->twig->render( "/email/tickerPurchase.html.twig" , [
            'purchases'             => $purchases, 
            'user'                  => $user,
            'ticketPurchaseSuccess' => $ticketPurchaseSuccess->getTickets()
        ]);
        
        $email = (new Email())
            ->from($user->getEmail())
            ->to('noreplay@ticket.com')            
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Ticket.com Acquisto biglietti!')
            ->text('Sending emails is fun again!')
            ->html($html);

        $this->mailer->send($email);
    }

}