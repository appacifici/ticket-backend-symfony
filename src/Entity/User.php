<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "user")]
#[ORM\UniqueConstraint(name: "unq_user_email", columns: ["email"])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User {
    
    const STATUS = [
        'DISATTIVO' => 0,
        'ATTIVO' => 1
    ];
        
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]    
    private $id;       
        
    #[Assert\NotBlank( message: 'Inserire il nome' )]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )] 
    #[ORM\Column( name:"name", type: "string", length: 255 )]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )] 
    private $name;
    
    #[Assert\NotBlank( message: 'Inserire il cognome' )]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]    
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column( name:"surname", type: "string", length: 255 )]    
    private $surname;
               
    #[Assert\NotBlank( message: "Inserire l'email" )]
    #[Assert\Email(
        message: 'Inserire un formato di email valido',
    )]         
    #[ORM\Column( name:"email", type: "string", length: 255 )]    
    private $email;
            
    #[Assert\NotBlank( message: 'Inserire username' )]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} nel e del tipo aspettato: {{ type }}.',
    )]    
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column( name:"username", type: "string", length: 255)]    
    private $username;        
    
    #[Assert\NotBlank( message: 'Inserire la password' )]
    #[Assert\Type(
        type: 'string',
        message: 'Il valore {{ value }} non è del tipo aspettato: {{ type }}.',
    )]    
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Inserire almeno {{ limit }} caratteri',
        maxMessage: 'Inserire massimo {{ limit }} caratteri',
    )]
    #[ORM\Column( name:"password", type: "string", length: 255)]      
    private $password;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }        
 
}