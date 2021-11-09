<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\CentroCustoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CentroCustoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para CentroCusto.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CentroCustoController extends FormListController
{

    /**
     * @Route("/fin/centroCusto/form", name="fin_centroCusto_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/CentroCusto/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fin/centroCusto/list", name="fin_centroCusto_list")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/CentroCusto/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }


}