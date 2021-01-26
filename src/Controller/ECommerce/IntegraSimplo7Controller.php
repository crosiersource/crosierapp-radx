<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorSimplo7;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegraSimplo7Controller extends BaseController
{

    /**
     *
     * @Route("/est/integraSimplo7/obterProdutos", name="est_integraSimplo7_obterProdutos")
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterProdutos(IntegradorSimplo7 $integraSimplo7Business): Response
    {
        $integraSimplo7Business->obterProdutos();
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraSimplo7/obterVendas/{dtVenda}", name="est_integraSimplo7_obterVendas", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param \DateTime|null $dtVenda
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendas(Request $request, IntegradorSimplo7 $integraSimplo7Business, ?\DateTime $dtVenda = null): Response
    {
        if (!$dtVenda) {
            $dtVenda = new \DateTime();
        }
        $resalvar = $request->get('resalvar') ?? null;
        $integraSimplo7Business->obterVendas($dtVenda, $resalvar === 'S');
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraSimplo7/reintegrarVendaParaCrosier/{venda}", name="est_integraSimplo7_reintegrarVendaParaCrosier")
     *
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param Venda|null $venda
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function reintegrarVendaParaCrosier(IntegradorSimplo7 $integraSimplo7Business, Venda $venda): Response
    {
        $integraSimplo7Business->reintegrarVendaParaCrosier($venda);
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraSimplo7/obterListaDeStatus/", name="est_integraSimplo7_obterListaDeStatus")
     *
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterListaDeStatus(IntegradorSimplo7 $integraSimplo7Business): Response
    {
        $status = $integraSimplo7Business->obterStatusPedidos();
        $html = '';
        foreach ($status as $s) {
            $html .= $s['id'] . ': ' . $s['nome'] . '<br>';
        }
        return new Response($html);
    }


    /**
     *
     * @Route("/est/integraSimplo7/gerarNFeParaVenda/{codVendaSimplo7}", name="est_integraSimplo7_gerarNFeParaVenda")
     *
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param int $codVendaSimplo7
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function gerarNFeParaVenda(IntegradorSimplo7 $integraSimplo7Business, int $codVendaSimplo7): Response
    {
        try {
            $nfId = $integraSimplo7Business->gerarNFeParaVenda($codVendaSimplo7);
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $nfId]);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return new Response('ERRO');
    }


    /**
     *
     * @Route("/est/integraSimplo7/atualizarPedidosMelhorEnvio/", name="est_integraSimplo7_atualizarPedidosMelhorEnvio")
     *
     * @param Request $request
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param \DateTime|null $dtVenda
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function atualizarPedidosMelhorEnvio(IntegradorSimplo7 $integraSimplo7Business): Response
    {
        $integraSimplo7Business->atualizarPedidosMelhorEnvio();
        return new Response('OK');
    }

}
