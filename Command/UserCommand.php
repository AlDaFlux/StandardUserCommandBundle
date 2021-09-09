<?php

namespace Aldaflux\AldafluxStandardUserCommandBundle\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Stopwatch\Stopwatch;

use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;



class UserCommand extends Command
{

    protected $entityManager;
    public $reflectUser;
    protected $defauldFields;
    private $passwordHasher;
    
    private $userProviderEntity;

    public function __construct(EntityManagerInterface $em,EntityUserProvider $userProviderEntity=null)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->userProviderEntity = $userProviderEntity;
        
//        dump($this->userProviderEntity);
//        dump($this->userProviderEntity->getClass());
        
        $this->users=$this->entityManager->getRepository('App:User');        
        $this->reflectUser = new \ReflectionClass("App\Entity\User");
        $this->defauldFields= array("id", "username", "fullName", "email", "roles");
    }
    
    function getFieldsName()
    {
        $fields=array();
        foreach ($this->reflectUser->getProperties() as $prop)
        {
            $fields[]=$prop->GetName();
        }
        return(array_intersect($fields,$this->defauldFields));
    }

    
    function hasProperty($property)
    {
        return(in_array($property, $this->getFieldsName()));
    }
    
    function hasFullName()
    {
        return($this->hasProperty("fullname"));
    }
    function hasEmail()
    {
        return($this->hasProperty("email"));
    }
    function hasUsername()
    {
        return($this->hasProperty("username"));
    }
    
    
    function getHeaderTable()
    {
        return(array_map("ucwords",$this->getFieldsName()));
    }
    
    
    

  
}