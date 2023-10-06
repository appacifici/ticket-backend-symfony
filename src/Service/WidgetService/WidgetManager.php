<?php

namespace App\Service\WidgetService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment as Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\UtilityService\GlobalUtility;
use App\Service\FormService\FormManager;
use App\Service\UserUtility\UserManager;
use App\Service\GlobalConfigService\GlobalTwigExtension;
use App\Service\GlobalConfigService\GlobalConfigManager;
use App\Service\GlobalConfigService\RouterManager;
use App\Service\UtilityService\CacheUtility;

class WidgetManager {
    
    public $twig;
    public $doctrine;
    public $memcached;
    public $requestStack;
    public $mobileDetector;
    public $container;

    /**
     * Oggetti che devono essere disponibili su tutti i widget
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct(             
            Environment $twig, 
            RequestStack $requestStack,
            EntityManagerInterface $doctrine, 
            Container $container,
            public UserManager $userManager,
            public GlobalConfigManager $globalConfigManager,
            private CacheUtility $cacheUtility            
        ) {
        $this->twig             = $twig;
        $this->requestStack     = $requestStack;
        $this->doctrine         = $doctrine;   
        $this->container        = $container;                        
        $this->userManager      = $userManager;                 

        $this->globalConfigManager = $globalConfigManager;
        
        $this->cacheUtility->initPhpCache();
        $this->memcached = $this->cacheUtility;        
    }
    
  
    /**
     * Metodo che controlla se l'utente è loggato ed ha i permessi necessari a utilizzare il core     
     */
    public function getPermissionCore( $action=false, $type=false ) {        
//        if( empty( $this->userManager->isLogged() ) ) {
////            header( 'Location: /admin/login' );
//        }

        //TODO da ripristinare
        return true;

        $this->userManager->getGroupPermission();
        $groupPermission = $this->userManager->getPermissionByGroup();      
        $this->twig->addGlobal( 'permission', $groupPermission );
        
        if( !empty( $action ) && ( empty( $groupPermission ) || empty( $groupPermission->{$action}->{$type} ) ) ) {
            return false;
        }
        return true;
    }    
    
    public function getVersionSite() {
       return  $this->globalConfigManager->getVersionsite();
    }
    
    public function getAllParamsFromGetRequest() {
        $request     = $this->requestStack->getCurrentRequest();
        if ( !empty ($_GET ) )
            return $_GET;
        return false;
    }

    public function getUrlId() {
        $request     = $this->requestStack->getCurrentRequest();
        $feedMatchId = $request->get( 'id' );
        if( !empty( $feedMatchId ) )
            return $feedMatchId;
        return false;
    }
    
    public function getUrlSearchString() {
        $request            = $this->requestStack->getCurrentRequest();
        $urlSearchString    = $request->get( 'searchString' );
        if( !empty( $urlSearchString ) )
            return $urlSearchString;
        return false;
    }
    
    public function getPage() {
        $request     = $this->requestStack->getCurrentRequest();
        $page = $request->get( 'page' );
        if( !empty( $page ) )
            return $page;
        return 1;
    }    
        
    public function getSearch() {
        $request    = $this->requestStack->getCurrentRequest();
        $search   = $request->query->get( 'search' );
        if( !empty( $search ) )
            return $search;
        return false;
    }    
       
    public function getParametersByCustomUri() {
        $params = explode( '-', trim( $_GET['uri'], '/' ) );
        $end =  end( $params );       
        $sexName = false;        
        if( $end == 'uomo' || $end == 'donna' ) {
            array_pop( $params );
            $sexName = $end;
        }        
        return array( 'catSubcatTypology' => end( $params ), 'sex' => $sexName );
    }
        
    public function getUri() {
        $request     = $this->requestStack->getCurrentRequest();
        $uri = $request->get( 'uri' );
        if( !empty( $uri ) )
            return $uri;
        return 1;
    }
    
    public function getBreadcrumb() {
        $request      = $this->requestStack->getCurrentRequest();
        $url          = $request->server->get('REQUEST_URI');       
        return $url;
    }
    
    public function getRequestUri() {
        $request      = $this->requestStack->getCurrentRequest();
        $url          = $request->server->get('REQUEST_URI');       
        return $url;
    }
       
}