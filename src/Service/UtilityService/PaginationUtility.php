<?php

namespace App\Service\UtilityService;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\WidgetService\WidgetManager;

use Twig\Environment as Environment;

class PaginationUtility {

    private $currentPage;
    private $totalPages;
    private $linkStructure;
    private $containerId;
    private $noLink;
    private $step;    
    private $request;    
    public $msgAllForward;
    public $msgAllRewind;
    public $msgForward;
    public $msgRewind;
    public $msgPagina;
    private $gcm;
    private $index;
    private $skip;
    private $totArticleListCategory;
    private $limit;
    private $absolute;
    private $totalArticles;
    private $numLinks;
    private $notLink;

    /**
     * 
     * @param Container $container
     * @param RequestStack $requestStack
     */
    public function __construct(
        private Container $container, 
        private RequestStack $requestStack, 
        private WidgetManager $wm, 
        private Environment $twig
    ) {
        $this->twig             = $twig;
        $this->container        = $container;
        $this->requestStack     = $requestStack;
        $this->request          = $this->requestStack->getCurrentRequest();
        $this->gcm              = $this->wm->globalConfigManager;
    }

    /**
     * Recupera e valorizza i parametri presi dalla url corrente
     */
    public function getParamsPage($totArticleListCategory) {
        $this->request->get('page');
        $this->index = $this->request->get('page');
        $this->step = 0;

        $this->skip = isset($this->index) ? abs($this->index) : 0;
        $this->step = !empty($this->skip) && $this->skip > 0 ? ( $this->skip - 1 ) : $this->skip;

        $this->totArticleListCategory = $totArticleListCategory;
        $this->limit = ( $this->step * $this->totArticleListCategory ) . "," . $this->totArticleListCategory;
        
    } 

    /**
     * Recupera il limite d impostare nella query successiva da eseguire
     * @return type
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * Determina il link da mettere alle paginazioni
     * @return type
     */
    public function getLink($url = false) {
        $link = ( $url ) ? $url : $this->request->server->get('REQUEST_URI');
        $link = preg_replace('/\?page=[0-9]+/', '', $link);
        $link = preg_replace('/\&page=[0-9]+/', '', $link);


        $queryString = preg_replace('/page=[0-9]+/', '', $this->request->server->get('QUERY_STRING'));
        $qoper = empty($queryString) ? '?' : '&';
        if ($url)
            return $url . 'page=%i';

        return $link = $this->wm->globalConfigManager->getBaseAbsoluteUrl().$link . $qoper . 'page=%i';
    }

    /**
     * costruttore
     * @param string $linkStruct
     * @param int $tot
     * @param int $cur
     * @param int $step
     */
    public function init($tot, $numLinks = 10, $noLink = false, $url = false, $absolute = false ) {
        $step = $this->step;
        $this->absolute = $absolute;
        $this->linkStructure = $this->getLink($url);
        $this->totalArticles = $tot < 0 ? 0 : $tot;
        $this->step = $step < 1 ? 1 : $step;
        $this->totalPages = $this->lastPage();
        $this->setNextPrevpage();

        $this->numLinks = $numLinks;
        $this->notLink = $noLink;
        $cur = $this->skip;

//	  $cur--;
        if ($cur < 0) {
            $this->currentPage = 0;
        } else if ($cur > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        } else {
            $this->currentPage = $cur;
        }

        $this->currentPage = $this->currentPage > 0 ? $this->currentPage : 1;
        //stringhe

        $this->containerId = 'pagination';
        $this->msgAllForward = 'Salta all\'ultima pagina';
        $this->msgAllRewind = 'Salta alla prima pagina';
        $this->msgForward = 'avanti';
        $this->msgRewind = 'indietro';
        $this->msgPagina = 'Vai a pagina %i';
    }

    public function getPaginationInfiniteScroll() {
        if ($this->totalPages <= 1) {
            return '';
        }
        if ($this->currentPage < $this->totalPages) {
            $retval = '<a href="' . str_replace('%i', $this->skip + 1, $this->linkStructure) . '"  title="' . $this->msgAllForward . '">&raquo;</a>';
        }
        return $retval;
    }

    /**
     * Metodo che crea la paginazione
     * @return string
     */
    public function makeList() {
        if ($this->totalPages <= 1) {
            return '';
        }

        $startPage = ceil($this->currentPage - ($this->numLinks / 2) < 1 ? 1 : $this->currentPage - ($this->numLinks / 2)); //link iniziale
        if ($startPage > 1)
            $startPage++;

        $retval = '<div class="paginationContainer"><ul class="' . $this->containerId . '" id="' . $this->containerId . '">';
        if ($this->notLink) {
            $retval .= '<li id="0" class="nextOnePage active"><a href="#"  title="' . $this->msgAllForward . '">&laquo;</a></li>';
        } else {
            $prevBlockPage = $this->currentPage - 10 > 0 ? $this->currentPage - 10 : 1;
            
            if($this->currentPage > 1  && $this->totalPages > 10 ) {     
                $retval.= '<li class="step1 backOnePage active"><a href="' . str_replace('%i', $prevBlockPage, $this->linkStructure) . '" title="' . $this->msgAllRewind . '">&laquo;</a></li>';                
            } else {
                $retval .= '<li class="step1 backOnePage disabled"><a>&laquo;</a></li>';
            }
        }
        
        if ($this->notLink) {
            $retval .= '<li id="0" class="backOnePage"><a href="#"  title="Vai alla pagina precedente">&lt;</a></li>';
        } else {
            if ($this->currentPage > 1 ) {
                $retval .= '<li class="backOnePage active"><a href="' . str_replace('%i', ($this->currentPage - 1), $this->linkStructure) . '" title="Vai alla pagina precedente">Precedente</a></li>';
            } else {
                $retval .= '<li class="backOnePage disabled"><a>&lt;</a></li>';
            }
        }
        
        
        if( $this->currentPage >  5  ) {
            $retval .= '<li class="itemLinkPagination"><a href="' . str_replace('%i', 1, $this->linkStructure) . '">1</a></li>';
            $retval .= '<li class="itemLinkPagination disabled">...</li>';
        }
        
        $c = $startPage;
        while ($c < $startPage + $this->numLinks) {
            if ($c > $this->totalPages) {
                break;
            }
            $msg = str_replace('%i', $c, $this->msgPagina);
            $current = $this->currentPage > 0 ? $this->currentPage : 1;

            $class = $c == $current ? 'active itemLinkPagination' : 'itemLinkPagination';
            if ($this->notLink) {
                $retval .= '<li id="' . ($c) . '" class="' . $class . '" ><a href="#" title="' . $msg . '">' . ($c) . '</a></li>';
            } else {
                if ($c == 1) {
                    if ($current == $c)
                        $retval .= '<li id="' . ($c) . '" class="' . $class . '" ><span>' . ($c) . '</span></li>';
                    else
                        $retval .= '<li class="' . $class . '"><a href="' . str_replace(array('?page=%i', '&page=%i'), array('', ''), $this->linkStructure) . '" title="' . $msg . '">' . ($c) . '</a></li>';
                } else {
                    if ($current == $c)
                        $retval .= '<li id="' . ($c) . '" class="' . $class . '" ><span>' . ($c) . '</span></li>';
                    else
                        $retval .= '<li class="' . $class . '"><a href="' . str_replace('%i', $c, $this->linkStructure) . '" title="' . $msg . '">' . ($c) . '</a></li>';
                }
            }
            $c++;
        }
        
        if( $this->currentPage < ( $this->totalPages - 10 ) ) {
            $retval .= '<li class="itemLinkPagination disabled">...</li>';
            $retval .= '<li class="itemLinkPagination disabled">' . $this->totalPages . '</li>';
        }
        
        $nextOnePage = $this->currentPage > 0 ? $this->currentPage : 1;
        $nextOnePage++;

        $nextBlockPage = $this->currentPage + 10 < $this->totalPages ? $this->currentPage + 10 : $this->totalPages;
        if ($this->notLink) {
            $retval .= '<li class="nextOnePage" id="' . $nextBlockPage . '" ><a href="#"  title="' . $this->msgAllForward . '">&raquo;</a></li>';
        } else {
            if ($this->currentPage < $this->totalPages && $this->totalPages > 10 ) {
                $retval .= '<li class="step2 nextOnePage active"><a href="' . str_replace('%i', $nextBlockPage, $this->linkStructure) . '"  title="' . $this->msgAllForward . '">&raquo;</a></li>';
            } else {
                $retval .= '<li class="step2 nextOnePage disabled"><a>&raquo;</a></li>';
            }
        }
        
        if ($this->notLink) {
            $retval .= '<li class="nextOnePage" id="' . ($nextOnePage) . '" ><a href="#"  title="Vai alla pagina successiva">&gt;</a></li>';
        } else {            
            if ($this->currentPage < $this->totalPages) {
                $retval .='<li class="nextOnePage active"><a href="' . str_replace('%i', ($nextOnePage), $this->linkStructure) . '"  title="Vai alla pagina successiva">Successiva</a></li>';
            } else {
                $retval .= '<li class="nextOnePage disabled"><a>&gt;</a></li>';
            }
        }

        $retval .= '</ul></div>';
        return $retval;
    }

    /**
     * Metodo che calcola l'ultima pagina
     * @return int
     */
    public function lastPage() {        
        return ceil($this->totalArticles / $this->totArticleListCategory);
    }

    public function setNextPrevpage() {                
        $link = explode('?', $this->linkStructure);
        
        $urlBasePrevNext = $link[0];
        if (!empty($this->wm->getPage() && $this->wm->getPage() > 1)) {
            $prevPage = (int) $this->wm->getPage() - 1;
            if ($prevPage > 1)
                $this->twig->addGlobal('prevUrl', $urlBasePrevNext . '?page=' . ((int) $this->wm->getPage() - 1));
            else
                $this->twig->addGlobal('prevUrl', $urlBasePrevNext);
        }

        $maxPage = $this->lastPage();
        if (!empty($this->wm->getPage()) && $this->wm->getPage() < $maxPage)
            $this->twig->addGlobal('nextUrl', $urlBasePrevNext . '?page=' . ((int) $this->wm->getPage() + 1));
        
        if(  (int)$this->wm->getPage() > 1 ) {            
            $this->twig->addGlobal( 'canonicalUrl', str_replace( '/amp/', '/', $urlBasePrevNext ). '?page=' .(int) $this->wm->getPage());
            $this->twig->addGlobal( 'robots', 'index,follow' );
        }
    }  
}
