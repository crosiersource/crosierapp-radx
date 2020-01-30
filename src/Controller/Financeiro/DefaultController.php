<?php

namespace App\Controller\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 * @author Carlos Eduardo Pauluk
 */
class DefaultController extends BaseController
{

    /**
     *
     * @Route("/fin/", name="fin_root")
     */
    public function index()
    {
        return $this->doRender('Financeiro/dashboard.html.twig');
    }

}