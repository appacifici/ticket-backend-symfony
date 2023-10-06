<?php

namespace App\Service\UserUtility;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\UtilityService\CacheUtility;
use Symfony\Component\Cache\CacheItem;
use App\Entity\User;
use App\Entity\GroupSectionPermission;

class UserManager {
    
    protected $groupPermission  = [];

    /**
     * Oggetti che devono essere disponibili su tutti i widget
     */
    public function __construct(
        protected EntityManagerInterface $doctrine, 
        public CacheUtility $cacheUtility, 
        protected Container $container
    ) {        
        $this->doctrine         = $doctrine;
        $this->container        = $container;        
        $this->cacheUtility     = $cacheUtility;
        $this->cacheUtility->initPhpCache();
//        $this->getGroupPermission();
    }
    
    /**
     * Metodo che ritorna il tipo di profilo dell'utente
     */
    public function getUserPofile( $userId ) {
        $userProfile    = null;
        $user           = $this->doctrine->getRepository(User::class)->find($userId);
        if( !empty( $user ) )
            $userProfile    = $user->getRole();
        
        return $userProfile;
    }
    
    /**
     * Metodo che gestisce la sessione e la cache al login     
     * @return boolean
     */
    public function loginUser( string $email, string $password ): bool {        
        $user = $this->doctrine->getRepository(User::class)->findByEmailePassword( $email, $password, true );        
        if( !empty( $user ) ) {
            
            $email = $user->email;
            if(is_object($user->registerAt))
                $RegisterDate = $user->registerAt->date;
            else
                $RegisterDate = $user->registerAt;
            
            $RegisterDate = '';
            
            $code = $this->container->getParameter( 'app.userCode' ); 
            $userCode = $email.$RegisterDate.$code;
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            setcookie("userCode", $userCode, time()+3600, "/", $_SERVER['HTTP_HOST'] );                        
            $this->cacheUtility->phpCacheSet( $this->container->getParameter( 'session_memcached_prefix' ).'user_'.$userCode , $user, 3600 );            
            
            return true;
        } else {
            
            return false;
        }
    }
    
    /*
     * Se l'utente è in cache ritorna i dati dell'utente
     */
    public function getDataUser() {
        if( empty( $this->isLogged() ) )
            return false;        
        
        $user = $this->cacheUtility->phpCacheGet( $this->container->getParameter( 'session_memcached_prefix' ).'user_'.$_COOKIE['userCode'] );        

        if( !empty( $user ) )
            return $user;
    }
    
    /**
     * Metodo che cotrolla che l'utente sia loggato
     */
    public function isLogged() {        
        if( !empty( $_COOKIE['userCode'] ) && !empty($this->cacheUtility->phpCacheGet( $this->container->getParameter( 'session_memcached_prefix' ).'user_'.$_COOKIE['userCode'])) )
            return true;
        else
            return false;
    }
    
    
    public function getGroupPermission () {
        if(!empty( $this->groupPermission ) )
            return $this->groupPermission;
        
        $listGroupPermissions = $this->doctrine->getRepository( GroupSectionPermission::class )->findAll();
        
        $entityColumns = $this->doctrine->getClassMetadata( GroupSectionPermission::class )->getFieldNames();
        $aGroups = array();
        
        foreach( $listGroupPermissions AS $listGroupPermission ) {                        
            $itemGroups = explode( '-', $listGroupPermission->getPermission() );
  
            $newGroups = array('read' => $itemGroups[0],
                            'edit' =>  ( !empty( $itemGroups[1] ) ?  $itemGroups[1] : 0 ),
                            'remove' => !empty( $itemGroups[2] ) ?  $itemGroups[2] : 0,
                            'create' => !empty( $itemGroups[3] ) ?  $itemGroups[3] : 0 );

//            //Se l'utente è un super admin forze tutti i permessi
//            if( $listGroupPermission->getGroup()->getId() == 1 ) {
//                $newGroups = array('read' => 1, 'edit' => 1, 'remove' => 1, 'create' => 1 );                                                 
//            }
                       
            $aGroups[$listGroupPermission->getGroup()->getId()][$listGroupPermission->getGroupSection()->getRoute()] = $newGroups;
        }
                        
        $this->groupPermission =  json_decode(json_encode( $aGroups ), FALSE);        
        return $this->groupPermission;
    }
    
    public function getPermissionByGroup ( $group = false ) {
        if( empty( $group ) ) {
            $user = $this->getDataUser();
            if( empty( $user ) )
                return false;            
            $group = $user->role->id;
        }
        
        if( empty( $this->groupPermission ) ) 
            return false;
        
        
        return $this->groupPermission->{$group};
    }

}