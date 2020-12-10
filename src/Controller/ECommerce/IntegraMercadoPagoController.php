<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorMercadoPago;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaPagto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     */
    public function obterListaDeStatus(IntegradorMercadoPago $integr, VendaPagto $vendaPagto): Response
    {
        $status = $integr->handleTransacaoParaVendaPagto($vendaPagto);
        return new Response(json_encode($status));
    }

}