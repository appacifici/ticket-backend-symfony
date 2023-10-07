<?php

declare(strict_types=1);

namespace App\Service\DependencyService;

use App\Service\DependencyService\DependencyManager;

/**
 * Description of DependenciesModules
 * @author alessandro
 */
class DependencyManagerTemplate extends DependencyManager
{
    public array $dependeciesJSBody;

    public function __construct($paramaters)
    {
        parent::__construct($paramaters);
    }

    /**
     * Gestisce le inclusioni globali di css e js
     * @return void
     */
    public function globalDependencies(): void
    {
        parent::globalDependencies();
        $this->addDependencyJSHead('https://code.jquery.com/jquery-2.2.4.min.js');

        // $this->addDependencyCSSHead( $this->parameters->commonPath.'library/bootstrap/css/bootstrap.min.css' );
        // $this->addDependencyCSSHead( $this->parameters->commonPath.'plugins/forms_elements_bootstrap-datepicker/css/bootstrap-datepicker.css' );
        // $this->addDependencyCSSHead( $this->parameters->commonPath.'plugins/notifications_notyfy/css/jquery.notyfy.css' );

        // $this->addDependencyJSHead( $this->parameters->commonPath.'library/jquery/jquery.'.$this->jqueryVersion.'.js' );
        // if( $this->forceVersion != 'app_direttagoal' && !$this->isMobileOrApp ) {
        //     $this->addDependencyJSHead( $this->parameters->commonPath.'library/jquery-ui/js/jquery-ui.min.js' );
        //     $this->addDependencyJSHead( $this->parameters->commonPath.'library/jquery/jquery-migrate.min.js' );
        // }
        // $this->addDependencyJSHead( $this->parameters->commonPath.'library/modernizr/modernizr.js' );
        // $this->addDependencyJSHead( $this->parameters->commonPath.'plugins/forms_elements_bootstrap-datepicker/js/bootstrap-datepicker.js' );
        // $this->addDependencyJSHead( $this->parameters->commonPath.'components/forms_elements_bootstrap-datepicker/bootstrap-datepicker.init.js' );

        // $this->addDependencyJSBody( $this->parameters->commonPath.'library/bootstrap/js/bootstrap.min.js' );

        // $this->addDependencyJSBody( $this->parameters->extensionsJsPath.'jqueryExtends.js' );
        // $this->addDependencyJSBody( $this->parameters->extensionsJsPath.'main.js' );
        // $this->addDependencyJSBody( $this->parameters->commonPath.'components/ui_modals/modals.init.js' );
        // $this->addDependencyJSBody( $this->parameters->commonPath.'plugins/notifications_notyfy/js/jquery.notyfy.js' );
        // $this->addDependencyJSBody( $this->parameters->commonPath.'components/admin_notifications_notyfy/notyfy.init.js' );

        // $this->addDependencyJSBody( $this->parameters->extensionsJsPath.'modules.init.js' );
    }

    /**
     * Recupera l'elenco di come devono essere inclusi i file dalla classe padre e ne sovrascrive i valori necessari
     * @return void
     */
    public function loaderFiles(): void
    {
        parent::loaderFiles();
        $this->dependeciesJSBody[$this->parameters->extensionsJsPath . 'users.js']                            = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsPath . 'templateManager.js']                  = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsPath . 'main.js']                             = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsAdminPath . '/mainAdmin.js']                  = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsAdminPath . '/widgetLogin.js']                = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsAdminPath . '/widgetManagerInlineForm.js']    = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsAdminPath . '/widgetManagerMenus.js']         = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsAdminPath . '/widgetExtraConfigs.js']         = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsPath . 'sapUtility.js']                 = false;
        $this->dependeciesJSBody[$this->parameters->extensionsJsPath . 'widget/template/widgetBusinessPartner.js']      = false;
    }

    /**
     * Funzione chiamata dal template manager per recuperare le dipendenze dei file twig inlusi nella sezione
     * @param string $widget
     * @return void
     */
    public function getDependency($widget): void
    {
        $this->addDependencyJSBody($this->parameters->extensionsJsPath . '/bootstrap.min.js');
        $this->addDependencyJSBody($this->parameters->extensionsJsPath . '/bootbox.min.js');
        $this->addDependencyJSBody($this->parameters->extensionsJsPath . 'modals.init.js');
        $this->addDependencyJSBody($this->parameters->extensionsJsPath . 'boxOverlay.js');

        switch ($widget) {
            case 'widget_Alert':
                $this->addDependencyJSBody($this->parameters->extensionsJsPath . 'widget/template/widgetAlert.js');
                break;
            case 'widget_ExtraConfigs':
                break;
        }
    }
}
