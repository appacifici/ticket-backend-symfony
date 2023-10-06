<?php declare( strict_types = 1 );

namespace App\Service\DependencyService;
use App\Service\UtilityService\GlobalUtility;

/**
 * Description of DependenciesModules
 * @author alessandro
 */
class DependencyManager {
        
    public array $dependenciesCSSHead       = array();
    
    public array $dependenciesCSSBody       = array();
    
    public array $dependenciesJSHead        = array();
    
    public array $dependenciesJSBody        = array();
    
    public string $jqueryVersion            = 'min';  
    
    public string $jqueryValidateVersion    = 'min'; 
    
    public string $forceVersion;
    
    public bool $isMobileOrApp              = false;
    
    public object $parameters;
    
    public object $config;


    /**
     * Metodo costruttore della classe che instanzia anche la classe padre
     */
    public function __construct( $paramaters )  {                
        $this->parameters = json_decode( json_encode( $paramaters ), FALSE );
        //$this->globalUtility    = $globalUtility;        
        //$this->browserUtility   = $globalUtility->browserUtility;  

//        $this->setForceVersion( 'm_template' ); 
            
        $this->getSpecificVersion();
        $this->loaderFiles();
        $this->globalDependencies();
    }
    
    public function setForceVersion( $version ) {        
        $this->forceVersion = $version;
       
        $this->isMobileOrApp = false;
//        if( $this->browserUtility->mobileDetector->isMobile() || 
//            $this->forceVersion == 'm_template'
//        ) {
//            $this->isMobileOrApp = true;
//        }
        
    }
    
    /**
     * Metodo che cambia i plugin da caricare in base alle versioni specifiche dei prowser dell'utente
     */
    public function getSpecificVersion() {
        if( !empty( $this->config->isIeVersion ) && $this->config->isIeVersion < 9  ) {
            $this->jqueryVersion         = 'min.1.7.0';
            $this->jqueryValidateVersion = '1.9.0';
        }
    }
            
    /**
     * Metodo che include le librerire globali del sito
     */
    public function globalDependencies() {              
        $this->addDependencyJSHead( $this->parameters->commonPath.'library/jquery/jquery.'.$this->jqueryVersion.'.js' );                        
        $this->addDependencyJSBody( $this->parameters->extensionsJsPath.'modules.init.js' );        
        $this->addDependencyJSBody( $this->parameters->extensionsJsPath.'managerLinks.js' );
    }

    /**
     * Metodo che aggiunge all'array delle dipendenze i file da caricare per il widget passato
     * @param string $fileName
     */
    public function addTplDependencies( $widget ) {        
        $this->getDependency( $widget );        
    }
    
    public function getDependency( $widget ) {
        
    }
        

    /**
     * Metodo che setta a TRUE nell'array relativo al head e quindi include la dipenendenza
     */
    public function addDependencyJSHead( string $dependency ): void {
        $this->dependenciesJSHead[$dependency] = true;
    }

    /**
     * Metodo che setta a TRUE nell'array relativo al body e quindi include la dipenendenza
     */
    public function addDependencyJSBody( string $dependency ): void {
        $this->dependenciesJSBody[$dependency] = true;
        
    }
    
    /**
     * Metodo che setta a TRUE nell'array relativo al head e quindi include la dipenendenza
     */
    public function addDependencyCSSHead( string $dependency ): void {
        $this->dependenciesCSSHead[$dependency] = true;
    }

    /**
     * Metodo che setta a TRUE nell'array relativo al body e quindi include la dipenendenza
     */
    public function addDependencyCSSBody( string $dependency ): void  {
        $this->dependenciesCSSBody[$dependency] = true;
    }

    /**
     * Metodo che ritorna l'array contenente le dipendenze sta inserire dentro il tag head
     */
    public function getJSHead(): array {
        return array_filter( $this->dependenciesJSHead );
    }

    /**
     * Metodo che ritorna l'array contenente le dipendenze sta inserire dentro il tag body
     */
    public function getJSBody(): array {
        return array_filter( $this->dependenciesJSBody );
        
    }

    /**
     * Metodo che ritorna l'array contenente le dipendenze sta inserire dentro il tag head
     */
    public function getCSSHead(): array {
        return array_filter( $this->dependenciesCSSHead );
    }

    /**
     * Metodo che ritorna l'array contenente le dipendenze sta inserire dentro il tag body
     */
    public function getCSSBody(): array {
        return array_filter( $this->dependenciesCSSBody );
    }
    
    public function restoreDependency(): void {        
        $this->loaderFiles();
        $this->globalDependencies();        
    }

    /**
     * Metodo che valorizza gli array con i path delle dipendenze css e js sia quelli della sezione head
     * che quelli della sezione body
     */
    public function loaderFiles() {
        
        $this->dependenciesCSSHead = array(
            $this->parameters->commonPath.'library/bootstrap/css/bootstrap.min.css'                                             => false,            
        );
        
        $this->dependenciesCSSBody = array(
        );
        
        $this->dependenciesJSHead = array(
            $this->parameters->commonPath.'library/jquery/jquery.min.js'                                                        => false,           
        );

        $this->dependenciesJSBody = array(
            $this->parameters->extensionsJsPath.'lazy.js'                                                                       => false,            
                        
        );
                
    }
}