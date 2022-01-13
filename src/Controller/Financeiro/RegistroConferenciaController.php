<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\RegistroConferenciaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\RegistroConferenciaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegistroConferencia;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class RegistroConferenciaController extends FormListController
{

    /**
     *
     * @Route("/api/fin/registroConferencia/gerarProximo/{id}", name="api_fin_registroConferencia_gerarProximo", requirements={"id"="\d+"})
     * @param RegistroConferencia $registroConferencia
     * @return JsonResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function gerarProximo(RegistroConferenciaBusiness $business, RegistroConferencia $registroConferencia): JsonResponse
    {
        try {
            $business->gerarProximo($registroConferencia);
            return CrosierApiResponse::success();
        } catch (\Exception $e) {
            return CrosierApiResponse::error($e, true);
        }
    }


}