<?php

namespace App\Controller\Estoque;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package App\Controller\Estoque
 * @author Carlos Eduardo Pauluk
 */
class GrupoController extends FormListController
{
    /**
     *
     * @Route("/est/grupo/select2json/{depto}", name="est_grupo_select2json", requirements={"id"="\d+"})
     * @param Depto $depto
     * @return JsonResponse
     *
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function grupoSelect2json(Depto $depto): JsonResponse
    {
        $grupos = $depto->grupos->toArray();

        $select2js = Select2JsUtils::toSelect2DataFn($grupos, function ($e) {
            /** @var Grupo $e */
            return $e->getDescricaoMontada();
        });
        return new JsonResponse(
            ['results' => $select2js]
        );
    }


}