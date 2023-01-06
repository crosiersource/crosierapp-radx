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
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
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
        $this->integradorTray->integraSubgrupo($subgrupo, true);
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
     * @IsGranted("ROLE_ESTOQUE_ECOMMERCE", statusCode=403)
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
     * @Route("/api/ecommerce/migrarProdutosParaCategoria", name="api_ecommerce_migrarProdutosParaCategoria")
     * @IsGranted("ROLE_ADMIN", statusCode=403)
     */
    public function migrarProdutosParaCategoria(ProdutoEntityHandler $produtoEntityHandler): Response
    {
        $codigos = [57091, 59055, 28313, 28313, 34579, 37542, 3563, 3563, 67566, 20499, 47066, 20501, 62752, 62752, 60864, 60864, 64901, 64901, 64902, 34442, 7605, 20520, 20520, 20519, 20523, 41751, 41751, 20529, 66925, 32596, 32596, 23368, 47162, 64519, 66437, 26262, 42881, 26548, 7737, 41847, 7747, 7747, 7747, 47188, 47189, 22778, 7771, 7768, 7770, 43734, 64138, 64830, 7822, 40222, 7827, 47236, 3567, 3568, 3568, 3569, 3569, 30902, 60892, 6147, 6147, 58884, 3560, 60787, 61851, 7931, 63795, 63795, 58782, 58782, 3525, 57914, 57914, 57914, 63796, 4114, 4114, 64008, 20610, 20622, 5715, 5715, 62956, 47417, 47419, 65663, 67927, 67927, 63527, 54752, 67928, 20362, 6693, 6693, 20636, 60777, 65648, 65648, 62131, 62328, 68579, 59456, 67817, 67818, 47604, 40232, 4891, 6914, 47621, 63510, 31160, 41156, 47634, 40654, 40654, 46440, 47643, 47643, 47644, 8302, 35933, 63700, 66954, 47652, 54941, 54941, 42, 44, 63276, 63276, 18966, 18966, 8469, 23681, 57915, 57915, 24991, 56719, 56719, 63320, 63321, 36330, 6655, 6655, 19006, 6791, 6791, 36200, 8751, 44439, 20784, 20784, 58013, 60220, 39911, 48718, 39115, 64496, 64496, 64497, 66406, 68011, 55922, 9052, 9052, 9053, 20814, 3574, 66052, 66052, 66053, 66053, 66054, 68050, 60743, 60744, 9186, 20839, 20839, 20840, 48190, 48190, 22807, 6023, 9236, 9236, 19083, 9270, 20848, 63196, 48217, 36902, 48226, 26806, 38266, 60644, 58503, 58503, 56713, 59716, 63450, 63094, 65994, 65994, 66358, 63100, 5262, 2210, 9641, 9641, 9641, 40420, 34410, 17919, 40829, 68515, 4574, 19153, 20998, 20998, 9821, 6740, 6740, 9823, 64407, 56851, 56851, 20956, 19155, 19156, 19156, 9831, 48619, 64071, 64071, 26514, 48623, 20958, 67174, 67017, 66225, 64299, 59886, 10260, 6050, 68276, 10262, 10263, 68671, 10264, 68473, 64935, 60828, 60828, 68205, 157, 157, 10310, 19282, 59838, 59333, 59415, 59409, 43269, 43269, 59262, 44903, 56128, 49154, 30609, 30609, 49167, 49167, 65419, 49174, 187, 4728, 58956, 68139, 3911, 4211, 4211, 24757, 24757, 22801, 22801, 56194, 56180, 56180, 56180, 49201, 10500, 42340, 68657, 61534, 61535, 2736, 67326, 10669, 56852, 56641, 10853, 10857, 10858, 250, 68047, 58481, 57840, 57840, 58412, 58981, 58981, 65777, 49507, 49507, 6795, 6795, 6795, 56298, 66855, 42199, 45577, 45758, 45758, 49516, 49548, 49548, 43533, 43533, 61124, 60763, 60763, 11117, 56549, 56549, 62749, 67809, 49657, 32977, 32977, 33568, 58485, 57918, 42577, 42577, 45756, 45756, 3586, 58866, 64297, 63153, 63153, 49828, 49829, 39263, 42632, 52042, 52043, 52060, 63041, 46818, 46818, 60178, 56989, 59518, 44628, 60180, 60180, 67655, 67954, 67656, 32128, 49975, 65601, 37894, 37895, 63781, 63782, 66017, 66017, 56400, 11751, 11751, 4737, 1161, 30167, 55950, 55950, 11981, 11982, 11982, 50237, 60669, 56766, 3547, 3547, 3546, 50355, 50355, 58834, 3597, 3595, 3596, 2224, 61334, 41520, 41520, 68258, 2461, 40901, 3591, 12493, 3590, 3590, 3590, 12528, 50556, 56196, 56533, 56368, 64009, 64010, 3592, 67659, 56649, 56649, 59066, 56793, 64034, 64966, 64966, 65067, 64149, 59222, 59222, 50857, 63101, 50859, 50912, 50912, 59591, 59591, 66331, 13030, 13030, 13030, 13030, 13030, 68051, 50975, 50975, 50978, 67531, 59442, 59442, 60389, 57898, 57898, 56714, 67554, 40406, 51001, 51001, 44960, 51006, 44859, 13092, 46790, 51011, 57828, 68668, 58599, 58053, 66757, 58600, 5802, 57482, 57483, 68292, 54754, 54754, 3692, 3692, 60913, 58835, 51168, 67848, 58573, 64122, 68319, 62848, 63812, 51225, 51227, 51227, 67534, 51274, 26954, 408, 59738, 64171, 48829, 48829, 48830, 58761, 22978, 62983, 62983, 65150, 65647, 59128, 26762, 59023, 27366, 10061, 66858, 68438, 68438, 63489, 63464, 1413, 58805, 48648, 48649, 66754, 66755, 21854, 3641, 3640, 3640, 3642, 3645, 3643, 3643, 3644, 14575, 3687, 57807, 51778, 62793, 14625, 19229, 21045, 21046, 21047, 21047, 2155, 2156, 4866, 4866, 5269, 67301, 67302, 63171, 2242, 63554, 63091, 66452, 27050, 27050, 14777, 52028, 6943, 2262, 52029, 19875, 38595, 38595, 52076, 61087, 58184, 56491, 56491, 56964, 56964, 63921, 6762, 68279, 63705, 60409, 59660, 62579, 37146, 52216, 68267, 68268, 46442, 46442, 58831, 48888, 48888, 66369, 60344, 67114, 59683, 59683, 33149, 4740, 52329, 52332, 15311, 15311, 22089, 15312, 22090, 6066, 15313, 61987, 60018, 66588, 59781, 49362, 64428, 55218, 49370, 66981, 66981, 39064, 66534, 66535, 59771, 62936, 62847, 62847, 31131, 31131, 6959, 6959, 19961, 19961, 6434, 58878, 52601, 59732, 68642, 15637, 613, 613, 614, 614, 59532, 67675, 57451, 42884, 50563, 50334, 60712, 55213, 41830, 50567, 61007, 56767, 62317, 62317, 33181, 33181, 67417, 67417, 28412, 28412, 52688, 52688, 33015, 66576, 44373, 64295, 34802, 34802, 34802, 34802, 58624, 15780, 15780, 6521, 15781, 59651, 59870, 56735, 58482, 57564, 57564, 57564, 57383, 66386, 1547, 62326, 67377, 64965, 68270, 58830, 52834, 52843, 33402, 35952, 40483, 45841, 22215, 15932, 7162, 7162, 6760, 6760, 67786, 67786, 3648, 6745, 6745, 67556, 15948, 63685, 60852, 52988, 52988, 66405, 56704, 40117, 63345, 59674, 55359, 21073, 22815, 55862, 55069, 16208, 16217, 16217, 18335, 65671, 28678, 28679, 23467, 16344, 16344, 24774, 53201, 4014, 4014, 36333, 35667, 35667, 59736, 63915, 53239, 41378, 41378, 41378, 41379, 41379, 3675, 55762, 68514, 63377, 3626, 16634, 16634, 16635, 20109, 20109, 57047, 48950, 26526, 754, 753, 16855, 755, 64936, 64936, 64936, 59416, 30318, 30318, 16875, 16875, 16875, 61229, 60742, 22448, 36924, 53560, 60268, 62783, 16904, 16904, 16904, 19263, 57043, 60454, 58438, 58438, 60155, 60155, 60155, 60773, 56569, 56569, 64311, 58698, 22477, 22477, 22479, 22479, 17007, 17007, 2484, 2484, 23031, 23032, 62840, 62604, 62604, 62605, 62201, 3620, 3620, 17125, 39533, 17290, 17290, 66445, 17310, 3931, 6258, 57292, 57292, 39835, 39835, 56972, 56972, 43486, 43486, 59605, 31494, 68620, 59164, 65580, 65370, 68539, 56687, 53900, 43171, 43171, 43172, 56761, 62227, 17403, 22975, 22975, 17434, 53976, 33082, 39189, 58759, 58759, 57697, 57156, 58171, 17639, 60437, 60437, 60438, 61772, 61772, 60182, 60182, 65307, 60676, 63332, 61313, 61313, 32456, 39216, 39114, 54182, 29841,];
        $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
        
        $subgrupoId = 346;
        $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);
        /** @var Subgrupo $subgrupo */
        $subgrupo = $repoSubgrupo->find($subgrupoId);
        
        $alterados = 0;
        $naoAlterados = 0;
        foreach ($codigos as $codigo) {
            /** @var Produto $produto */
            $produto = $repoProduto->findOneByCodigo($codigo);
            if (!$produto) {
                \Symfony\Component\VarDumper\VarDumper::dump('<br>Produto não encontrado para o código ' . $codigo);
                continue;
            }
            if ($produto->subgrupo->getId() == $subgrupoId) {
                $naoAlterados++;
                continue;
            }
            $produto->jsonData['subgrupo_anterior_id'] = $produto->subgrupo->getId();
            $produto->subgrupo = $subgrupo;
            $produto->grupo = $subgrupo->grupo;
            $produto->depto = $subgrupo->grupo->depto;
            $produto->jsonData['preco_sob_consulta'] = true;
            $produto = $produtoEntityHandler->save($produto);
            $this->integradorTray->integraProduto($produto, false, true);
            $alterados++;
        }
        
        return new Response("OK (Alterados: $alterados. Não alterados: $naoAlterados");
    }


}