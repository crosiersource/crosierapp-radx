<?php

namespace App\Controller\ECommerce;

use App\Business\ECommerce\IntegradorWebStorm;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
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
    public function integrarDeptosGruposSubgrupos(IntegradorWebStorm $integraWebStormBusiness)
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
                $qtde = null;
            }

            $rQtde = $integraWebStormBusiness->reenviarProdutosParaIntegracao($qtde);
            $this->addFlash('success', $rQtde . ' produtos enviados para integração com sucesso');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao enviar produtos para integração (' . $e->getMessage() . ')');
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
     * @param \DateTime $dtVenda
     * @return Response
     * @throws ViewException
     * @throws \Doctrine\DBAL\ConnectionException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendas(Request $request, IntegradorWebStorm $integraWebStormBusiness, ?\DateTime $dtVenda = null)
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
     * @Route("/est/integraWebStorm/integrarVendaParaECommerce/{venda}", name="est_integraWebStorm_integrarVendaParaECommerce")
     *
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param Venda|null $venda
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarVendaParaECommerce(Request $request, IntegradorWebStorm $integraWebStormBusiness, Venda $venda)
    {
        $integraWebStormBusiness->integrarVendaParaECommerce($venda);
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraWebStorm/atualizaEstoqueEPrecos", name="est_integraWebStorm_atualizaEstoqueEPrecos")
     *
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function atualizaEstoqueEPrecos(IntegradorWebStorm $integraWebStormBusiness)
    {
        $integraWebStormBusiness->atualizaEstoqueEPrecos();
        return new Response('OK');
    }

}