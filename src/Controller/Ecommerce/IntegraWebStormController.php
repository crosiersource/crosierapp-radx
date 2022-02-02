<?php

namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\IntegradorWebStorm;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegraWebStormController extends BaseController
{

    /**
     *
     * @Route("/est/integraWebStorm/integrarDeptosGruposSubgrupos", name="est_integraWebStorm_integrarDeptosGruposSubgrupos")
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarDeptosGruposSubgrupos(IntegradorWebStorm $integraWebStormBusiness): Response
    {
        $integraWebStormBusiness->integrarDeptosGruposSubgrupos();
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraWebStorm/integrarProduto/{produto}", name="est_integraWebStorm_integrarProduto")
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param Produto $produto
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     * @return RedirectResponse
     */
    public function integrarProduto(Request $request, IntegradorWebStorm $integraWebStormBusiness, Produto $produto): RedirectResponse
    {
        try {
            $start = microtime(true);
            $integrarImagens = null;
            if ($request->query->has('integrarImagens')) {
                $integrarImagens = $request->query->get('integrarImagens');
            } else {
                $integrarImagens = true;
            }
            $integraWebStormBusiness->integraProduto($produto, $integrarImagens);
            $tt = (int)(microtime(true) - $start);
            $this->addFlash('success', 'Produto integrado com sucesso (em ' . $tt . 's)');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao integrar produto (' . $e->getMessage() . ')');
        }
        return $this->redirectToRoute('est_produto_form', ['id' => $produto->getId(), '_fragment' => 'ecommerce']);
    }

    /**
     *
     * @Route("/est/integraWebStorm/reenviarProdutosParaIntegracao", name="est_integraWebStorm_reenviarProdutosParaIntegracao")
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function reenviarProdutosParaIntegracao(Request $request, IntegradorWebStorm $integraWebStormBusiness): Response
    {
        try {
            $integrarImagens = null;

            if ($request->query->has('qtde')) {
                $qtde = $request->query->get('qtde');
            } else {
                $qtde = 1;
            }

            $rQtde = $integraWebStormBusiness->reenviarTodosOsProdutosParaIntegracao($qtde);
            $this->addFlash('success', $rQtde . ' produtos enviados para integração com sucesso');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erro ao enviar produtos para integração (' . $e->getMessage() . ')');
        }
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraWebStorm/atualizarTodosOsEstoquesEPrecos", name="est_integraWebStorm_atualizarTodosOsEstoquesEPrecos")
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function atualizarTodosOsEstoquesEPrecos(Request $request, IntegradorWebStorm $integraWebStormBusiness): Response
    {
        try {
            $rQtde = $integraWebStormBusiness->atualizarTodosOsEstoquesEPrecos();
            $this->addFlash('success', $rQtde . ' qtdes/preços atualizados.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erro ao atualiar qtdes/preços (' . $e->getMessage() . ')');
        }
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraWebStorm/obterVendas/{dtVenda}", name="est_integraWebStorm_obterVendas", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param \DateTime|null $dtVenda
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendas(Request $request, IntegradorWebStorm $integraWebStormBusiness, ?\DateTime $dtVenda = null): Response
    {
        if (!$dtVenda) {
            $dtVenda = new \DateTime();
        }
        $resalvar = $request->get('resalvar') ?? null;
        $integraWebStormBusiness->obterVendas($dtVenda, $resalvar === 'S');
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraWebStorm/obterVendaPorId/{id}", name="est_integraWebStorm_obterVendaPorId")
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendaPorId(Request $request, IntegradorWebStorm $integraWebStormBusiness, int $id): Response
    {
        $resalvar = $request->get('resalvar') ?? null;
        $integraWebStormBusiness->obterVendaPorId($id, $resalvar === 'S');
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraWebStorm/integrarVendaParaEcommerce/{venda}", name="est_integraWebStorm_integrarVendaParaEcommerce")
     *
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param Venda|null $venda
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function integrarVendaParaEcommerce(IntegradorWebStorm $integraWebStormBusiness, Venda $venda): Response
    {
        $integraWebStormBusiness->integrarVendaParaEcommerce($venda);
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraWebStorm/reintegrarVendaParaCrosier/{venda}", name="est_integraWebStorm_reintegrarVendaParaCrosier")
     *
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param Venda|null $venda
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function reintegrarVendaParaCrosier(IntegradorWebStorm $integraWebStormBusiness, Venda $venda): Response
    {
        $integraWebStormBusiness->reintegrarVendaParaCrosier($venda);
        return new Response('OK');
    }

}
