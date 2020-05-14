<?php

namespace App\Controller\ECommerce;

use App\Business\ECommerce\IntegraWebStorm;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param IntegraWebStorm $integraWebStormBusiness
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarDeptosGruposSubgrupos(IntegraWebStorm $integraWebStormBusiness)
    {
        $integraWebStormBusiness->integrarDeptosGruposSubgrupos();
        return new Response('OK');
    }

    /**
     *
     * @Route("/est/integraWebStorm/integrarProduto/{produto}", name="est_integraWebStorm_integrarProduto")
     * @param IntegraWebStorm $integraWebStormBusiness
     * @param Produto $produto
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarProduto(IntegraWebStorm $integraWebStormBusiness, Produto $produto)
    {
        $integraWebStormBusiness->integraProduto($produto);
        return new Response('OK');
    }


    /**
     *
     * @Route("/est/integraWebStorm/obterVendas/{dtVenda}", name="est_integraWebStorm_obterVendas", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param IntegraWebStorm $integraWebStormBusiness
     * @param \DateTime $dtVenda
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendas(IntegraWebStorm $integraWebStormBusiness, ?\DateTime $dtVenda = null)
    {
        if (!$dtVenda) {
            $dtVenda = new \DateTime();
        }
        $integraWebStormBusiness->obterVendas($dtVenda);
        return new Response('OK');
    }

}