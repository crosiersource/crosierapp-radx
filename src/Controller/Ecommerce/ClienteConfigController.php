<?php


namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\MercadoLivreBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\TrayBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\ClienteConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class ClienteConfigController extends BaseController
{

    private MercadoLivreBusiness $mercadoLivreBusiness;

    private TrayBusiness $trayBusiness;

    /**
     * @required
     * @param MercadoLivreBusiness $mercadoLivreBusiness
     */
    public function setMercadoLivreBusiness(MercadoLivreBusiness $mercadoLivreBusiness): void
    {
        $this->mercadoLivreBusiness = $mercadoLivreBusiness;
    }

    /**
     * @required
     * @param TrayBusiness $trayBusiness
     */
    public function setTrayBusiness(TrayBusiness $trayBusiness): void
    {
        $this->trayBusiness = $trayBusiness;
    }


    /**
     * @Route("/api/ecommerce/clienteConfig/registrarAutorizacaoMercadoLivre", name="ecommerce_clienteConfig_registrarAutorizacaoMercadoLivre")
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function registrarAutorizacaoMercadoLivre(Request $request): JsonResponse
    {
        $i = $request->get('i');
        $mlCode = $request->query->get('mlCode'); // token_tg
        if (!$mlCode) {
            throw new ViewException('mlCode n/d');
        }
        $uuid = $request->query->get('UUID'); // clienteConfig.UUID
        if (!$uuid) {
            throw new ViewException('UUID n/d');
        }
        $i = $request->query->get('i'); // clienteConfig.UUID
        if (!$i) {
            throw new ViewException('i n/d');
        }

      
        $repoClienteConfig = $this->getDoctrine()->getRepository(ClienteConfig::class);
        $clienteConfig = $repoClienteConfig->findOneByFiltersSimpl([['UUID', 'EQ', $uuid]]);
        if (!$clienteConfig) {
            throw new ViewException('clienteConfig n/d');
        }
        
        if (!($clienteConfig->jsonData['mercadolivre'][$i] ?? false)) {
            throw new ViewException('clienteConfig.jsonData.mercadolivre.$i n/d');
        }
        
        $clienteConfig->jsonData['mercadolivre']['token_tg'] = $mlCode;
        
        $this->mercadoLivreBusiness->autorizarApp($clienteConfig);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }

    /**
     * @Route("/api/ecommerce/clienteConfig/reautorizarNoMercadoLivre/{id}", name="ecommerce_clienteConfig_reautorizarNoMercadoLivre", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function reautorizarNoMercadoLivre(ClienteConfig $clienteConfig): JsonResponse
    {
        $this->mercadoLivreBusiness->autorizarApp($clienteConfig);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/clienteConfig/renewAccessTokenMercadoLivre/{id}", name="ecommerce_clienteConfig_renewAccessTokenMercadoLivre", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function renewAccessTokenMercadoLivre(ClienteConfig $clienteConfig): JsonResponse
    {
        $this->mercadoLivreBusiness->handleAccessToken($clienteConfig);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/clienteConfig/autorizarNaTray/{id}", name="ecommerce_clienteConfig_autorizarNaTray", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function autorizarNaTray(ClienteConfig $clienteConfig): JsonResponse
    {
        $this->trayBusiness->autorizarApp($clienteConfig);
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/clienteConfig/renewAccessTokenTray/{id}", name="ecommerce_clienteConfig_renewAccessTokenTray", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function renewAccessTokenTray(ClienteConfig $clienteConfig): JsonResponse
    {
        $this->trayBusiness->handleAccessToken($clienteConfig);
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/clienteConfig/renewAllAccessTokenMl", name="ecommerce_clienteConfig_renewAllAccessTokenMl")
     * @IsGranted("ROLE_ECOMM_ADMIN", statusCode=403)
     */
    public function renewAllAccessTokenMl(): JsonResponse
    {
        try {
            /** @var ClienteConfigRepository $repoClienteConfig */
            $repoClienteConfig = $this->getDoctrine()->getRepository(ClienteConfig::class);
            $todos = $repoClienteConfig->findByFiltersSimpl([['ativo', 'EQ', true]], null, 0, null);
            foreach ($todos as $cc) {
                $mlBusiness->handleAccessToken($cc);
            }
            return CrosierApiResponse::success();
        } catch (ViewException $e) {
            return CrosierApiResponse::error($e);
        }
    }


}
