<?php

namespace App\Controller\Ecommerce;

use App\Business\Ecommerce\IntegradorTray;
use App\Business\Ecommerce\TrayBusiness;
use App\Entity\Ecommerce\ClienteConfig;
use App\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayController extends BaseController
{

    protected SyslogBusiness $syslog;

    private ClienteConfigEntityHandler $clienteConfigEntityHandler;

    protected IntegradorTray $integradorTray;

    private TrayBusiness $trayBusiness;

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
     * @required
     * @param TrayBusiness $trayBusiness
     */
    public function setTrayBusiness(TrayBusiness $trayBusiness): void
    {
        $this->trayBusiness = $trayBusiness;
    }


    /**
     * Método exposto para receber o "code" da tray.
     *
     * @Route("/ecommerce/tray/endpoint/{clienteConfig}", name="ecommerce_tray_endpoint")
     * @throws ViewException
     */
    public function trayEndpoint(Request $request, ?ClienteConfig $clienteConfig = null): Response
    {

        $r = [];
//        $r[] = 'Cliente IP: ' . $request->getClientIp();
//        $r[] = 'Host: ' . $request->getHost();
//        $r[] = '<hr />';
//        $r[] = 'Content:';
//        $r[] = $request->getContent();
//        $r[] = '--------------------------------';
//        $r[] = 'Query';
//        foreach ($request->query->all() as $k => $v) {
//            $r[] = $k . ': ' . print_r($v, true);
//        }
//        $r[] = '--------------------------------';
//        $r[] = 'Request';
//        foreach ($request->request->all() as $k => $v) {
//            $r[] = $k . ': ' . print_r($v, true);
//        }
//        $r[] = '--------------------------------';
//        $r[] = 'Headers';
//        foreach ($request->headers->all() as $k => $v) {
//            $r[] = $k . ': ' . print_r($v, true);
//        }

        $this->syslog->info('ecomm_tray_endpoint', implode(PHP_EOL, $r));

        if ($request->get("code")) {
            $r[] = '<h3>Código Tray: <b style="color: orangered">' . $request->get("code") . '</b></h3>';
            if ($clienteConfig) {
                $clienteConfig->jsonData['tray']['code'] = $request->get("code");
                $this->clienteConfigEntityHandler->save($clienteConfig);
            }
        }
        if ($clienteConfig) {
            return new RedirectResponse('/v/ecommerce/clienteConfig/form?id=' . $clienteConfig->getId());
        } else {
            return new Response(implode('<br/>', $r));
        }
    }


    /**
     * @Route("/ecommerce/tray/webhook", name="ecommerce_tray_webhook")
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
     * @Route("/api/ecommerce/tray/reautorizarNaTray", name="api_ecommerce_tray_reautorizarNaTray")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function reautorizarNaTray(): JsonResponse
    {
        $this->integradorTray->autorizarApp();

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/tray/renewAccessToken/{clienteConfig}", name="api_ecommerce_tray_renewAccessToken")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function renewAccessTokenTray(ClienteConfig $clienteConfig): JsonResponse
    {
        try {
            $this->trayBusiness->renewAccessToken($clienteConfig);
            return CrosierApiResponse::success();
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }

    /**
     * @Route("/api/ecommerce/tray/renewAllAccessTokens", name="api_ecommerce_tray_renewAllAccessTokens")
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
     * @Route("/ecommerce/tray/integraDepto/{depto}", name="ecommerce_tray_integraDepto", requirements={"depto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraDepto(Depto $depto): Response
    {
        // $this->integradorTray->integraDepto($depto);
        return new Response('Depto/categoria integrado com sucesso');
    }

    /**
     * @Route("/ecommerce/tray/integraGrupo/{grupo}", name="ecommerce_tray_integraGrupo", requirements={"grupo"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraGrupo(Grupo $grupo): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integraCategoria($depto);
        return new Response('Depto/categoria integrado com sucesso');
    }

    /**
     * @Route("/ecommerce/tray/integraSubgrupo/{subgrupo}", name="ecommerce_tray_integraSubgrupo", requirements={"subgrupo"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraSubgrupo(Subgrupo $subgrupo): Response
    {
        $this->integradorTray->integraSubgrupo($subgrupo);
        return new Response('Subgrupo integrado com sucesso');
    }


    /**
     * @Route("/ecommerce/tray/alteraSubgrupo/{subgrupo}", name="ecommerce_tray_alteraSubgrupo", requirements={"subgrupo"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function alteraSubgrupo(Subgrupo $subgrupo): Response
    {
        $this->integradorTray->alteraSubgrupo($subgrupo);
        return new Response('Subgrupo integrado com sucesso');
    }


    /**
     * @Route("/ecommerce/tray/alteraGrupo/{grupo}", name="ecommerce_tray_alteraGrupo", requirements={"grupo"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function alteraGrupo(Grupo $grupo): Response
    {
        $this->integradorTray->alteraGrupo($grupo);
        return new Response('Grupo integrado com sucesso');
    }


    /**
     * @Route("/ecommerce/tray/alteraDepto/{depto}", name="ecommerce_tray_alteraDepto", requirements={"depto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function alteraDepto(Depto $depto): Response
    {
        $this->integradorTray->alteraDepto($depto);
        return new Response('Depto integrado com sucesso');
    }


    /**
     * @Route("/ecommerce/tray/integraProduto/{produto}", name="ecommerce_tray_integraProduto", requirements={"produto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integraProduto(Produto $produto): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
        try {
            $this->integradorTray->integraProduto($produto);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            if ($e instanceof ViewException) {
                return CrosierApiResponse::error($e, true);
            }
            return CrosierApiResponse::error();
        }
    }


    /**
     * @Route("/ecommerce/tray/integraVariacaoProduto/{produto}", name="ecommerce_tray_integraVariacaoProduto", requirements={"produto"="\d+"})
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
     * @Route("/ecommerce/tray/obterPedido", name="ecommerce_tray_obterPedido")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function obterPedido(Request $request): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
        $numPedido = $request->get('numPedido');
        $resalvar = filter_var($request->get('resalvar'), FILTER_VALIDATE_BOOLEAN);
        $json = $this->integradorTray->obterPedidoDoEcommerce($numPedido);
        $this->integrarVendaParaCrosier($json, $resalvar);
        return new Response('Pedido integrado com sucesso');
    }


    /**
     * @Route("/ecommerce/tray/integrarVendaParaEcommerce/{numPedido}", name="ecommerce_tray_integrarVendaParaEcommerce", requirements={"numPedido"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function integrarVendaParaEcommerce(int $numPedido): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
//        $integradorTray->integrarVendaParaEcommerce2($numPedido);
        return new Response('Pedido integrado com sucesso');
    }

    /**
     *
     * @Route("/ecommerce/tray/obterVendasPorPeriodo/{dtIni}", name="ecommerce_tray_obterVendasPorPeriodo", defaults={"dtIni": null})
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
        $resalvar = filter_var($request->get('resalvar'), FILTER_VALIDATE_BOOLEAN);
        $total = $this->integradorTray->obterVendas($dtIni, $resalvar);
        return new Response('OK: ' . $total);
    }


    /**
     * @Route("/ecommerce/tray/atualizaDadosEnvio/{numPedido}", name="ecommerce_tray_atualizaDadosEnvio", requirements={"numPedido"="\d+"})
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
     * @Route("/ecommerce/tray/cancelarPedido/{numPedido}", name="ecommerce_tray_cancelarPedido", requirements={"numPedido"="\d+"})
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
     * @Route("/ecommerce/tray/gerarNFeParaVenda/{codVenda}", name="ecommerce_tray_gerarNFeParaVenda")
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function gerarNFeParaVenda(string $codVenda): Response
    {
        try {
            $nfId = $this->integradorTray->gerarNFeParaVenda($codVenda);
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $nfId]);
        } catch (\Throwable $e) {
            $msg = ExceptionUtils::treatException($e);
            if ($e->getPrevious()) {
                $msg .= ' - ' . $e->getPrevious()->getMessage();
            }
            $this->addFlash('error', $msg);
            return new Response($msg);
        }

    }

    /**
     * @Route("/ecommerce/tray/integrarDadosFiscaisNoPedido/{codVenda}", name="ecommerce_tray_integrarDadosFiscaisNoPedido")
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


    /**
     * @required
     * @param ClienteConfigEntityHandler $clienteConfigEntityHandler
     */
    public function setClienteConfigEntityHandler(ClienteConfigEntityHandler $clienteConfigEntityHandler): void
    {
        $this->clienteConfigEntityHandler = $clienteConfigEntityHandler;
    }


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
     * @Route("/api/ecommerce/trayVenda/atualizarListaVendasCliente/{id}", name="api_ecommerce_trayVenda_atualizarListaVendasCliente")
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


    /**
     * @Route("/ecommerce/tray/consultaProduto/{produto}", name="ecommerce_tray_consultaProduto", requirements={"produto"="\d+"})
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function consultaProduto(Produto $produto): Response
    {
//        $integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
//        $integradorTray->accessToken = $clienteConfig->jsonData['tray']['access_token'];
        $r = $this->integradorTray->consultaProduto($produto);
        return new Response('<pre>' . print_r($r, true));
    }


}