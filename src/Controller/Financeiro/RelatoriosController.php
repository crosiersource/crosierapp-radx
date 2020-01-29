<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RelatoriosController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RelatoriosController extends BaseController
{

    /**
     *
     * @Route("/relatorios/list", name="relatorios_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(Request $request)
    {
        return new Response();
    }


}