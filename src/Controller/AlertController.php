<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlertController extends TemplateController
{
    /**
     * @Route( "/alert", name="alert" )
     */
    public function alertction(Request $request)
    {
        return $this->getPageFromHttpCache($request, 'alert.xml');
    }
}
