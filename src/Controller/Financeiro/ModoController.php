<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\ModoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\ModoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Modo.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ModoController extends FormListController
{

    /**
     * @Route("/fin/modo/form", name="fin_modo_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Modo/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fin/modo/list", name="fin_modo_list")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Modo/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

}