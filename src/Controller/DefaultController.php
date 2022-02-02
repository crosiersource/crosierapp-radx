<?php

namespace App\Controller;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

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
        $cache = new FilesystemAdapter($_SERVER['CROSIERAPPRADX_UUID'] . '.findValuesTagsDin', 3600, $_SERVER['CROSIER_SESSIONS_FOLDER']);
        $radxUrl = $cache->get('v_vuePage_serverParams', function (ItemInterface $item) {
            $rURL = $this->getDoctrine()->getConnection()->fetchAssociative('SELECT valor FROM cfg_app_config WHERE app_uuid = :appUUID AND chave = :chave', [
                'appUUID' => $_SERVER['CROSIERAPPRADX_UUID'],
                'chave' => 'URL_' . $_SERVER['CROSIER_ENV']
            ]);
            return $rURL['valor'];
        });

        $params['serverParams'] = json_encode([
            'radxURL' => $radxUrl,
        ]);

        $params['jsEntry'] = $vuePage;
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