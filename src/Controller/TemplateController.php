<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

class TemplateController extends AbstractController
{
    private $onCallPhp              = false;
    public $versionSite            = null;
    private $folderTemplate         = null;
    private $callJsLoadPage         = array();
    public $memcached              = null;
    public $isMobile               = null;
    public $globalConfigManager    = null;
    public $respondeCode           = 202;
    private $finder                 = null;

    /**
     * Metodo statico che fa la sottoscrizione run time dei servizi per la retrocompatibilita con le versioni precedenti si symfony
     * @return type
     */
    public static function getSubscribedServices(): array
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../Service/WidgetService/');

        /**
         * Trick per retrocompatibilita vecchie versioni symfony per accedere ai servici con ->get('app.servizio' )
         * Aggiungere qui i core che si vogliono richiamare in questa vecchia maniera
         */
        return array_merge(
            parent::getSubscribedServices(),
            [
                'app.userManager'                               => \App\Service\UserUtility\UserManager::class,
                'app.globalConfigManager'                       => \App\Service\GlobalConfigService\GlobalConfigManager::class,
                'app.dependencyManagerTemplate'                 => \App\Service\DependencyService\DependencyManagerTemplate::class,
                "app.widgetManager"                             => \App\Service\WidgetService\WidgetManager::class,
                "app.coreAlert"                                 => \App\Service\WidgetService\CoreAlert::class,
                "app.coreAdminMenu"                             => \App\Service\WidgetService\CoreAdminMenu::class,
            ]
        );
    }

    /**
     * Ritorna la versione vorresnte del sito
     * @return type
     */
    public function getVersionSite(): string
    {
        $this->globalConfigManager = $this->container->get('app.globalConfigManager');
        return $this->globalConfigManager->getVersionSite();
    }

    /**
     * Metodo publico per il settaggio dei paramertri base iniziali
     */
    public function baseParameters(): void
    {

        //Avvio il servizio che gestisce il recupero delle dipendenze javascript
        $this->globalConfigManager = $this->container->get('app.globalConfigManager');

        $this->forceRouteCss = !empty($this->forceRouteCss) ?  $this->forceRouteCss : false;
        $this->globalConfigManager->getAboveTheFoldCss($this->forceRouteCss);

        $this->cacheUtility         = $this->globalConfigManager->cacheUtility;
        $this->versionSite          = $this->globalConfigManager->getVersionSite();
        $this->controlSession       = $this->globalConfigManager->getControlSession();
        $this->sessionActive        = $this->globalConfigManager->getSessionActive();
        $this->userIsActive         = $this->globalConfigManager->getUserIsActive();
        $this->isIeVersion          = $this->globalConfigManager->getIsIeVersion();
        $this->isMobile             = $this->globalConfigManager->isMobile();

        $versioneDependency = ucfirst(str_replace(array( 'app_', 'm_', 'amp_' ), '', $this->versionSite));

        if ('admin' == strtolower($versioneDependency)) {
            $this->dependencyManager = $this->container->get('app.dependencyManagerAdmin');
        } else {
            $this->dependencyManager = $this->container->get('app.dependencyManagerTemplate');
        }

        $this->dependencyManager->setForceVersion($this->versionSite);
    }

    /**
     * Recupera la second level cache
     * @return type
     */
    public function getSecondLevelCache()
    {
        $this->baseParameters();
        return $this->globalConfigManager->secondLevelCacheUtility;
    }

    /**
     * Metodo che setta i parametri necessari all'utilizzo della classe
     */
    public function setParameters($extraParams = false)
    {
        $this->finder = new Finder();
        $this->finder->files()->in(__DIR__ . '/../../templates/template/');

        //Inizializzazione variabili twig globali, da fare qui prima del load altrimenti non le fa più aggiungere a runtime ma le fa modificare
        $this->container->get('twig')->addGlobal("prevUrl", false);
        $this->container->get('twig')->addGlobal("nextUrl", false);
        $this->container->get('twig')->addGlobal("robots", false);

        //cicla i file trovati ed aggiunge runtime le varibili globali utili al template manager per costruire la pagina
        foreach ($this->finder as $file) {
            $filename = str_replace(array( '.html.twig' ), array(''), $file->getFilename());
            $this->container->get('twig')->addGlobal("fetch_" . $filename, false);
        }
        $this->forceRouteCss = $extraParams;

        //Recupera i parametri dalle configurazioni parameters di ambiente
        $this->baseParameters();
        $this->folderTemplate   = $this->getParameter('app.folder_templates_xml');
        $this->folderCss        = $this->getParameter('app.folder_css') . $this->versionSite;
        $this->folderJs         = $this->getParameter('app.folder_js') . $this->versionSite;
        $this->folderJsMin      = $this->getParameter('app.folder_js_minified') . $this->versionSite;
        $this->compactSite      = $this->getParameter('app.compactSite');
        $this->compactSite      = $this->versionSite == 'admin' ? false : $this->getParameter('app.compactSite');
        $this->extension        = $this->getParameter('app.extensionTpl');

        if (!empty($this->compactSite)) {
            $this->folderCss    = $this->getParameter('app.cdn') . $this->folderCss . $this->getParameter('app.compactVersion');
            $this->folderJs     = $this->getParameter('app.cdn') . $this->folderJsMin . $this->getParameter('app.compactVersion');
        }

        $this->container->get('twig')->addGlobal('versionSite', $this->versionSite);
        $this->container->get('twig')->addGlobal('isMobile', $this->isMobile);
        $this->container->get('twig')->addGlobal('cdn', $this->getParameter('app.cdn'));
    }

    /**
     * Inizializza tutto il motore
     * @param type $fileStructure
     */
    public function init(string $fileStructure, Request $request, object $extraParams = null)
    {
        $this->forceRouteCss = !empty($extraParams->forceRouteCss) ? $extraParams->forceRouteCss : false;
        $this->setParameters($this->forceRouteCss);

        $fileSection        = str_replace('.xml', '', $fileStructure);

        //Setta se esiete l'id del match da aprire da url
        $openMatchId        = !empty($extraParams->openMatchId) ? $extraParams->openMatchId : null;
        $date               = !empty($extraParams->date) ? $extraParams->date : null;
        $controllerPage     = !empty($extraParams->controllerPage) ? $extraParams->controllerPage : null;
        $categoryUrlName    = !empty($extraParams->categoryUrlName) ? $extraParams->categoryUrlName : null;
        $seasonUrlName      = !empty($extraParams->seasonUrlName) ? $extraParams->seasonUrlName : null;
        $tabLivescore       = !empty($extraParams->tabLivescore) ? $extraParams->tabLivescore : null;
        $this->responsePage = !empty($extraParams->responsePage) ? $extraParams->responsePage : null;


        $this->container->get('twig')->addGlobal('date', $date);
        $shareFb = !empty($request->get('shareFb')) ? 1 : 0;

        //determina quale versione del sito caricare
        $fileStructure = $this->versionSite . '/' . $fileStructure;

        //Array dei tpl che necessitano che l'utente sia loggato
        $tplViewTplSessionActive = ['widget_UserMenu' => 'widget_ButtonLogin'];

        //Array dei tpl che necessitano che l'utente sia attivo
        $disableTplUserIsNotActive = array(
//            'widget_UserCoverMenuModify' => 'widget_RegistrationStep2',
        );


        $this->setXml($this->folderTemplate, $fileStructure);
        $this->setSessionActive(true, $this->sessionActive);
        $this->setViewTplSessionActive($tplViewTplSessionActive);

        $controlUserIsActive = !empty($this->sessionActive) ? true : false;
        $userIsActive = !empty($this->userIsActive) ? $this->userIsActive : false;

        $this->setUserIsActive($controlUserIsActive, $userIsActive);
        $this->disableTplUserIsNotActive($disableTplUserIsNotActive);

        $this->initTemplate();
//        $main->setMetaPropertys();

        $this->addDependencies($fileStructure);

        //chiuda la connessione a memcached
        $this->cacheUtility->closePhpCache();


////        return $this->render( $this->versionSite.'/_index'.$this->extension, array(
        $html = $this->container->get('twig')->render($this->versionSite . '/_index' . $this->extension, array(
            // 'appConfig'         => $this->getParameter( 'app.js' ),
            'appConfig'         => '',
            'compactSite'       => $this->compactSite,
            'compactVersion'    => $this->getParameter('app.compactVersion'),
            'nameFileCssVerSite' => str_replace(array( 'm_', 'app_'), '', $this->versionSite),
            'folderCss'         => $this->folderCss,
            'folderJs'          => $this->folderJs,
//            'skin'              => $this->skin,
            'callJsLoadPage'    => $this->callJsLoadPage,
            'openMatchId'       => $openMatchId,
            'page'              => $controllerPage,
            'seasonUrlName'     => $seasonUrlName,
            'categoryUrlName'   => $categoryUrlName,
            'fileSection'       => $fileSection,
            'tabLivescore'      => $tabLivescore,
            'shareFb'           => $shareFb
        ));

        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
//            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );
        return $html;
        return preg_replace($search, $replace, $html);
    }

    public function getRespondeCode()
    {
        return $this->respondeCode;
    }

    /**
     * Metodo che avvia il caricamento delle dipendenze javascript della sezione head e body
     */
    public function addDependencies($fileStructure)
    {
        $this->container->get('twig')->addGlobal('dependenciesCSSHead', $this->dependencyManager->getCSSHead());
        $this->container->get('twig')->addGlobal('dependenciesJSHead', $this->dependencyManager->getJSHead());

        if (!$this->compactSite || (!empty($this->isIeVersion) && $this->isIeVersion < 10 )) {
            $this->container->get('twig')->addGlobal('dependenciesCSSBody', $this->dependencyManager->getCSSBody());
            $this->container->get('twig')->addGlobal('dependenciesJSBody', $this->dependencyManager->getJSBody());
            return;
        }
        $fileMinifiedJs = 'js_' . str_replace('.xml', '.js', trim($fileStructure, '/'));
        $fileMinifiedCss = 'css_' . str_replace('.xml', '.css', trim($fileStructure, '/'));

        if (file_exists($this->folderJsMin . ltrim($fileMinifiedJs)) && file_exists($this->folderJsMin . ltrim($fileMinifiedCss))) {
            $filesJS = array($this->folderJsMin . ltrim($fileMinifiedJs) => 1);
            $filesCSS = array($this->folderJsMin . ltrim($fileMinifiedCss) => 1);

            $this->container->get('twig')->addGlobal('dependenciesCSSBody', $filesCSS);
            $this->container->get('twig')->addGlobal('dependenciesJSBody', $filesJS);
            return;
        }
        $this->container->get('twig')->addGlobal('dependenciesCSSBody', $this->dependencyManager->getCSSBody());
        $this->container->get('twig')->addGlobal('dependenciesJSBody', $this->dependencyManager->getJSBody());
    }

    /**
     * Metodo che setta il nome del xml da leggere
     * @param string $xml
     */
    public function setXml($config, $xml)
    {
        $callPhp = str_replace('.xml', '_callPhp.xml', $xml);

        //Se eseiste un file di sequenza delle chiamate php specifico per il tpl chiama la loadCallPhp per fare le chiamare prima di creare i tpl
        if (file_exists($config . $callPhp)) {
            $this->onCallPhp = true;
            $this->loadCallPhp($config . $callPhp);
        }
        $this->xml = simplexml_load_file($config . $xml);
    }

    /**
     * Metodo che cicla il file xml delle chiamare php e le esegue
     * @param type $xml
     */
    private function loadCallPhp($xml)
    {
        $xmlPhp = simplexml_load_file($xml);
        foreach ($xmlPhp->tpl as $node) {
            list( $name, $id ) = explode("|", $node['name']);
            $this->getFunctionForThisTpl($name);
        }
    }

    /**
     * Metodo che setta le varibili di controllo se l'utente è loggato ono
     * @param boolean $controlSession ( Determina se devono essere controlste le sessioni dell'utente o no )
     * @param boolean $sessionActive  ( Determina se l'utente è loggato oppure no )
     */
    public function setSessionActive($controlSession, $sessionActive)
    {
        $this->controlSession = $controlSession;
        $this->sessionActive = $sessionActive;
    }

    /**
     * Metodo che setta l'array dei template sostitutivi per i tpl che richiedono sessione non attiva
     * @param array $array ( ES: 'login' => 'personalProfile' se l'utente è loggato il tpl login sarà sostituito con il personalProfile )
     */
    public function setViewTplSessionActive($array = array())
    {
        $this->tplToSessionActive = $array;
    }

    /**
     * Metodo che setta le varibili di controllo sull'utente se è attivo o no
     * @param boolean $controlUserIsActive ( Determina se deve essere controlsto se l'utente è in stato attivo o no )
     * @param boolean $userIsActive  ( Determina se l'utente è in stato attivo )
     */
    public function setUserIsActive($controlUserIsActive, $userIsActive)
    {
        $this->controlUserIsActive = $controlUserIsActive;
        $this->userIsActive = $userIsActive;
    }

    /**
     * Metodo che setta l'array dei template sostitutivi per i tpl che richiedono che l'utente sia stato attivato
     */
    public function disableTplUserIsNotActive($array = array())
    {
        $this->tplToUserIsActive = $array;
    }

    /**
     * Metodo che legge l'xml e fa il fetch e l'assign di tutti i tpl
     */
    public function initTemplate()
    {
        $this->createTemplate($this->xml);
        if (!empty($this->arrayFetch)) {
            $this->arrayFetch = array_reverse($this->arrayFetch);
            foreach ($this->arrayFetch as $key => $myfetch) {
                $this->concatenate($this->arrayFetch, $key);
            }
        }
    }

    /**
     * Metodo che cicla l'xml e fa il fetch dei figli unici e gli altri li mette in un array
     * @param obj $xml
     */
    function createTemplate($xml)
    {
        $this->container->get('twig')->addGlobal('dependenciesCSSHead', '');
        $this->container->get('twig')->addGlobal('dependenciesCSSBody', '');
        $this->container->get('twig')->addGlobal('dependenciesJSHead', '');
        $this->container->get('twig')->addGlobal('dependenciesJSBody', '');
        $this->container->get('twig')->addGlobal("fetch_index", '');

        $x = 0;
        foreach ($xml->tpl as $node) {
            $name = explode("|", $node['name']->__toString()) ;
            $root = explode("|", $node['padre']->__toString());
            $cores = explode(" ", $node['cores']->__toString());

            $ajaxCore = $node['ajax'];
            $ajaxCore = $ajaxCore == 'null' ? 0 : $ajaxCore;

            $categoryNews = $node['categoryNews'];
            $categoryNews = $categoryNews == 'null' ? 0 : $categoryNews;

            $subcategory = $node['subcategory'];
            $subcategory = $subcategory == 'null' ? 0 : $subcategory;

            $typology = $node['typology'];
            $typology = $typology == 'null' ? 0 : $typology;

            $limitNews = $node['limitNews'];
            $limitNews = $limitNews == 'null' ? 0 : $limitNews;

            $varAttrAjax = (string)$node['varAttrAjax'];

            $affiliation = $node['affiliation'];
            $affiliation = $affiliation == 'null' ? 0 : (int)$affiliation;

            $trademark = $node['trademark'];
            $trademark = $trademark == 'null' ? 0 : (int)$trademark  ;

            //inizializza tutti i fetch delle varibili di ogni tpl per evitare notice
            $this->container->get('twig')->addGlobal("fetch_" . $name[0], '');

            if (!empty($node['assign'])) {
                $this->container->get('twig')->addGlobal("fetch_" . $node['assign'], '');
            }

            //Se il tpl da caricare necessita che l'utente sia loggato blocca l'inclusione, e se è settato un tpl di copertura lo carica
            if ($this->controlSession) {
                if (!$this->sessionActive && key_exists($name[0], $this->tplToSessionActive)) {
                    $newLoadTpl = $this->tplToSessionActive[$name[0]];
                    $name[0] = $newLoadTpl;
                    if (empty($name[0])) {
                        continue;
                    }
                }
            }

            //Se il tpl da caricare necessita che l'utente sia loggato blocca l'inclusione, e se è settato un tpl di copertura lo carica
            if ($this->controlUserIsActive) {
                if (!$this->userIsActive && key_exists($name[0], $this->tplToUserIsActive)) {
                    $newLoadTpl = $this->tplToUserIsActive[$name[0]];
                    $name[0] = $newLoadTpl;
                    if (empty($name[0])) {
                        continue;
                    }
                }
            }

            //Se è definita una variabile specifica sulla quale assegnare il fetch la setta nella specifica in caso contrario a quella del padre
            $varFetch = !empty($node['assign']) ? $node['assign'] : $root[0];

            if ($this->controls($xml, $node['name'], $root[0]) && count($node) == 0) {
                $value = $this->getFunctionForThisTpl($name[0], $cores, $ajaxCore, $categoryNews, $subcategory, $typology, $limitNews);
                $this->container->get('twig')->addGlobal("fetch_" . $varFetch, $value);
                //$this->arrayBox["fetch_".$varFetch] = $value;
            } else {
                if (!empty($name[0])) {
                    $this->arrayFetch["fetch_" . $varFetch][$x]['value'] = $name[0];
                    $this->arrayFetch["fetch_" . $varFetch][$x]['cores'] = $cores;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['ajax'] = $ajaxCore;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['categoryNews'] = $categoryNews;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['subcategory'] = $subcategory;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['typology'] = $typology;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['limitNews'] = $limitNews;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['varAttrAjax'] = $varAttrAjax;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['affiliation'] = $affiliation;
                    $this->arrayFetch["fetch_" . $varFetch][$x]['trademark'] = $trademark;
                }
            }
            $x++;
            $this->createTemplate($node);
        }
    }

    /**
     * Metodo che verifica se il padre del box seguente avra altri figli, se si torna False e la funzione
     * chiamante non farà il fetch ma metterà il nome del tpl in un array
     * @param object $xml
     * @param string $tpl
     * @param string $fetchVar
     * @return boolean
     */
    function controls($xml, $tpl, $fetchVar)
    {
        $count = 0;
        foreach ($xml->tpl as $node) {
            $nome = explode("|", $node['name']->__toString());
            $root = explode("|", $node['padre']->__toString());
            if ($node['name'] != $tpl) {
                if ($root[0] == $fetchVar) {
                    $count++;
                }
            }
            $this->controls($node, $tpl, $fetchVar);
        }
        return ( $count == 0 ? true : false );
    }

    /**
     * Metodo che concatenate i fetch dei box figli che andranno inclusi nel box padre
     * @param array $arr
     * @param string $fetchVar
     */
    function concatenate($arr, $fetchVar)
    {
        $value = '';
        foreach ($arr[$fetchVar] as $myBox) {
            $value .= $this->getFunctionForThisTpl(
                $myBox['value'],
                $myBox['cores'],
                $myBox['ajax'],
                $myBox['categoryNews'],
                $myBox['subcategory'],
                $myBox['typology'],
                $myBox['limitNews'],
                $myBox['varAttrAjax'],
                $myBox['affiliation'],
                $myBox['trademark']
            );
        }
        $this->container->get('twig')->addGlobal($fetchVar, $value);
    }

    /**
     * Metodo che lancia le funzioni necessarie per la costruzione del tpl
     * @param string $myTpl
     */
    public function getFunctionForThisTpl($template, $cores = null, $ajaxCore = 0, $categoryNews = 0, $subcategory = 0, $typology = 0, $limitNews = 0, $varAttrAjax = false, $affiliation = false, $trademark = false)
    {
        $this->dependencyManager->addTplDependencies($template);
        $categoryNews = $categoryNews != 'allnews' ? (int)$categoryNews : $categoryNews;
        $subcategory = $subcategory != 'allnews' ? (int)$subcategory : $subcategory;
        $typology = $typology != 'allnews' ? (int)$typology : $typology;

        //Se il tpl inizia con le seguenti stringe non instanzia la classe
        if (strpos(strtolower($template), "tructure_") || strpos(strtolower($template), "idget_") || strpos(strtolower($template), "anner_")) {
            $data = array();

            if (( empty($ajaxCore) || $ajaxCore == 0 ) && !empty($cores)) {
                foreach ($cores as $core) {
                    if (!empty($core) && $core != null && $core != 'null') {
                        $widget = $this->container->get('app.' . lcfirst($core));

                        $options = new \stdClass();
                        $options->categoryNews = $categoryNews != 'allnews' ? (int)$categoryNews : $categoryNews;
                        $options->subcategory = $subcategory != 'allnews' ? (int)$subcategory : $subcategory;
                        $options->typology = $typology != 'allnews' ? (int)$typology : $typology;
                        $options->limitNews = (int)$limitNews;
                        $options->varAttrAjax   = $varAttrAjax;
                        $options->varAttrAjax   = $varAttrAjax;
                        $options->affiliation   = $affiliation;
                        $options->trademark     = $trademark;

                        $data_new = $widget->processData($options);
                        $data = array_merge($data, $data_new);


                        //se il core ritorna un elemento di errore cambia l'header della response
                        if (!empty($data_new['errorPage']) && !empty($this->responsePage)) {
                            switch ($data_new['errorPage']) {
                                case 404:
                                    $this->responsePage->setStatusCode(404);
                                    $this->container->get('twig')->addGlobal('error404Page', true);
                                    return $this->container->get('twig')->render($this->versionSite . "/widget_404NotFound" . $this->extension, $data);
                                break;
                                case 'notResultSearchFoundPage':
                                    $widget = $this->container->get('app.CoreBestsellerModels');
                                    $data = $widget->processData($options);

                                    $this->responsePage->setStatusCode(200);
                                    $this->container->get('twig')->addGlobal('error404Page', true);
                                    return $this->container->get('twig')->render($this->versionSite . "/widget_NotResultSearchFound" . $this->extension, $data);
                                break;
                            }
                        }
                    }
                }
            } else {
                if (in_array('CoreReplaceWidgetAjax', $cores)) {
                    $coreAjaxReplaceDataAttr = $this->container->get('app.CoreReplaceWidgetAjax');
                    $options = new \stdClass();
                    $options->varAttrAjax   = $varAttrAjax;
                    $data = $coreAjaxReplaceDataAttr->processData($options);
                    if (($key = array_search('CoreReplaceWidgetAjax', $cores)) !== false) {
                        unset($cores[$key]);
                    }
                }
                $this->callJsLoadPage[] = 'modules.add( "' . $template . '-_-' . rand(0, 9) . '","' . str_replace(array('Ajax_', '_TO'), '', implode(' ', $cores)) . '", ' . $ajaxCore . ', ' . (int)$limitNews . ', "' . $categoryNews . '", "' . $varAttrAjax . '", "' . $affiliation . '", "' . $trademark . '" )';
            }
            $resp = $this->container->get('twig')->render($this->versionSite . "/{$template}" . $this->extension, $data);
            return $resp;
        }
        return '';
    }

    /**
     * Metodo che effettua il rende di tutte le pagine se sta in cache la oagibna la prende da li 
     */
    public function getPageFromHttpCache( Request $request, string $section = 'homepage.xml', bool $enabledCache = true, $params = null) : Response
    {
        $this->setParameters();
        $eTag = md5($request->server->get('REQUEST_URI')  . '?&mobile=' . $this->globalConfigManager->isMobile() . '&isamp=' . $this->globalConfigManager->getAmpActive() . '&v=1 ');

        $params = !empty($params)  ? $params : new \stdClass();

        $extraConfig = $this->globalConfigManager->getExtraConfig();
        //recupera la risposta e la setta in cache

//        $response = new Response( $this->init( "homepage.xml", $request, $params ) );
//        return $response;
//
//        if( !empty( $enabledCache ) && !empty( $extraConfig['cacheEnabled']->getValue() ) && $extraConfig['cacheEnabled']->getValue() == 'true' ) {
//            $response = new Response();
//            $response->setETag($eTag);
//            $response->setPublic();
//            $params->responsePage = $response;
//
//            //se esiste la copia cachata la restituisce
//            if ($response->isNotModified($request)) {
//                $response->setNotModified();
//                return $response;
//            } else {
//                $response = new Response();
//                $params->responsePage = $response;
//                $response->setContent( $this->init( $section, $request, $params ) );
//
//                $response->setETag( $eTag );
//                $response->setPublic();
//                $response->setMaxAge( $this->getParameter('app.ttlHomaPageCache') );
//                $response->setSharedMaxAge( $this->getParameter('app.ttlHomaPageCache') );
//                $response->headers->addCacheControlDirective('must-revalidate', true);
//                return $response;
//            }
//        }

        $response = new Response();
        $params->responsePage = $response;
        $response->setContent($this->init($section, $request, $params));
        return $response;
    }


    /**
     * Metodo che controlla se il template è in cache in caso lo ritorna
     * @return \App\Controller\Response
     */
    public function getCacheTemplate(Request $request, $date, $filename, $params)
    {
        //Se la data e precedente a quella attuale metto in cache le response
        if (!empty($date) && date_format(date_create($date), 'Y-m-d') < date('Y-m-d')) {
            $eTag = md5($request->server->get('REQUEST_URI')) . '?v=' . $this->getParameter('app.eTagVersion');
            $d = new \DateTime();
            $date = $d->createFromFormat('Y-m-d', $date);

            $response = new Response();
            $response->setETag($eTag);
            $response->setLastModified($date);
            $response->setPublic(); // make sure the response is public/cacheable
            //se esiste la copia cachata la restituisce
            if ($response->isNotModified($request)) {
                return $response;
            } else {
                //recupera la risposta e la setta in cache

                $html = $this->init($filename, $request, $params);

                $response = new Response($html);
                $response->setETag($eTag);
                $response->setPublic();
                return $response;
            }
        } else {
            return false;
        }
    }

    /**
     * Metodo che instanza i cores del widget richiesto e poi fa il render del template
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function renderSingleTemplate(Request $request)
    {
        $this->setParameters();
        $template = explode('-_-', $request->query->get('widget'))[0];
        $cores = explode(' ', $request->query->get('cores'));

        $options                = new \stdClass();
        $options->categoryNews  = $request->query->get('category');
        $options->limitNews     = (int)$request->query->get('limit');
        $options->varAttrAjax   = $request->query->get('varAttrAjax');
        $options->trademark   = $request->query->get('trademark');
        $options->affiliation   = $request->query->get('affiliation');

        $data = array();
        foreach ($cores as $core) {
            if (!empty($core) && $core != null && $core != 'null') {
                $widget = $this->container->get('app.' . $core);
                $data_new = $widget->processData($options);
                $data = array_merge($data, $data_new);
            }
        }
        return $this->compressHtml($this->container->get('twig')->render($this->versionSite . "/{$template}" . $this->extension, $data));
    }

    /**
     * Metodo che instanza i cores del widget richiesto e poi fa il render del template
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function getDataCoreWidget(Request $request)
    {
        $this->setPatameters();
        $cores = explode(' ', $request->query->get('cores'));

        $data = array();
        foreach ($cores as $core) {
            if (!empty($core) && $core != null && $core != 'null') {
                $widget = $this->container->get('app.' . $core);
                $data_new = $widget->getDataToAjax();
                $data = array_merge($data, $data_new);
            }
        }
        return $data;
    }

    /* SEZIONE DEDICATA ALLA GESTIONE DELLA CACHE DEI WIDGET DEL SITO */

    /**
     * Determina se mettere la response di un template in cache
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function checkSetResponseCache(Request $request)
    {

        //Se la data e precedente a quella attuale, oppure lo status del match è end metto in cache le response
        //oppure se lo stato del match è end
        //oppure se è uno dei widget da temporizzare a tempo
        //oppure se la rotta aperta è una di quella consentite per essere messa in cache
        if (
            !empty($request->query->get('date')) && ( date_format(date_create($request->query->get('date')), 'Y-m-d') < date('Y-m-d') )
            || (!empty($request->query->get('status')) && $request->query->get('status') == 'end' )
            || $this->container->getEnabledWidgetCached($request->query->get('widget'))
            || $this->container->getEnabledRouteCached($request)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determina le rotte che possono essere messi in cache
     * @param type request
     * @return boolean
     */
    public function getEnabledRouteCached($request)
    {
        $matchUri = explode('?', $request->server->get('REQUEST_URI'));
        $this->route = $this->container->get('router')->match($matchUri[0]);
        $urlRoute = !empty($this->route['_route']) ? $this->route['_route'] : '';

        $routeEnabled = array( 'teamDetail' );
        if (in_array($urlRoute, $routeEnabled)) {
            return true;
        }
        return false;
    }

    /**
     * Determina il ttl da settare per la cache di un intera pagina gestione per route
     * @param type $request
     * @return int
     */
    public function getRouteTTLCacheResponse($request)
    {
        $matchUri = explode('?', $request->server->get('REQUEST_URI'));
        $this->route = $this->container->get('router')->match($matchUri[0]);
        $urlRoute = !empty($this->route['_route']) ? $this->route['_route'] : '';

        return 3600;
    }

    /**
     * Determina i widget che possono essere messi in cache anche se la data e quella odierna
     * @param type $widget
     * @return boolean
     */
    public function getEnabledWidgetCached($widget)
    {
        $widgetEnabled = array('widget_TabRanking', 'widget_HeadToHeadTeams', 'widget_OddsMatch', 'widget_FormationsFieldAndTable');
        if (in_array($widget, $widgetEnabled)) {
            return true;
        }
        return false;
    }
    /**
     * Determina il ttl da settare alla cache dei widget
     * @param type $widget
     * @return int
     */
    public function getTTLCacheResponse($widget)
    {
        $widgetDefaultTTL = array('widget_TabRanking', 'widget_HeadToHeadTeams', 'widget_OddsMatch', 'widget_FormationsFieldAndTable');
        if (in_array($widget, $widgetDefaultTTL)) {
            return $this->getParameter('s_memcached_expire_default');
        }
        $datWidget = array('widget_TabMenuMatch');
        if (in_array($widget, $datWidget)) {
            return $this->getParameter('s_memcached_expire_data_widget');
        }
        return $this->getParameter('s_memcached_expire_widget');
    }

    /**
     * Determina se nella generazione dell'ETag per salvare in cache la pagina richiesta deve essere presente il nome della versione
     * del sito, per salvare le versioni differenti di un template per la versione desktop e per la versione mobile
     * @param string  $widget
     * @param string $uri
     * @return string
     */
    public function getETagRequest($widget, $uri)
    {
        $versionSite = '';
        if ($widget == 'widget_LiveScoreHome') {
            $versionSite = $this->container->getVersionSite();
        }
        $eTag = md5($versionSite . $uri) . '?v=' . $this->getParameter('app.eTagVersion');
        return $eTag;
    }


    /**
     * Determina se la richiesta ajax per l'admin sia valida
     * @param type $request
     * @return type
     */
    public function checkIsValidRequestAdmin($request, $checkAjax = false)
    {
        $um = $this->container->get('app.userManager');
//        if ( empty( $um->isLogged() ) || empty( $_SERVER["HTTP_REFERER"] ) || stristr($_SERVER["HTTP_REFERER"], $_SERVER['SERVER_NAME']) == false ) {
        if (empty($um->isLogged())) {
            return false;
        }

        $isAjax = $request->isXmlHttpRequest();
        if (!empty($checkAjax) && empty($isAjax)) {
            return false;
        }

        return true;
    }

    /**
     * Metodo che comprime l'html
     * @param type $html
     * @return type
     */
    public function compressHtml($html)
    {
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
}
