<?php

namespace App\Controller\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CaixaOperacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class CaixaOperacaoController extends BaseController
{

    /**
     * @Route("/api/fin/caixaOperacao/status/{caixa}", methods={"HEAD","GET"}, name="api_fin_caixaOperacao_status")
     * @ParamConverter("data", options={"format": "Y-m-d"})
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function statusDoCaixa(Carteira $caixa): JsonResponse
    {
        try {
            if (!$caixa->caixa) {
                throw new ViewException('Carteira não é caixa');
            }
            $repoCaixaOperacao = $this->doctrine->getRepository(CaixaOperacao::class);
            $ultimaOperacao = $repoCaixaOperacao->getUltimaOperacao($caixa);
            $statusDoCaixa = $ultimaOperacao ? $ultimaOperacao->getStatus() : 'FECHADO';
            return CrosierApiResponse::success([
                'status' => $statusDoCaixa,
                'dtUltimaOperacao' => $ultimaOperacao ? $ultimaOperacao->dtOperacao->format('d/m/Y H:i:s') : null,
            ]);
        } catch (\Exception $e) {
            return CrosierApiResponse::viewExceptionError($e);
        }
    }


}