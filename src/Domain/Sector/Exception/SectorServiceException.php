<?php

declare(strict_types=1);

namespace App\Domain\Sector\Exception;

use App\Entity\Sector;
use Exception;

class SectorServiceException extends Exception
{
	const TICKET_SOLD_OUT 	 				= 1;	
	const TICKET_SECTOR_SOLD_OUT			= 2;	

	const SECTOR_ERROR_MESSAGE 		= [
		self::TICKET_SOLD_OUT 				=> 'Ticket sold out',		
		self::TICKET_SECTOR_SOLD_OUT 		=> 'Ticket sector sold out'		
	];

	private bool 	$hasException 			= false;	
	private array   $listExceptions 		= [];	
	private Sector  $sector;

	public function hasException(): bool {
		return $this->hasException;
	}

	public function getListException(): array {
		return $this->listExceptions;
	}

	public function addItemListException( int $typeError ): void {
		$this->hasException 	= true;
		$this->listExceptions[] = $typeError;
	}

	public function errorMessage()
  	{
		$errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
		. ': <b>' . $this->getMessage() . '</b>';
		return $errorMsg;
	}

	public function setSector(Sector $sector): self
	{
		$this->hasException = true;
		$this->sector 		= $sector;

		return $this;
	}

	public function getSector(): Sector
	{
		return $this->sector;
	}
	
	
}