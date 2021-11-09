<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\RegraImportacaoLinhaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegraImportacaoLinha;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\RegraImportacaoLinhaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegraImportacaoLinhaController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegraImportacaoLinhaController extends FormListController
{

    /**
     * @Route("/fin/regraImportacaoLinha/form", name="fin_regraImportacaoLinha_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/RegraImportacaoLinha/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fin/regraImportacaoLinha/list", name="fin_regraImportacaoLinha_list")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/RegraImportacaoLinha/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }


}