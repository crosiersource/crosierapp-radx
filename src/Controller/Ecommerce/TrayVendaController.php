<?php


namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\TrayBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\ClienteConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayVendaController extends BaseController
{



    /**
     * @Route("/api/ecommerce/trayVenda/atualizarListaVendas", name="ecommerce_trayVenda_atualizarListaVendas")
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     * @throws ViewException
     */
    public function atualizarListaVendas(TrayBusiness $trayBusiness): JsonResponse
    {
        $trayBusiness->atualizar();
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/trayVenda/atualizarListaVendasCliente/{id}", name="ecommerce_trayVenda_atualizarListaVendasCliente")
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     * @throws ViewException
     */
    public function atualizarListaVendasCliente(TrayBusiness $trayBusiness, ClienteConfig $clienteConfig): JsonResponse
    {
        $trayBusiness->atualizarCliente($clienteConfig);
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }

}
