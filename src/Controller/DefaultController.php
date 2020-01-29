<?php

namespace App\Controller;


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
     * @Route("/", name="index")
     */
    public function index()
    {
        $params['PROGRAM_UUID'] = 'e957c606-7f1a-4856-afde-287c2c6fe3dd';
        return $this->doRender('dashboard.html.twig', $params);
    }

}