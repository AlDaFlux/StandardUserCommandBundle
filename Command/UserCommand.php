<?php

namespace Aldaflux\AldafluxStandardUserCommandBundle\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command; 
 
use App\Entity\User;



class UserCommand extends Command
{

    public $reflectUser;
    protected $defauldFields;
    private $passwordHasher;
    

    public function __construct(protected EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->users=$this->entityManager->getRepository(User::class);        
        
//        $this->reflectUser = new \ReflectionClass("App\Entity\User");
        $this->reflectUser = new \ReflectionClass(User::class);
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