<?php

declare(strict_types=1);

namespace App\Service\WidgetService;

use App\Entity\Alert;
use App\Service\UtilityService\PaginationUtility;

class CoreAlert
{
    public function __construct(
        private WidgetManager $wm,
        private PaginationUtility $paginationUtility
    ) {
    }
    public function processData($options = false): array
    {
        //controllo se l'utente abbia i permessi di lettura
        if (!$this->wm->getPermissionCore('alert', 'read')) {
            header('Location: /');
            exit;
        }

        $this->paginationUtility->getParamsPage(50);

        $count          = $this->wm->doctrine->getRepository(Alert::class)->findByDate(Alert::COUNT_QUERY);
        $this->paginationUtility->init($count[0]['tot'], 10, false, false, true);

        $alerts         = $this->wm->doctrine->getRepository(Alert::class)->findByDate(Alert::RESULT_QUERY, $this->paginationUtility->getLimit());

        return array(
            'alerts'  => $alerts,
            'htmlPag' => $this->paginationUtility->makeList()
        );
    }
}
