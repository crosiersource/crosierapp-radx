<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoItemType;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CarteiraEntityHandler;
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
     * @Route("/api/fin/carteira/consolidar/{carteira}/{dtConsolidado}", methods={"HEAD","GET"}, name="api_fin_carteira_consolidar")
     * @ParamConverter("data", options={"format": "Y-m-d"})
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function consolidar(Carteira $carteira, \DateTime $dtConsolidado, CarteiraEntityHandler $carteiraEntityHandler): JsonResponse
    {
        try {
            $carteira->dtConsolidado = $dtConsolidado;
            $carteiraEntityHandler->save($carteira);
            return CrosierApiResponse::success();
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


}