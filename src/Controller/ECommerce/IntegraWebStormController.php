<?php

namespace App\Controller\Estoque;

use App\Business\ECommerce\IntegraWebStorm;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @Route("/est/integraWebStorm/integrarMarcas", name="est_integraWebStorm_integrarMarcas")
     * @param IntegraWebStorm $integraWebStormBusiness
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(IntegraWebStorm $integraWebStormBusiness)
    {
        $integraWebStormBusiness->integrarMarcas();
        return new Response('OK');
    }

}