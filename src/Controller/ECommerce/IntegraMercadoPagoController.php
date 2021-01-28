<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorMercadoPago;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaPagto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegraMercadoPagoController extends BaseController
{

    /**
     *
     * @Route("/est/mercadoPago/handleTransacaoParaVendaPagto/{vendaPagto}", name="est_mercadoPago_handleTransacaoParaVendaPagto")
     *
     * @param IntegradorMercadoPago $integr
     * @param VendaPagto $vendaPagto
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function obterListaDeStatus(IntegradorMercadoPago $integr, VendaPagto $vendaPagto): Response
    {
        $status = $integr->handleTransacaoParaVendaPagto($vendaPagto);
        return new Response(json_encode($status));
    }


    /**
     *
     * @Route("/est/mercadoLivre/obterVendas/{dtVenda}", name="est_mercadoLivre_obterVendas")
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorMercadoPago $integr
     * @param \DateTime $dtVenda
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function mercadoLivreObterVendas(Request $request, IntegradorMercadoPago $integr, \DateTime $dtVenda): Response
    {
        $resalvar = $request->get('resalvar') ?? null;
        $integr->mlUser = 'contato@defamilia.eco.br';
        $status = $integr->obterVendasMercadoLivre($dtVenda, $resalvar);
        return new Response(json_encode($status));
    }

}