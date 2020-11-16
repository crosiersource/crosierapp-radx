<?php

namespace App\Controller\Estoque;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibRadxBundle\Business\Estoque\CalculoPreco;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 * @author Carlos Eduardo Pauluk
 */
class CalculoPrecoController extends BaseController
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
     * @Route("/est/preco/calcular", name="est_preco_calcular")
     * @param Request $request
     * @return Response
     */
    public function calcular(Request $request): Response
    {

        //$this->ge

        $precoCusto = $request->get('precoCusto');
        $precoPrazo = $request->get('precoPrazo');
        $margem = $request->get('margem');
        $prazo = $request->get('prazo') ?? 0;
        $custoOperacional = $request->get('custoOperacional') ?? 35;
        $custoFinanceiro = $request->get('custoFinanceiro') ?? 0.15;


        $preco = [
            'prazo' => $prazo,
            'margem' => $margem,
            'custoOperacional' => $custoOperacional,
            'custoFinanceiro' => $custoFinanceiro,
            'precoCusto' => $precoCusto,
            'precoPrazo' => $precoPrazo,
        ];


        try {
            $this->calculoPreco->calcularPreco($preco);
        } catch (\Exception $e) {
            return new Response('ERRO');
        }

        $r = 'Prazo: ' . $preco['prazo'] . '<br>';
        $r .= 'Margem: ' . $preco['margem'] . '<br>';
        $r .= 'C. Ope: ' . $preco['custoOperacional'] . '<br>';
        $r .= 'C. Fin: ' . $preco['custoFinanceiro'] . '<br>';
        $r .= 'Preço Custo: ' . $preco['precoCusto'] . '<br>';
        $r .= 'Preço Prazo: ' . $preco['precoPrazo'] . '<br>';
        $r .= 'Coeficiente: ' . $preco['coeficiente'] . '<br>';
        $r .= 'Preço a Vista: ' . $preco['precoVista'] . '<br>';

        return new Response($r);
    }

}