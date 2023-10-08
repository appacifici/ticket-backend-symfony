<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use App\Entity\Event;
use Exception;

class TicketPurchaseLimitException extends Exception
{

	private Event $event;
	private int   $errorCode;

	public function errorMessage()
  	{
		$errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
		. ': <b>' . $this->getMessage() . '</b>';
		return $errorMsg;
	}

	public function getEvent(): Event
	{
		return $this->event;
	}
	
	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	public function getErrorCode():int
	{
		return $this->errorCode;
	}
	
	public function setErrorCode(int $errorCode):self
	{
		$this->errorCode = $errorCode;

		return $this;
	}
}
