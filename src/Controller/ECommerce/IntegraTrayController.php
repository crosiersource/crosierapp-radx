<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorTray;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegraTrayController extends BaseController
{

    protected SyslogBusiness $syslog;

    protected IntegradorTray $integradorTray;

    /**
     * @required
     * @param SyslogBusiness $syslog
     */
    public function setSyslog(SyslogBusiness $syslog): void
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
    }


    /**
     * @required
     * @param IntegradorTray $integradorTray
     */
    public function setIntegradorTray(IntegradorTray $integradorTray): void
    {
        $this->integradorTray = $integradorTray;
    }


    /**
     * Método exposto para receber o "code" da tray.
     *
     * @Route("/ecommIntegra/tray/endpoint", name="ecommIntegra_tray_endpoint")
     * @throws ViewException
     */
    public function trayEndpoint(Request $request): Response
    {
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        $r[] = '<hr />';
        $r[] = 'Content:';
        $r[] = $request->getContent();
        $r[] = '--------------------------------';
        $r[] = 'Query';
        foreach ($request->query->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        $r[] = '--------------------------------';
        $r[] = 'Request';
        foreach ($request->request->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        $r[] = '--------------------------------';
        $r[] = 'Headers';
        foreach ($request->headers->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }

        $this->syslog->info('ecomm_tray_endpoint', implode(PHP_EOL, $r));

        if ($request->get("code")) {
            $storeId = $request->get('store');
            $store = $this->integradorTray->getStore($storeId);
            $store['code'] = $request->get("code");
            
            $this->integradorTray->saveStoreConfig($store);
            return new Response(implode('<br/>', $r));
        } else {
            return new Response('"code" não retornado pela tray');
        }
    }


    /**
     * @Route("/ecommIntegra/tray/webhook", name="ecommIntegra_tray_webhook")
     */
    public function trayWebhook(Request $request): Response
    {
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        foreach ($request->request->all() as $k => $v) {
            $r[] = $k . ': ' . $v;
        }
        $this->syslog->info('tray/webhook', implode(PHP_EOL, $r));
        return new Response('OK');
    }


    /**
     * @Route("/api/ecommIntegra/tray/autorizarNaTray/{storeId}", name="api_ecommIntegra_tray_autorizarNaTray")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function autorizarNaTray(?string $storeId = null): JsonResponse
    {
        $this->integradorTray->autorizarApp($storeId);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommIntegra/tray/renewAccessToken/{id}", name="api_ecommIntegra_tray_renewAccessToken")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function renewAccessTokenTray(?string $storeId = null): JsonResponse
    {
        $store = $this->integradorTray->getStore($storeId);
        $this->integradorTray->renewAccessToken($store);
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }
    
    /**
     * @Route("/api/ecommIntegra/tray/renewAllAccessTokens", name="api_ecommIntegra_tray_renewAllAccessTokens")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function renewAllAccessTokens(): JsonResponse
    {
        $this->integradorTray->renewAllAccessTokens();
        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/ecommIntegra/tray/integraCategoria/{depto}", name="ecommIntegra_tray_integraCategoria", requirements={"depto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraCategoria(Depto $depto): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integraCategoria($depto);
        return new Response('Depto/categoria integrado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/integraProduto/{produto}", name="ecommIntegra_tray_integraProduto", requirements={"produto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraProduto(Produto $produto): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integraProduto($produto);
        return new Response('Produto integrado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/integraVariacaoProduto/{produto}", name="ecommIntegra_tray_integraVariacaoProduto", requirements={"produto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraVariacaoProduto(Produto $produto): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integraVariacaoProduto($produto);
        return new Response('Variação de Produto integrado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/obterPedido/{id}", name="ecommIntegra_tray_obterPedido", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function obterPedido(int $numPedido): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->obterPedido($numPedido);
        return new Response('Pedido integrado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/integrarVendaParaECommerce/{numPedido}", name="ecommIntegra_tray_integrarVendaParaECommerce", requirements={"numPedido"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integrarVendaParaECommerce(int $numPedido): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integrarVendaParaECommerce2($numPedido);
        return new Response('Pedido integrado com sucesso');
    }

    /**
     *
     * @Route("/ecommIntegra/tray/obterVendasPorPeriodo/{dtIni}", name="ecommIntegra_tray_obterVendasPorPeriodo", defaults={"dtIni": null})
     * @ParamConverter("dtIni", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorSimplo7 $integraSimplo7Business
     * @param \DateTime|null $dtIni
     * @param \DateTime|null $dtFim
     * @return Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function obterVendasPorPeriodo(Request $request, ?\DateTime $dtIni = null): Response
    {
        if (!$dtIni) {
            $dtIni = new \DateTime();
        }
        $resalvar = $request->get('resalvar') ?? null;
        $total = $this->integradorTray->obterVendas($dtIni, $resalvar === 'S');
        return new Response('OK: ' . $total);
    }



    /**
     * @Route("/ecommIntegra/tray/atualizaDadosEnvio/{numPedido}", name="ecommIntegra_tray_atualizaDadosEnvio", requirements={"numPedido"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function atualizaDadosEnvio(int $numPedido): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->atualizaDadosEnvio($numPedido);
        return new Response('Pedido integrado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/cancelarPedido/{numPedido}", name="ecommIntegra_tray_cancelarPedido", requirements={"numPedido"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function cancelarPedido(int $numPedido): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->cancelarPedido($numPedido);
        return new Response('Pedido cancelado com sucesso');
    }


    /**
     * @Route("/ecommIntegra/tray/gerarNFeParaVenda/{codVenda}", name="ecommIntegra_tray_gerarNFeParaVenda")
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function gerarNFeParaVenda(string $codVenda): Response
    {
        try {
            $nfId = $this->integradorTray->gerarNFeParaVenda($codVenda);
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $nfId]);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return new Response($e->getMessage());
        }
        
    }

    /**
     * @Route("/ecommIntegra/tray/integrarDadosFiscaisNoPedido/{codVenda}", name="ecommIntegra_tray_integrarDadosFiscaisNoPedido")
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarDadosFiscaisNoPedido(string $codVenda): Response
    {
        try {
            $nfId = $this->integradorTray->integrarDadosFiscaisNoPedido($codVenda);
            $this->addFlash('success', 'Dados fiscais integrados com sucesso no pedido');
            return new Response('OK');
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return new Response('ERRO');
        }
    }


}