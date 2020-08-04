<?php

namespace App\Controller\Estoque\API;

use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\APIProblem;
use CrosierSource\CrosierLibRadxBundle\Business\Estoque\CalculoPreco;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DiaUtilController
 *
 * @package App\Controller\Base
 * @author Carlos Eduardo Pauluk
 */
class CalculoPrecoAPIController extends AbstractController
{

    private CalculoPreco $calculoPreco;

    /**
     * @required
     * @param CalculoPreco $calculoPreco
     */
    public function setCalculoPreco(CalculoPreco $calculoPreco): void
    {
        $this->calculoPreco = $calculoPreco;
    }


    /**
     *
     * @Route("/api/est/calcularPreco", name="api_est_calcularPreco")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function calcularPreco(Request $request): ?JsonResponse
    {
        try {
            $precoCusto = (float)$request->get('precoCusto');
            $margem = (float)$request->get('margem');
            $prazo = (int)$request->get('prazo');
            $custoOperacional = (float)$request->get('custoOperacional'); // em porcentagem (ex.: 35.00)
            $custoFinanceiro = (float)$request->get('custoFinanceiro'); // em decimal (ex.: 0.15)
            $preco = [
                'prazo' => $prazo,
                'margem' => $margem,
                'custoOperacional' => $custoOperacional,
                'custoFinanceiro' => $custoFinanceiro,
                'precoCusto' => $precoCusto,

            ];
        } catch (\Exception $e) {
            return (new APIProblem(
                400,
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
            ))->toJsonResponse();
        }

        try {
            $this->calculoPreco->calcularPreco($preco);
        } catch (\Exception $e) {
            return (new APIProblem(
                400,
                ApiProblem::TYPE_INTERNAL_ERROR
            ))->toJsonResponse();
        }

        return new JsonResponse($preco);
    }

}
