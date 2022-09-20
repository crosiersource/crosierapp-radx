<?php

namespace App\Business\Ecommerce;

use App\Entity\Ecommerce\ClienteConfig;
use App\Entity\Ecommerce\TrayVenda;
use App\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use App\EntityHandler\Ecommerce\TrayVendaEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\PushMessageEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayBusiness
{

    private Client $client;

    private ClienteConfigEntityHandler $clienteConfigEntityHandler;

    private TrayVendaEntityHandler $trayVendaEntityHandler;

    private IntegradorTray $integradorTray;

    private PushMessageEntityHandler $pushMessageEntityHandler;

    private SyslogBusiness $syslog;

    public function __construct(ClienteConfigEntityHandler $clienteConfigEntityHandler,
                                TrayVendaEntityHandler     $trayVendaEntityHandler,
                                IntegradorTray             $integradorTray,
                                SyslogBusiness             $syslog,
                                PushMessageEntityHandler   $pushMessageEntityHandler
    )
    {
        $this->client = new Client();
        $this->clienteConfigEntityHandler = $clienteConfigEntityHandler;
        $this->trayVendaEntityHandler = $trayVendaEntityHandler;
        $this->integradorTray = $integradorTray;
        $this->pushMessageEntityHandler = $pushMessageEntityHandler;
        $this->syslog = $syslog->setApp('conecta')->setComponent(self::class);
    }

    /**
     * @throws ViewException
     */
    public function autorizarApp(ClienteConfig $clienteConfig): void
    {
        $this->syslog->info('Tray.autorizarApp', $clienteConfig->jsonData['url_loja']);
        $r = $this->integradorTray->autorizarApp($clienteConfig->jsonData['tray']['code'], $clienteConfig);
        $this->saveAuthInfo($clienteConfig, $r);
    }


    public function handleAccessToken(ClienteConfig $clienteConfig): void
    {
        if (!$clienteConfig->trayDtExpAccessToken || DateTimeUtils::diffInMinutes($clienteConfig->trayDtExpAccessToken, new \DateTime()) < 60) {
            try {
                $this->integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
                $this->syslog->info('Tray.handleAccessToken', $clienteConfig->jsonData['url_loja']);
                $rs = $this->integradorTray->renewAccessToken($clienteConfig->jsonData['tray']['refresh_token']);
                $this->saveAuthInfo($clienteConfig, $rs);
            } catch (ViewException $e) {
                $this->syslog->err('Erro no handleAccessToken', $e->getMessage());
                if ($e->getPrevious() instanceof ClientException && $e->getPrevious()->getResponse()->getStatusCode() === 401) {
                    $this->desativandoCliente($clienteConfig);
                }
            }
        }
    }

    public function renewAccessToken(ClienteConfig $clienteConfig): void
    {
        try {
            $this->integradorTray->endpoint = $clienteConfig->jsonData['url_loja'];
            $this->syslog->info('Tray.renewAccessToken', $clienteConfig->jsonData['url_loja']);
            $rs = $this->integradorTray->renewAccessToken($clienteConfig->jsonData['tray']['refresh_token']);
            $this->saveAuthInfo($clienteConfig, $rs);
        } catch (ViewException $e) {
            $this->syslog->err('Erro no renewAccessToken', $e->getMessage());
            throw $e;
        }
    }


    /**
     * @throws ViewException
     */
    private function saveAuthInfo(ClienteConfig $clienteConfig, array $authInfo)
    {
        if (!in_array((int)($authInfo['code'] ?? 0), [200, 201], true)) {
            throw new ViewException('Erro ao executar operação');
        }
        $clienteConfig->jsonData['tray']['access_token'] = $authInfo['access_token'];
        $clienteConfig->jsonData['tray']['refresh_token'] = $authInfo['refresh_token'];
        $clienteConfig->trayDtExpAccessToken = DateTimeUtils::parseDateStr($authInfo['date_expiration_access_token']);
        $clienteConfig->jsonData['tray']['dt_exp_refresh_token'] = $authInfo['date_expiration_refresh_token'];
        $clienteConfig->jsonData['tray']['date_activated'] = $authInfo['date_activated'];

        $clienteConfig->ativo = true;
        $clienteConfig->jsonData['dt_desativado'] = null;
        $clienteConfig->jsonData['tentativas_antes_de_desativar'] = 0;

        $this->clienteConfigEntityHandler->save($clienteConfig);
    }


    /**
     * @throws ViewException
     */
    private function obterVendasPorClienteConfig(ClienteConfig $clienteConfig): ?array
    {
        try {
            $this->syslog->info('Tray.obterVendasPorClienteConfig - INI', $clienteConfig->jsonData['url_loja']);
            $this->handleAccessToken($clienteConfig);
            $endpoint = $clienteConfig->jsonData['url_loja'] . 'web_api/orders?limit=50&access_token=' . $clienteConfig->jsonData['tray']['access_token'];
            $rs = [];
            $temResults = true;
            $page = 1;
            $dtStart = $clienteConfig->jsonData['tray']['pedidos_integrados_ate'] ?? '1970-01-01';
            while ($temResults) {
                $response = $this->client->request('GET',
                    $endpoint . '&modified=' . $dtStart .
                    '&page=' . $page);
                $bodyContents = $response->getBody()->getContents();
                $r = json_decode($bodyContents, true);
                if (count($r['Orders']) > 0) {
                    $rs = array_merge($rs, $r['Orders']);
                    $page++;
                } else {
                    $temResults = false;
                }
            }
            // Refazer todas as consultas pegando os dados completos do pedido
            foreach ($rs as $k => $r) {
                $endpoint = $clienteConfig->jsonData['url_loja'] . 'web_api/orders/' . $r['Order']['id'] . '/complete?access_token=' . $clienteConfig->jsonData['tray']['access_token'];
                $response = $this->client->request('GET', $endpoint);
                $bodyContents = $response->getBody()->getContents();
                $r = json_decode($bodyContents, true);
                $rs[$k] = $r;
            }
            $this->syslog->info('Tray.obterVendasPorClienteConfig - RS: ' . count($rs), $clienteConfig->jsonData['url_loja']);
            return $rs;
        } catch (GuzzleException $e) {
            if (strpos($e->getHandlerContext()['error'] ?? '', 'Could not resolve host') !== FALSE) {
                $this->syslog->err('Tray.obterVendasPorClienteConfig - (Could not resolve host) ', $clienteConfig->jsonData['url_loja']);
                // $this->desativandoCliente($clienteConfig);
            } else {
                $msg = ExceptionUtils::treatException($e);
                $this->syslog->err('Tray.obterVendasPorClienteConfig - Erro para ' . $clienteConfig->jsonData['url_loja'], $msg);
            }
        }
        return null;
    }

    /**
     * @throws ViewException
     */
    private function desativandoCliente(ClienteConfig $clienteConfig)
    {
        $maxTentativas = 30; // TODO: colocar no cfg_app_config ?
        if ($clienteConfig->jsonData['tentativas_antes_de_desativar'] ?? false) {
            $tentativas = $clienteConfig->jsonData['tentativas_antes_de_desativar'];
            if ($tentativas >= $maxTentativas) {
                $this->syslog->err('Desativando cliente na tray: ' . $clienteConfig->jsonData['url_loja']);
                $clienteConfig->ativo = false;
                $clienteConfig->jsonData['dt_desativado'] = (new \DateTime())->format('d/m/Y H:i');
                $this->pushMessageEntityHandler
                    ->enviarMensagemParaLista(
                        "Atenção! " .
                        $clienteConfig->cliente->nome .
                        " foi desconectado por falta de acesso a Tray.",
                        "CLIENTES_DESCONECTADOS");
            } else {
                $this->syslog->info('Ainda não vou desativar... apenas ' . $tentativas . ' tentativa(s) de ' . $maxTentativas);
            }
        }
        $tentativas = ($clienteConfig->jsonData['tentativas_antes_de_desativar'] ?? 0) + 1;
        $clienteConfig->jsonData['tentativas_antes_de_desativar'] = $tentativas;
        $this->clienteConfigEntityHandler->save($clienteConfig);
    }


    /**
     * @throws ViewException
     */
    public function atualizar()
    {
        $this->syslog->info('Tray.obterVendasGlobal - INI');
        $clienteConfigs = $this->clienteConfigEntityHandler->getDoctrine()
            ->getRepository(ClienteConfig::class)->findByAtivo(true);

        foreach ($clienteConfigs as $clienteConfig) {
            if ($clienteConfig->jsonData['tray']['access_token'] ?? false) {
                $q = $this->atualizarCliente($clienteConfig);
                if ($q) {
                    $this->pushMessageEntityHandler
                        ->enviarMensagemParaLista(
                            $q . " venda(s) obtida(s) para " .
                            $clienteConfig->cliente->nome,
                            "NOVAS_VENDAS");
                }
            }
        }
    }

    /**
     * @throws ViewException
     */
    public function atualizarCliente(ClienteConfig $clienteConfig)
    {
        $this->syslog->info('Tray.atualizarCliente - INI');

        $repoTrayVenda = $this->clienteConfigEntityHandler->getDoctrine()->getRepository(TrayVenda::class);
        $q = 0;
        if ($clienteConfig->jsonData['tray']['access_token'] ?? false) {
            $rsVendas = $this->obterVendasPorClienteConfig($clienteConfig);
            if ($rsVendas) {
                foreach ($rsVendas as $rVenda) {
                    $trayVenda = $repoTrayVenda->findOneByFiltersSimpl([
                        ['clienteConfig', 'EQ', $clienteConfig],
                        ['idTray', 'EQ', $rVenda['Order']['id']]
                    ]);
                    if (!$trayVenda) {
                        $trayVenda = new TrayVenda();
                        $q++;
                    }
                    $trayVenda->jsonData['result_tray'] = $rVenda;
                    $trayVenda->clienteConfig = $clienteConfig;
                    $trayVenda->valorTotal = $rVenda['Order']['total'];
                    $trayVenda->clienteId = $rVenda['Order']['Customer']['id'];
                    $trayVenda->clienteNome = $rVenda['Order']['Customer']['name'];
                    $trayVenda->dtVenda = DateTimeUtils::parseDateStr($rVenda['Order']['date'] . ' ' . $rVenda['Order']['hour']);
                    $trayVenda->idTray = $rVenda['Order']['id'];
                    $trayVenda->statusTray = $rVenda['Order']['status'];
                    $trayVenda->pointSale = $rVenda['Order']['point_sale'];
                    $this->trayVendaEntityHandler->save($trayVenda);
                }
                $clienteConfig->jsonData['tray']['pedidos_integrados_ate'] = (new \DateTime())->format('Y-m-d');
                $this->syslog->info('Tray.obterVendasGlobal - pedidos_integrados_ate: ' .
                    $clienteConfig->jsonData['tray']['pedidos_integrados_ate'], $clienteConfig->jsonData['url_loja']);
                $this->clienteConfigEntityHandler->save($clienteConfig);
            }
        }
        return $q;
    }

}