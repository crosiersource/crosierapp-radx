<?php

namespace App\Controller\Estoque;


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
     * @Route("/est/", name="est_root")
     */
    public function index()
    {
        return $this->doRender('Estoque/dashboard.html.twig');
    }

}