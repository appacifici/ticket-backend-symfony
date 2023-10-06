<?php

namespace App\Service\GlobalConfigService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Twig\Environment as Environment;
use Twig\SimpleFunction;
use Twig_SimpleFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session; 

use App\Service\UtilityService\CacheUtility;
use App\Entity\ExtraConfig;
use App\Entity\MsxSite;
use App\Entity\User;
use App\Service\UserUtility\UserManager;

//require_once __DIR__.'/../../../config/extraConfig.php';

/**********UA For Search Bots**************/
//$UA_SB_ACCOUNT_ID = "UA-121804415-1"; //Replace with the UA Web Property ID.
//$UA_SB_PATH = "ua-searchbots/ua-searchbots.php"; //location of the UA for Search Bots script
//include($UA_SB_PATH);
//https://www.evemilano.com/tracciare-i-bot-con-google-universal-analytics/
//https://www.evemilano.com/monitorare-log-web-server/
/**********UA For Search Bots**************/

class GlobalConfigManager {    
    public $controlSession;     
    public $sessionActive;                    
    public $isIeVersion;     
    protected $genericUtility;    
    protected $twig;    
    protected $container;    
    protected $requestStack;    
    protected $entityManager;    
    protected $request;    
    private $lang;    
    private $currentDomain;    
    private $aExtraConfigs;    
    private $filtersActive;
    private bool    $userIsActive;
    private bool    $isAppVersion;
    private bool    $ampActive;
    private bool    $ampRouteActive;
    private bool    $userIsAdmin;
    private array  $route;
    private string  $currentRoute;
    private string  $requestUriSite;
    private string  $httpProtocol;
    private string  $wwwProtocol;
    private string  $baseAbsoluteUrl;
    private string  $currentRouteCss;
    private string  $versionSite;
    private $user;
    private $paramsByDomain;
    
    
    /**
     * Metodo costruttore della classe che instanzia anche la classe padre
     */
    public function __construct( 
            Environment $twig,
            RequestStack $requestStack,
            Container $container,
            //\App\Service\UtilityService\GlobalUtility $globalUtility ,
            EntityManagerInterface $entityManager,
            public CacheUtility $cacheUtility,
            public UserManager $um
    ) {
        
        //$this->globalUtility    = $globalUtility;
        $this->twig             = $twig;
        $this->requestStack     = $requestStack;
        $this->container        = $container;
        $this->entityManager    = $entityManager;
        $this->cacheUtility     = $cacheUtility;
        //$this->memcached        = $this->cacheUtility->initPhpCache();                
        
        //Crea l'istanza di redis per le cache di secondo livello di doctrine
        // $this->secondLevelCacheUtility = $this->container->get( 'app.cacheUtilitySecondLevelCache' );    
        
        // $params = new \stdClass();
        // $params->subcategoriesType = $this->container->getParameter( 'admin.subcategoriesType' );
        // $params->secondLevelCacheUtility = $this->secondLevelCacheUtility;
                
//        define( 'BESTSELLER_MODEL_REGION_TTL', 3600 );        
//        $this->entityManager->getRepository('App:Banner')->setCacheUtility( $this->secondLevelCacheUtility );                                       
        
        // $params = new \stdClass;
        // $params->secondLevelCacheUtility = $this->secondLevelCacheUtility;
               
        $this->controlSession   = false; //Serve per determinare se il sistema manager dei tamplate debba verificare se un twig sia visibili solo se l'utente loggato
        
        $session                = new Session();
        $email                  = $session->get( 'user' );
                
        $sessionActive = false;
        if (! empty( $email ) ) {
           $sessionActive = true;
        } 
        $this->sessionActive    = !empty($_COOKIE['externalUserCode'] ) ? true : false;
        $this->userIsActive     = $sessionActive;         
        $this->isAppVersion     = false;
        $this->isIeVersion      = false;
        $this->ampActive        = false;
        $this->ampRouteActive   = false;
        $this->lang             = 'it_IT';       
        $this->request          = $this->requestStack->getCurrentRequest();        
        
        $matchUri               = explode( '?', $this->request->server->get( 'REQUEST_URI' ) );
        $this->route            = $this->container->get('router')->match( $matchUri[0] );   
            
        $this->currentRoute     = $this->route['_route'];        
        
        $this->requestUriSite   = str_replace( array('?page=1' ), array(''), $matchUri[0] );
                
        $this->httpProtocol     = $this->container->getParameter( 'app.hostProtocol' );
        $this->wwwProtocol      = $this->container->getParameter( 'app.wwwProtocol' );
        
        
        $this->initExtraConfigs();
        $this->detectIsAmp();
        $this->detectDomain();        
        $this->detectSite();        
        $this->isIsVersion();
        $this->getGlobalVars();        
        $this->registerCustomExtensionTwig();        
        $this->setParamsByDomain();
        
        $this->um = $this->container->get('app.userManager');         
        if( $this->um->isLogged() ) {
            $this->sessionActive    = true;
            $this->userIsAdmin      = true;            
            $this->user = $this->um->getDataUser();
            $this->twig->addGlobal( 'user', $this->user );            
        } else {
//            $this->sessionActive    = false;
            $this->userIsAdmin      = false;
            $this->twig->addGlobal( 'user', false );
           
        }
        
        $this->twig->addGlobal( 'sessionActive', $this->sessionActive );
        $this->twig->addGlobal( 'userIsAdmin', $this->userIsAdmin );
        $this->twig->addGlobal( 'currentBaseUrl', $matchUri[0] );
        $this->baseAbsoluteUrl = $canonicalUrl = $this->httpProtocol.'://'.str_replace( array('m.', '?page=1' ), array('www.', ''), $this->request->server->get( 'HTTP_HOST' ) );
        $this->twig->addGlobal( 'baseAbsoluteUrl', $this->baseAbsoluteUrl );
        $this->twig->addGlobal( 'hostImg', $this->baseAbsoluteUrl );
        
        
    }
    
    public function compressHtml($html) {
        $html = preg_replace("/\n ?+/", " ", $html);
        $html = preg_replace("/\n+/", " ", $html);
        $html = preg_replace("/\r ?+/", " ", $html);
        $html = preg_replace("/\r+/", " ", $html);
        $html = preg_replace("/\t ?+/", " ", $html);
        $html = preg_replace("/\t+/", " ", $html);
        $html = preg_replace("/ +/", " ", $html);
        $html = trim($html);
        return $html;
    }
    
    /**
     * Metodo che gestisce il css about the fold per la rotta corrente

     */
    public function getAboveTheFoldCss( bool $forceRouteCss = false ) :bool {
        $this->currentRouteCss  = !empty( $forceRouteCss ) ? strtolower( $forceRouteCss ) : strtolower( $this->currentRoute );
        
        if( substr( $this->currentRouteCss, 0, 3 ) == 'amp' ) {
            $this->currentRouteCss = substr( $this->currentRouteCss, 3 , strlen( $this->currentRouteCss ) -3 );
        }
        
        $this->twig->addGlobal( 'currentRouteCss', $this->currentRouteCss );
                
        if( $this->versionSite == 'admin' ) {
            return false;
        }        
        //Recupera gli about thefold per AMP
        if( !empty( $this->ampActive ) ) {                
            $css = @file_get_contents( 'css/template/Amp.ATF.'.strtolower( $this->currentRouteCss ).'.css' );
            $css = str_replace( '@charset "UTF-8";', '', $css);
            $this->twig->addGlobal( 'aboveTheFoldCss', $this->compressHtml( trim( $css ))  );
            return true;
        }

        $css = @file_get_contents( 'css/template/Desk.ATF.'.strtolower( $this->currentRouteCss ).'.css' );
        $css = str_replace( '@charset "UTF-8";', '', $css);
        $this->twig->addGlobal( 'aboveTheFoldCss', $this->compressHtml( $css )  );
        return true;
        
    }
    
    /**
     * Metodo che inizializza l'extra config

     */
    public function initExtraConfigs() :void {
        //TODO DA RIPRISTINARE

        // $aExtraConfigs = $this->entityManager->getRepository(ExtraConfig::class)->findAll();
        $aExtraConfigs      = [];
        $newExtraConfigs    = [];
        foreach ( $aExtraConfigs as $config ) {
            $newExtraConfigs[$config->getKeyName()] = $config;
        }
        $this->aExtraConfigs = $newExtraConfigs;
        $this->twig->addGlobal( 'extraConfigs', $this->aExtraConfigs );
    }
        
    
    /**
     * Metodo centralizzato per recuperare tutte le categorie lasciato qui per promemoria genstione in cache dei dati "statici"
     */
    private function getCategories() {
        // $categoryById   = $this->cacheUtility->phpCacheGet( $this->container->getParameter( 'session_memcached_prefix' ).'categoriesById' );
        // $categoryByName = $this->cacheUtility->phpCacheGet( $this->container->getParameter( 'session_memcached_prefix' ).'categoriesByName' );
        
        // if( empty( $categoryById ) || empty( $categoryByName ) ) {
        //     $categories = $this->entityManager->getRepository( 'App:Category' )->findAllCategories( true );
            
        //     $categoryById = array();
        //     $categoryByName = array();            
            
        //     foreach ( $categories as $category ) {                
        //         $categoryById[$category->id]        = $category;
        //         $categoryByName[$category->nameUrl] = $category;
        //     }
        //     $this->cacheUtility->phpCacheSet( $this->container->getParameter( 'session_memcached_prefix' ).'categoriesById', $categoryById, 3600 );
        //     $this->cacheUtility->phpCacheSet( $this->container->getParameter( 'session_memcached_prefix' ).'categoriesByName', $categoryByName, 3600 );
        // } 
        // $this->twig->addGlobal( 'allCategories', (array)$categoryById );
    }
    
    /**
     * Metodo che setta tutte le funzioni custom per Twig
     */
    private function registerCustomExtensionTwig() {        
        global $typeNews;

        //https://twig.sensiolabs.org/doc/advanced.html#functions
//        $this->twig->addExtension( new \nochso\HtmlCompressTwig\Extension( true ) );        
//        $globalTwigExtension = $this->container->get( 'app.globalTwigExtension' );
//        $globalTwigExtension->routerManager->setAmpActive( $this->ampActive );
//        
//        $domain = 'www.'.$this->getCurrentDomain();
//        $base = 'https://'.str_replace( 'app.', 'www.',$domain);
//        $globalTwigExtension->routerManager->setBaseUrl( $base );
    }
    
    /**
     * Metodo che setta le della varibili global per twig
     */
    private function getGlobalVars() {        
        $currentUrl = $this->request->server->get( 'HTTP_HOST' ).$this->request->server->get( 'REQUEST_URI' );
        $this->twig->addGlobal( 'currentUrl', $currentUrl );
        $currentHostUrl = $this->request->server->get( 'HTTP_HOST' );
        $this->twig->addGlobal( 'currentHostUrl', $currentHostUrl );
        $this->twig->addGlobal( 'currentRoute', strtolower( $this->currentRoute ) );
                
        $this->setLanguage();
        $this->twig->addGlobal( 'analytics', $this->getAnalytics() );        
        $this->twig->addGlobal( 'fbPixel', $this->getFbPixel() );        
        $this->twig->addGlobal( 'googleSiteVerification', $this->getGoogleSiteVerification() );        
        $this->twig->addGlobal( 'bingSiteVerification', $this->getBingSiteVerification() );        
        $this->twig->addGlobal( 'isAppVersion', $this->getIsAppVersion() );
        $this->twig->addGlobal( 'ampActive', $this->ampActive );
        $this->twig->addGlobal( 'search', '' );
        $this->twig->addGlobal( 'currentDomain', str_replace( 'app.', 'www.', $this->currentDomain ) );
    }       
    
    /**
     * Metodo che setta la lingua del sit
     */
    private function setLanguage() {
        $this->twig->addGlobal( 'lang', $this->lang );
    }
    
    /**
     * Metodo che determina se deve essere attiva la versione amp
     */
    private function detectIsAmp() {
        $this->ampRouteActive   = false;
//        echo $this->route['_route'];
        //Abilita il link amphtml nell'head per le sezioni sottostanti
        switch( $this->route['_route'] ) {                
            case 'detailNews':
            case 'detailNews1':
            case 'detailNews2':
                $this->ampRouteActive   = true;                
            break;
        }
        
        if( strpos( ' '.$this->request->server->get( 'REQUEST_URI' ), '/amp' ) !== FALSE ) {     
            //GESTISCE URL AMP
            $this->ampActive = false;
            switch( $this->route['_route'] ) {
                case 'homepageAmp':
                case 'detailNewsAmp':
                case 'listArticlesAmp':
                case 'listArticlesTeamAmp':
                    $this->ampActive = true;
                break;         
                default:
                    $this->ampActive = true;
                break;
            }
            return false;
        }
        
        //gestisce la versione mobile classica
        //TODO da ripristinare
        // if( $this->mobileDetector->isMobile()  &&  strpos( ' '.$this->request->server->get( 'HTTP_HOST' ), 'm_' ) === FALSE ) {     
        //     $this->ampActive        = false;            
            
        //     switch( $this->route['_route'] ) {                               
        //         case 'homepage':
        //         case 'detailNews':
        //         case 'listArticles':
        //         case 'listArticlesTeam':
        //             $this->ampActive = false;//FORZA IN AMP ANCHE PER MOBILE METTI A TRUE                                        
        //         break;
        //         default:
        //             $this->ampActive = false;
        //         break;
        //     }
        //     return true;
        // }              
                 
    }
    
    /**
     * Determina quale dominio sta lanciando il cms
     */
    private function detectDomain(): void {
        if( strpos( $this->request->server->get( 'HTTP_HOST' ), 'acquistigiusti.it', '0' ) !== false ) {
            $this->currentDomain = 'acquistigiusti.it';            
        } else if( strpos( $this->request->server->get( 'HTTP_HOST' ), 'tricchetto.homepc.it', '0' ) !== false ) {
            $this->currentDomain = 'tricchetto.homepc.it';             
        }
    }
    
    /**
     * Metodo che ricava la versione del sito dal dominio che effettua la richiesta
     */
    private function detectSite() {        
        $this->versionSite = $this->container->getParameter( 'app.default_version_site' );               
        //$this->container->get('translator')->setLocale($this->lang);        
                     
//        $this->ampActive = true;
//        $this->versionSite =  'amp_template';  
//        $canonicalUrl = 'https://'.str_replace( array( 'm.', '/amp/' ), array('www.','/'), $this->request->server->get( 'HTTP_HOST' ) ).str_replace( array( 'm.', '/amp/' ), array('www.','/'), $this->requestUriSite );
//        $this->twig->addGlobal( 'canonicalUrl', $canonicalUrl );
//        return true;
//        
//        //SE DI TRATTA DELLA URL DELL'APPLICAZIONE
//        if( strpos( $this->request->server->get( 'HTTP_HOST' ), 'app.', '0' ) !== false || strpos( $this->request->server->get( 'HTTP_HOST' ), 'www.x-diretta.it', '0' ) !== false ) {
//            $this->versionSite = 'app_'.str_replace( 'm_', '', $this->versionSite );
//            $this->isAppVersion = true;   
//            $this->ampActive = true;
//            return true;
//        }        
        
        //URL AMP SPECIFICA O VERSIONE MOBILE FORZATA IN AMP
        if( $this->ampActive ) {       
            $versionCurrent = str_replace( array('m_', 'app_', 'www.', 'amp_'), '',$this->versionSite ); 
            $this->versionSite =  'amp_'.$versionCurrent;                        
            
            $canonicalUrl = $this->httpProtocol.'://'.str_replace( array( 'm.', '/amp/' ), array('www.','/'), $this->request->server->get( 'HTTP_HOST' ) ).str_replace( array( 'm.', '/amp/' ), array('www.','/'), $this->requestUriSite );
            $this->twig->addGlobal( 'canonicalUrl', trim( $canonicalUrl, '/' ) );
            return true;
        }
        
//        if( $this->mobileDetector->isTablet() ) {            
//            $this->versionSite = 'amp_'.$this->versionSite;
////            $this->versionSite = $this->versionSite;
//            
//            $canonicalUrl = $this->httpProtocol.'://'.str_replace( 'm.', 'www.', $this->request->server->get( 'HTTP_HOST' ) ).$this->requestUriSite;
//            $this->twig->addGlobal( 'canonicalUrl', trim( $canonicalUrl, '/' ) );
//            $this->twig->addGlobal( 'alternateUrl', false );
//            
//        } else if( $this->mobileDetector->isMobile() && strpos( $this->request->server->get( 'HTTP_HOST' ), 'app.', '0' ) === false ) {            
//            $this->versionSite = 'm_'.$this->versionSite;
//            
////            exit;
//             //Se il dispositivo è un mobile lo reindirizza alla url mobile
////            if( strpos( ' '.$this->request->server->get( 'HTTP_HOST' ), 'm.' ) === FALSE ) {
////                $url = 'http://m.'.str_replace( 'www.', '', $this->request->server->get( 'HTTP_HOST' ) ).$this->request->server->get( 'REQUEST_URI' );
////                header( 'Location: '.$url.'');
////                exit;
////            }    
//            
//            $canonicalUrl = $this->httpProtocol.'://'.str_replace( array('m.', '?m=1'), array('www.',''), $this->request->server->get( 'HTTP_HOST' ) ).$this->requestUriSite;
//            $this->twig->addGlobal( 'canonicalUrl', str_replace( array('m.', '?m=1'), array('www.',''), trim( $canonicalUrl, '/' ) ) );
//            $this->twig->addGlobal( 'alternateUrl', false );
//            
//        } else {            
//            
            if( empty( $this->ampActive ) ) {  
                
                $canonicalUrl = $this->httpProtocol.'://'.str_replace( array('m.', '?page=1' ), array('www.', ''), $this->request->server->get( 'HTTP_HOST' ) ).$this->requestUriSite;
                $this->twig->addGlobal( 'canonicalUrl', trim( $canonicalUrl, '/' ) );
                
                $alternateUrl = $this->httpProtocol.'://'.str_replace( 'www.', 'm.', $this->request->server->get( 'HTTP_HOST' ) ).$this->requestUriSite;
                if( $this->currentDomain == 'chedonna.it' )
                    $this->twig->addGlobal( 'alternateUrl', $alternateUrl );
                else
                    $this->twig->addGlobal( 'alternateUrl', false );
                    
                
                if( !empty( $this->ampRouteActive ) ) {
                    $page = !empty( $_GET['page'] ) && $_GET['page'] != 1 ? '?page='.$_GET['page'] : '';
                    $ampHtmlUrl = $this->httpProtocol.'://'.str_replace( 'm.', 'www.', $this->request->server->get( 'HTTP_HOST' ) ).'/amp'.$this->requestUriSite.$page;
                    $this->twig->addGlobal( 'ampHtmlUrl', $ampHtmlUrl );                      
                }
//            }     
        }    
        
        
//        //Se la url è quella del mobile, la variabile non Ã¨ gia settata a mobile la setta
//        if( strpos( ' '.$this->request->server->get( 'HTTP_HOST' ), 'm.' ) !== FALSE && strpos( ' '.$this->versionSite, 'm_' ) === FALSE  ) {
//            $this->versionSite = 'm_'.$this->versionSite;
//        }
                
//        //Se non è mobile e la url è mobile fa la redirect alla desktop
//        if( !$this->mobileDetector->isMobile()  &&  strpos( ' '.$this->request->server->get( 'HTTP_HOST' ), 'm.' ) !== FALSE ) {
//            $url = 'https://'.str_replace( 'm.', '', $this->request->server->get( 'HTTP_HOST' ) ).$this->request->server->get( 'REQUEST_URI' );
//            header( 'Location: '.$url.'');
//            exit;
//        }                                     
        
        
        if( strpos( $this->request->server->get( 'REQUEST_URI' ), '/admin', '0') !== false ) {
            $this->versionSite = 'admin';
        }        
        
    }    
    
    
    public function forceMobileForTable() {
        switch( $this->currentDomain ) {
            case'xxx': 
                return true;
            break;
        }
        return false;
    }
    
    /**
     * Gestisce quale versione garfica caricare in base al sito aperto
     */
    public function loadPlugin(): void {
        $versioneDependency =  str_replace( array( 'app_', 'm_', 'amp_' ), '', $this->versionSite ) ;
        switch( $versioneDependency ) {
            case 'xxx':
                $this->versionSite = str_replace( $versioneDependency, 'alchimist', $this->versionSite );
            break;
        }             
    }        
    
    /**
     * Determina se è¨ un mobile
     */
    public function isMobile(): bool {
        //TODO-Ale da riattivare se risolvi problema bundle mobiledetect
        return false;
        // $mobileDetector = $this->globalUtility->browserUtility->mobileDetector;
        // return $mobileDetector->isMobile();
    }    
    
    /**
     * Ritorna la false se non è explorer oppure la versione corrente di IE
     */
    private function isIsVersion() {  
       // $this->isIeVersion = $this->globalUtility->browserUtility->getIsIeVersion();
    }
    
    /**
     * Metodo che ritorna la versione del sito determinata in base al dominio
     */
    public function getVersionSite(): string {
        return $this->versionSite;
    }
    /**
     * Metodo che ritorna la versione del sito determinata in base al dominio
     */
    public function getExtraConfigs() {
        return $this->aExtraConfigs;
    }
    
    /**
     * Ritorna la variabile che determina se nell'inclusione dinamica dei twig deve controllare  i
     * moduli che richiedono la sessione attiva
     */
    public function getControlSession() {
        return $this->controlSession;
    }
    
    /**
     * Ritorna la variabile che determina se la sessione utente Ã¨ attiva o no
     */
    public function getSessionActive() {
        return $this->sessionActive;
    }
    
    /**
     * Ritorna la variabile che determina se l'utente Ã¨ attivo o no
     */
    public function getUserIsActive() { 
        return $this->userIsActive;
    }
    
    /**
     * Ritorna se il browser 
     */
    public function getIsIeVersion() {
        return $this->isIeVersion;
    }
        
    /**
     * Ritorna se app 
     */
    public function getIsAppVersion() {
        return $this->isAppVersion;
    }
        
    /**
     * Ritorna se il domonio che carica il cms 

     */
    public function getCurrentDomain() {
        return $this->currentDomain;
    }
    
    /**
     * Ritorna i parametri specifici di un dominio
     */
    public function getParamsByDomain() {
        return $this->paramsByDomain;
    }
    
    /**
     * Ritorna i parametri specifici di un dominio
     */
    public function getAmpActive() {
        return $this->ampActive;
    }
    
    /**
     * Ritorna i parametri specifici di un dominio
     */
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    /**
     * Ritorna i parametri specifici di un dominio
     */
    public function getCurrentRouteCss() {
        return $this->currentRouteCss;
    }
    
    /**
     * Setta i parametri specifici di un dominio
     */
    public function setCurrentRouteCss( $routeName ) {
        return $this->currentRouteCss = $routeName;
    }
    
    /**
     * Ritorna i parametri specifici di un dominio
     */
    public function getExtraConfig() {
        return $this->aExtraConfigs;
    }
    
    /**
     * Ritorna la url assoluta del dominio
     */
    public function getBaseAbsoluteUrl() {
        return $this->baseAbsoluteUrl;
    }
    /**
     * Ritorna che recuper i filtri attivi
     */
    public function getFiltersActive() {
        return $this->filtersActive;
    }
    
    /**
     * Ritorna che recuper i filtri attivi
     */
    public function setFiltersActive( $filtersActive ) {
        $this->filtersActive = $filtersActive;
    }
    
    /**
     * Metodo che setta il codice di webmastertools per la versione del sito
     */
    private function getGoogleSiteVerification() {    
        $code = '';
        switch( $this->currentDomain ) {
            case 'xxx.it':
            case 'xxx.xxx.it':
                $code =  'A91SoRAqUDPL1tjWWtrx_zx2PwJXVi2D9SRMd7rnztQ';
            break;
            
        }        
        return $code;
    }
    
    /**
     * Metodo che setta il codice di webmastertools per la versione del sito
     */
    private function getBingSiteVerification() {    
        $code = '';
        switch( $this->currentDomain ) {
            case 'xxx.it':
            case 'xxx.homepc.it':
                $code = 'ABC6EE076983C19103243A9F1BA2EDEA';                    
            break;
            
        }        
        return $code;
    }
    
    /**
     * Metodo che setta il pixel di facebook
     * @return boolean|string
     */
    private function getFbPixel() {
        $fbPixel = '';        
        
        if( $_SERVER['SERVER_NAME'] == 'staging.xxx.it' ) {
            return false;
        } 
//        return $fbPixel;
        
        switch( $this->currentDomain ) {
            case 'xxx.it':
//            case 'tricchetto.homepc.it':
                if( empty( $this->ampActive ) ) {
                    $fbPixel = "";                
                } else {
//                    $fbPixel = '<amp-pixel src="https://www.facebook.com/tr?id=336559373945147&ev=PageView&noscript=1" layout="nodisplay"></amp-pixel>';   
                }
            break;                           
            case 'xxx.homepc.it':
//                if( empty( $this->ampActive ) ) {
//                    $fbPixel = "";                
//                } else {
//                    $fbPixel = '<amp-pixel src="https://www.facebook.com/tr?id=336559373945147&ev=PageView&noscript=1" layout="nodisplay"></amp-pixel>';   
//                }
            break;                           
        } 
        return $fbPixel;
    }
    
    /**
     * Metodo che setta il codice di analytics per la versione del sito
     */
    private function getAnalytics() {
        $analyticsHead = '';
        $analyticsBody = '';       
        
        if( $_SERVER['SERVER_NAME'] == 'staging.acquistigiusti.it' ) {
            $this->twig->addGlobal( 'analyticsHead', false );
            $this->twig->addGlobal( 'analyticsBody', false );
            return false;
        } 

        switch( $this->currentDomain ) {
            case 'xxx.it':            
//            case 'tricchetto.homepc.it':
                if( empty( $this->ampActive ) ) {
                    $analyticsHead = "<!-- Google Tag Manager -->
                        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                        })(window,document,'script','dataLayer','GTM-PKNC7SL');</script>
                    ";

//                    $analyticsBody = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N26SLDS" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';                                        
//                    $analyticsHead = '';
                    $analyticsBody = '<!-- Google Tag Manager (noscript) -->
                    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PKNC7SL"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
                    <!-- End Google Tag Manager (noscript) -->';
                    
                } else {
                    $analyticsHead = '<!-- AMP Analytics --><script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
                    $analyticsBody = '<amp-analytics config="https://www.googletagmanager.com/amp.json?id=GTM-5DCDJQX&gtm.url=SOURCE_URL" data-credentials="include"></amp-analytics>';  
                }
            break;                           
            case 'xxx.xxx.it':
//                if( empty( $this->ampActive ) ) {
//                    $analyticsHead = "<!-- Google Tag Manager -->
//                        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
//                        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
//                        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
//                        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
//                        })(window,document,'script','dataLayer','GTM-PKNC7SL');</script>
//                    ";
//
////                    $analyticsBody = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N26SLDS" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';                                        
////                    $analyticsHead = '';
//                    $analyticsBody = '<!-- Google Tag Manager (noscript) -->
//                    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PKNC7SL"
//                    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
//                    <!-- End Google Tag Manager (noscript) -->';
//                    
//                } else {
//                    $analyticsHead = '<!-- AMP Analytics --><script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
//                    $analyticsBody = '<amp-analytics config="https://www.googletagmanager.com/amp.json?id=GTM-5DCDJQX&gtm.url=SOURCE_URL" data-credentials="include"></amp-analytics>';
//                }
            break;
        } 
        $this->twig->addGlobal( 'analyticsHead', $analyticsHead );
        $this->twig->addGlobal( 'analyticsBody', $analyticsBody );
        return $analyticsHead;
    }    
    
    /**
     * Metodo che ritorna lo skin da implementare nel css

     */
    public function setParamsByDomain() {
        $params = new \stdClass();
        $params->urlPlayStore = null;
        $params->urlIosStore = null;
        $params->logoImg = null;
        
        switch( $this->versionSite ) {
            case 'sd':
            case 'ds':
            case 'sd':
                $params->urlPlayStore = 'https://play.google.com/store/apps/details?id=com.nextmedia.direttagoal&hl=it';
                $params->urlIosStore = 'https://itunes.apple.com/it/app/direttagoal/id547046910?mt=8';
                $params->logoImg = 'https://www.direttagoal.it/images/miniLogoDirettagoal.png';
            break;
        }
        $this->paramsByDomain = $params;
        $this->twig->addGlobal( 'paramsByDomain', $this->paramsByDomain );
    }
    
    public function getUserManager() {
        return $this->um;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    
}
