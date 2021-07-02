<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
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
     * @Route("/est/integraSimplo7/obterVendasPorPeriodo/{dtIni}/{dtFim}", name="est_integraSimplo7_obterVendasPorPeriodo", defaults={"dtIni": null, "dtFim": null})
     * @ParamConverter("dtIni", options={"format": "Y-m-d"})
     * @ParamConverter("dtFim", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param \DateTime|null $dtIni
     * @param \DateTime|null $dtFim
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendasPorPeriodo(Request $request, IntegradorSimplo7 $integraSimplo7Business, ?\DateTime $dtIni = null, ?\DateTime $dtFim = null): Response
    {
        if (!$dtIni) {
            $dtIni = new \DateTime();
        }
        if (!$dtFim) {
            $dtFim = new \DateTime();
        }
        $resalvar = $request->get('resalvar') ?? null;
        $total = $integraSimplo7Business->obterVendasPorPeriodo($dtIni, $dtFim, $resalvar === 'S');
        return new Response('OK: ' . $total);
    }


    /**
     *
     * @Route("/est/integraSimplo7/obterVendasPorNumero/{numero}", name="est_integraSimplo7_obterVendasPorNumero")
     *
     * @param Request $request
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param int $numero
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendasPorNumero(Request $request, IntegradorSimplo7 $integraSimplo7Business, int $numero): Response
    {
        $resalvar = $request->get('resalvar') ?? null;
        $total = $integraSimplo7Business->obterVendasPorNumero($numero, $resalvar === 'S');
        return new Response('OK: ' . $total);
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
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function atualizarPedidosMelhorEnvio(IntegradorSimplo7 $integraSimplo7Business): Response
    {
        $r = $integraSimplo7Business->atualizarPedidosMelhorEnvio();
        return new Response(implode('<br>', $r));
    }


    /**
     *
     * @Route("/est/integraSimplo7/atualizarDtPagtoPedido", name="est_integraSimplo7_atualizarDtPagtoPedido")
     *
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function atualizarDtPagtoPedido(Request $request, IntegradorSimplo7 $integraSimplo7Business): Response
    {
        $dtPagto = null;
        $id = null;
        if ($request->get('dtPagto')) {
            $dtPagto = DateTimeUtils::parseDateStr($request->get('dtPagto'));
            if (!$dtPagto) {
                $this->addFlash('warn', 'Data inválida!');
            }
        }
        if ($request->get('id') && ((int)$request->get('id'))) {
            $id = $request->get('id');
        }
        if ($dtPagto && $id && $request->get('btnSalvar')) {
            $integraSimplo7Business->atualizarDtPagtoPedido($id, $dtPagto);
            $this->addFlash('info', 'Atualizado com sucesso!');
        }
        $params['id'] = $id;
        $params['dtPagto'] = $dtPagto ? $dtPagto->format('d/m/Y') : null;
        return $this->doRender('ECommerce/atualizaDtPagtoPedido.html.twig', $params);
    }


}
