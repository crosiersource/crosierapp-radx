<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoItemType;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @author Carlos Eduardo Pauluk
 */
class CarteiraController extends BaseController
{

    /**
     * @Route("/api/fin/carteira/consolidar/{carteira}/{data}", methods={"HEAD","GET"}, name="api_fin_carteira_consolidar")
     * @ParamConverter("data", options={"format": "Y-m-d"})
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function consolidar(Grupo $pai, Carteira $carteira, \DateTime $dt): JsonResponse
    {
        try {
            $prox = filter_var($request->get('prox'), FILTER_VALIDATE_BOOLEAN);
            $grupoItemGerado = $grupoEntityHandler->gerarNovo($pai, $prox);
            return CrosierApiResponse::success(['id' => $grupoItemGerado->getId()]);
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


}