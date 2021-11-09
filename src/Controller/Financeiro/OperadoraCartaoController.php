<?php

namespace App\Controller\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperadoraCartaoController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class OperadoraCartaoController extends FormListController
{

    /**
     * @Route("/fin/operadoraCartao/form", name="fin_operadoraCartao_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/OperadoraCartao/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fin/operadoraCartao/list", name="fin_operadoraCartao_list")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/OperadoraCartao/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }


}