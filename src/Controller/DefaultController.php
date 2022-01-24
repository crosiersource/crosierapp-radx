<?php

namespace App\Controller;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/", name="radx_root")
     */
    public function index()
    {
        return $this->doRender('dashboard.html.twig');
    }

    /**
     * @Route("/v/{vuePage}", name="v_vuePage", requirements={"vuePage"=".+"})
     */
    public function vuePage($vuePage): Response
    {
        $params = [
            'jsEntry' => $vuePage
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/r/{vueRel}", name="r_vueRel", requirements={"vueRel"=".+"})
     */
    public function vueRel($vueRel): Response
    {
        $params = [
            'jsEntry' => $vueRel
        ];
        return $this->doRender('@CrosierLibBase/vue-app-rel.html.twig', $params);
    }

    /**
     *
     * @Route("/nosec", name="nosec", methods={"GET"})
     * @return Response
     */
    public function nosec(): Response
    {
        return new Response('nosec OK');
    }

}