<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoItemType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para GrupoItem.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoItemController extends FormListController
{

    /**
     * @Route("/api/fin/grupoItem/gerarNovo/{pai}", methods={"HEAD","GET"}, name="api_fin_grupoItem_gerarNovo", requirements={"pai"="\d+"})
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function gerarNovo(Request $request, Grupo $pai, GrupoEntityHandler $grupoEntityHandler): JsonResponse
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