<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use Exception;

class PurchaseDTOException extends Exception
{

	const EMPTY_PURCHASE 	 			= 1;
	const PURCHASE_MISSING_EVENT_ID 	= 2;
	const PURCHASE_MISSING_PLACE_TYPE 	= 3;
	const PURCHASE_MISSING_PLACE_ID 	= 4;

	const PURCHASE_ERROR_MESSAGE 		= [
		self::EMPTY_PURCHASE 				=> 'Missin Purchase element',
		self::PURCHASE_MISSING_EVENT_ID 	=> 'Missin EventId element',
		self::PURCHASE_MISSING_PLACE_TYPE 	=> 'Missin PlaceType element',
		self::PURCHASE_MISSING_PLACE_ID 	=> 'Missin PlaceId element',
	];

	private bool $hasException 	= false;
	private mixed $userId    	= null;
	private array $puschases 	= [];	

	private int $user  			= 0;
	private int $event 			= 0;
	private int $place 			= 0;

	public function hasException(): bool {
		return $this->hasException;
	}

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
		$this->hasException = true;
		$this->event = $event;

		return $this;
	}

	public function getNotFoundEntityUser(): int
	{
		return $this->user;
	}

	public function setNotFoundEntityUser(int $user):self
	{
		$this->hasException = true;
		$this->user 		= $user;

		return $this;
	}

	public function getNotFoundEntityPlace():int
	{
		return $this->place;
	}

	public function setNotFoundEntityPlace(int $place):self
	{
		$this->hasException = true;
		$this->place 		= $place;

		return $this;
	}
	
	public function getUserId(): mixed
	{
		return $this->userId;
	}

	public function setUserId(mixed $userId):self
	{
		$this->hasException = true;
		$this->userId 		= $userId;

		return $this;
	}

	public function getPuschases(): array
	{
		return $this->puschases;
	}

	public function setPuschases( int $field, ?int $key ):self
	{
		$this->hasException 		= true;
		$this->puschases[$key][]  	= $field;

		return $this;
	}
}
