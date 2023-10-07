<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use Exception;

class PurchaseDTOException extends Exception
{

	private int $userId      = 0;
	private array $puschase  = [];	

	private int $user  = 0;
	private int $event = 0;
	private int $place = 0;

	public function errorMessage()
  	{
		$errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
		. ': <b>' . $this->getMessage() . '</b>';
		return $errorMsg;
	}

	public function getNotFoundEntityEvent(): int
	{
		return $this->event;
	}
	
	public function setNotFoundEntityEvent(int $event): self
	{
		$this->event = $event;

		return $this;
	}

	public function getNotFoundEntityUser(): int
	{
		return $this->user;
	}

	public function setNotFoundEntityUser(int $user): self
	{
		$this->user = $user;

		return $this;
	}

	public function getNotFoundEntityPlace(): int
	{
		return $this->place;
	}

	public function setNotFoundEntityPlace(int $place):self
	{
		$this->place = $place;

		return $this;
	}
	
}
