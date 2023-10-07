<?php

namespace App\Service\WidgetService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\FormService\FormManager;

class CoreAdminMenu extends AbstractController
{
    public function __construct(private WidgetManager $wm)
    {
    }

    public function processData($options = null)
    {
        if (!$this->wm->getPermissionCore()) {
            return [];
        }

        return [];
    }
}
