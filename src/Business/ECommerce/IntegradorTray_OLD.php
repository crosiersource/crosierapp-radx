<?php


namespace CrosierSource\CrosierLibRadxBundle\Business\ECommerce;

use App\Entity\EcommIntegra\ClienteConfig;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Vendas\VendaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalItem;
use CrosierSource\CrosierLibRadxBundle\Entity\RH\Colaborador;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\PlanoPagto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaPagto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\DeptoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Fiscal\NotaFiscalRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\PlanoPagtoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Regras de negócio para a integração com a Tray.
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegradorTray_OLD implements IntegradorECommerce
{

    private Client $client;

    public string $endpoint;

    public string $accessToken;

    private Security $security;

    private ParameterBagInterface $params;

    private SyslogBusiness $syslog;

    private DeptoEntityHandler $deptoEntityHandler;

    private Connection $conn;

    private ProdutoEntityHandler $produtoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private ?array $deptosNaTray = null;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private VendaBusiness $vendaBusiness;

    private ClienteEntityHandler $clienteEntityHandler;

    private Depto $deptoIndefinido;
    private Grupo $grupoIndefinido;
    private Subgrupo $subgrupoIndefinido;
    private ?Fornecedor $fornecedorDefamilia = null;

    private ?int $carteiraIndefinidaId = null;

    private ?int $carteiraYapayId = null;

    public function __construct(Security               $security,
                                ParameterBagInterface  $params,
                                SyslogBusiness         $syslog,
                                DeptoEntityHandler     $deptoEntityHandler,
                                ProdutoEntityHandler   $produtoEntityHandler,
                                VendaEntityHandler     $vendaEntityHandler,
                                VendaItemEntityHandler $vendaItemEntityHandler,
                                NotaFiscalBusiness     $notaFiscalBusiness,
                                ClienteEntityHandler   $clienteEntityHandler,
                                VendaBusiness          $vendaBusiness
    )
    {
        $this->security = $security;
        $this->params = $params;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->conn = $deptoEntityHandler->getDoctrine()->getConnection();
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->notaFiscalBusiness = $notaFiscalBusiness;
        $this->vendaBusiness = $vendaBusiness;
        $this->client = new Client();

        /** @var DeptoRepository $repoDepto */
        $repoDepto = $this->produtoEntityHandler->getDoctrine()->getRepository(Depto::class);
        $this->deptoIndefinido = $repoDepto->findOneBy(['codigo' => '00']);

        /** @var GrupoRepository $repoGrupo */
        $repoGrupo = $this->produtoEntityHandler->getDoctrine()->getRepository(Grupo::class);
        $this->grupoIndefinido = $repoGrupo->findOneBy(['codigo' => '00']);

        /** @var SubgrupoRepository $repoSubgrupo */
        $repoSubgrupo = $this->produtoEntityHandler->getDoctrine()->getRepository(Subgrupo::class);
        $this->subgrupoIndefinido = $repoSubgrupo->findOneBy(['codigo' => '00']);

        /** @var FornecedorRepository $repoFornecedor */
        $repoFornecedor = $this->produtoEntityHandler->getDoctrine()->getRepository(Fornecedor::class);
        $this->fornecedorDefamilia = $repoFornecedor->findOneBy(['nome' => 'DEFAMILIA']);
    }

    /**
     * @param array $store
     * @throws ViewException
     */
    public function saveStoreConfig(array $store)
    {
        if (!$store['store_id']) {
            throw new ViewException('chave "store_id" n/d no array');
        }
        $rs = $this->conn->fetchAssociative('SELECT id, valor FROM cfg_app_config WHERE chave = :chave', ['chave' => 'tray.configs.json']);
        if ($rs['valor'] ?? false) {
            $stores = json_decode($rs['valor'], true);
            $achou = false;
            foreach ($stores as $k => $v) {
                if ($v['store_id'] === $store['store_id']) {
                    $stores[$k] = array_merge($v, $store);
                    $achou = true;
                    break;
                }
            }
            if (!$achou) {
                $stores[] = $store;
            }
            $this->conn->update('cfg_app_config', ['valor' => json_encode($stores)], ['id' => $rs['id']]);
            return $store;
            if (!$store) {
                throw new ViewException('storeId n/d em cfg_app_config.tray.configs.json');
            }
        } else {
            throw new ViewException('cfg_app_config.tray.configs.json n/d');
        }
    }

    public function getStores()
    {
        $rs = $this->conn->fetchAssociative('SELECT id, valor FROM cfg_app_config WHERE chave = :chave', ['chave' => 'tray.configs.json']);
        if ($rs['valor'] ?? false) {
            $stores = json_decode($rs['valor'], true);
            foreach ($stores as $store) {
                // já seta a chave cfg_app_config.id para poder salvar mais fácil 
                if (!($store['cfg_app_config.id'] ?? false)) {
                    $store['cfg_app_config.id'] = $rs['id'];
                    $store = $this->saveStoreConfig($store);
                }
            }
            return $stores;
        } else {
            throw new ViewException('cfg_app_config.tray.configs.json n/d');
        }
    }


    public function getStore(?string $storeId = null)
    {
        $stores = $this->getStores();
        $store = null;
        // se não passou pega o único por default
        if (!$storeId) {
            if (count($stores) > 1) {
                throw new ViewException('Diversas configs de lojas em cfg_app_config.tray.configs.json e storeId n/d');
            } else {
                $store = $stores[0];
            }
        } else {
            foreach ($stores as $k => $v) {
                if ($v['store_id'] === $storeId) {
                    $store = $v;
                }
            }
        }
        if (!$store) {
            throw new ViewException('storeId n/d em cfg_app_config.tray.configs.json');
        }
        return $store;

    }

    /**
     * @throws ViewException
     */
    public function handleAccessToken(array &$store): string
    {
        if (!($store['date_expiration_access_token'] ?? false) || DateTimeUtils::diffInMinutes(DateTimeUtils::parseDateStr($store['date_expiration_access_token']), new \DateTime()) < 60) {
            try {
                $this->syslog->info('Tray.renewAccessToken', $store['url_loja']);
                $store = $this->renewAccessToken($store);
            } catch (ViewException $e) {
                if ($e->getPrevious() instanceof ClientException && $e->getPrevious()->getResponse()->getStatusCode() === 401) {
                    $store['ativa'] = false;
                    $store = $this->saveStoreConfig($store);
                }
                throw new ViewException($e->getMessage(), 0, $e);
            }
        }
        return $store['access_token'];
    }


    public function renewAllAccessTokens(): void
    {
        $stores = $this->getStores();
        foreach ($stores as $store) {
            $this->renewAccessToken($store);
        }
    }


    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }


    public function autorizarApp(?string $storeId = null)
    {
        try {
            $store = $this->getStore($storeId);
            $url = $store['url_loja'] . 'web_api/auth';
            $response = $this->client->request('POST', $url, [
                'form_params' => [
                    'consumer_key' => $store['consumer_key'],
                    'consumer_secret' => $store['consumer_secret'],
                    'code' => $store['code'],
                ]
            ]);
            $bodyContents = $response->getBody()->getContents();
            $authInfo = json_decode($bodyContents, true);
            $store['access_token'] = $authInfo['access_token'];
            $store['refresh_token'] = $authInfo['refresh_token'];
            $store['date_expiration_access_token'] = $authInfo['date_expiration_access_token'];
            $store['date_expiration_refresh_token'] = $authInfo['date_expiration_refresh_token'];
            $store['date_activated'] = $authInfo['date_activated'];
            $this->saveStoreConfig($store);
            return $store;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro - autorizarAppByStoreId', 0, $e);
        }
    }


    public function renewAccessToken(?array $store = null): array
    {
        try {
            $store = $store ?? $this->getStore();

            $response = $this->client->request('GET', $store['url_loja'] . 'web_api/auth?refresh_token=' . $store['refresh_token']);
            $bodyContents = $response->getBody()->getContents();
            $authInfo = json_decode($bodyContents, true);

            $store['access_token'] = $authInfo['access_token'];
            $store['refresh_token'] = $authInfo['refresh_token'];
            $store['date_expiration_access_token'] = $authInfo['date_expiration_access_token'];
            $store['date_expiration_refresh_token'] = $authInfo['date_expiration_refresh_token'];
            $store['date_activated'] = $authInfo['date_activated'];
            $store = $this->saveStoreConfig($store);
            return $store;
        } catch (GuzzleException $e) {
            if ($e->getCode() === 401) {
                // tento reautorizar uma vez
                return $this->autorizarApp($store['store_id']);
                throw new ViewException('Erro: 401 - Unauthorized em renewAccessToken. É necessário reativar a loja.', 0, $e);
            }
            throw new ViewException('Erro - renewAccessTokenByStoreId', 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    public function integraCategoria(Depto $depto): int
    {
        $store = $this->getStore();
        $syslog_obs = 'depto = ' . $depto->nome . ' (' . $depto->getId() . ')';
        $this->syslog->debug('integraDepto - ini', $syslog_obs);
        $idDeptoTray = null;

        $url = $this->getEndpoint() . 'web_api/categories?access_token=' . $this->handleAccessToken($store) . '&name=' . $depto->nome;
        $response = $this->client->request('GET',
            $url);
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        $idDeptoTray = $json['Categories'][0]['Category']['id'] ?? null;


        if (!$idDeptoTray) {
            $this->syslog->info('integraDepto - não existe, enviando...', $syslog_obs);

            $url = $this->getEndpoint() . 'web_api/categories?access_token=' . $this->handleAccessToken($store);
            $response = $this->client->request('POST', $url, [
                'form_params' => [
                    'Category' => [
                        'name' => $depto->nome,
                    ]
                ]
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if ($json['message'] !== 'Created') {
                throw new ViewException('Erro ao criar categoria');
            }
            $idDeptoTray = $json['id'];
            $this->syslog->info('integraDepto - integrado', $syslog_obs);
        }
        if (!isset($depto->jsonData['ecommerce_id']) || $depto->jsonData['ecommerce_id'] !== $idDeptoTray) {
            $this->syslog->info('integraDepto - salvando json_data', $syslog_obs);
            $depto->jsonData['ecommerce_id'] = $idDeptoTray;
            $depto->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $depto->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
            $this->deptoEntityHandler->save($depto);
            $this->syslog->info('integraDepto - salvando json_data: OK', $syslog_obs);
        }

        return $idDeptoTray;
    }

    /**
     * @throws ViewException
     */
    public function integraProduto(Produto $produto): int
    {
        try {
            $syslog_obs = 'produto = ' . $produto->nome . ' (' . $produto->getId() . ')';
            $this->syslog->debug('integraProduto - ini', $syslog_obs);
            $arrProduct = [
                'Product' => [
//                    'category_id' => $produto->depto->jsonData['ecommerce_id'],
//                    'ean' => $produto->jsonData['ean'],
//                    'brand' => $produto->jsonData['marca'],
//                    'name' => $produto->nome,
//                    'title' => $produto->jsonData['titulo'],
//                    'description' => $produto->jsonData['descricao_produto'],
//                    'additional_message' => $produto->jsonData['caracteristicas'],
//                    "picture_source_1" => "https://49839.cdn.simplo7.net/static/49839/sku/panos-de-cera-pano-de-cera-kit-p-m-g-estampa-abelhas--p-1619746505558.jpg",
//                    "picture_source_2" => "https://49839.cdn.simplo7.net/static/49839/sku/panos-de-cera-pano-de-cera-kit-p-m-g-estampa-abelhas--p-1619746502208.jpg",
//                    'available' => $produto->status === 'ATIVO' ? 1 : 0,
//                    'has_variation' => 0,
//                    'hot' => 1,
//                    'price' => 10,
//                    'weight' => 20,
                    'stock' => 9,
                ],
            ];
            $jsonRequest = json_encode($arrProduct, JSON_UNESCAPED_SLASHES);
            $url = $this->getEndpoint() . 'web_api/products?access_token=' . $this->handleAccessToken($store);
            $method = 'POST';
            if ($produto->jsonData['ecommerce_id'] ?? false) {
                //$arrProduto['id'] = $produto->jsonData['ecommerce_id'];
                $url = $this->getEndpoint() . 'web_api/products/' . $produto->jsonData['ecommerce_id'] . '?access_token=' . $this->handleAccessToken($store);
                $method = 'PUT';
            }
            $response = $this->client->request($method, $url, [
                'form_params' => $arrProduct
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Created', 'Saved'], true)) {
                throw new ViewException('Erro ao criar produto');
            }
            $idProdutoTray = $json['id'];
            $this->syslog->info('integraProduto - integrado', $syslog_obs);
            $this->syslog->info('integraProduto - salvando json_data', $syslog_obs);
            $produto->jsonData['ecommerce_id'] = $idProdutoTray;
            $produto->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $produto->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
            $this->produtoEntityHandler->save($produto);
            $this->syslog->info('integraProduto - salvando json_data: OK', $syslog_obs);
            return $idProdutoTray;
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }

    /**
     * @throws ViewException
     */
    public function integraVariacaoProduto(Produto $produto): int
    {
        try {
            $syslog_obs = 'produto = ' . $produto->nome . ' (' . $produto->getId() . ')';
            $this->syslog->debug('integraProduto - ini', $syslog_obs);
            $variacao = '102';
            $arrVariant = [
                'Variant' => [
                    'product_id' => $produto->jsonData['ecommerce_id'],
                    'ean' => $produto->jsonData['ean'] . '_' . $variacao,
                    "picture_source_1" => "https://49839.cdn.simplo7.net/static/49839/sku/160453730076346.jpg",
                    "picture_source_2" => "https://49839.cdn.simplo7.net/static/49839/sku/160453730095911.jpg",
                    'price' => 18,
                    'stock' => 999,
                    'weight' => 321,
                    'Sku' => [
                        ['type' => 'Tamanho', 'value' => 102],
                    ]
                ],
            ];
            $jsonRequest = json_encode($arrVariant, JSON_UNESCAPED_SLASHES);
            $url = $this->getEndpoint() . 'web_api/products/variants?access_token=' . $this->handleAccessToken($store);
            $method = 'POST';
            if ($produto->jsonData['ecommerce_item_id'] ?? false) {
                //$arrProduto['id'] = $produto->jsonData['ecommerce_id'];
                $url = $this->getEndpoint() . 'web_api/products/variants/' . $produto->jsonData['ecommerce_item_id'] . '?access_token=' . $this->handleAccessToken($store);
                $method = 'PUT';
            }
            $response = $this->client->request($method, $url, [
                'form_params' => $arrVariant
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Created', 'Saved'], true)) {
                throw new ViewException('Erro ao criar produto');
            }
            $idVariantTray = $json['id'];
            $this->syslog->info('integraProduto - integrado', $syslog_obs);
            $this->syslog->info('integraProduto - salvando json_data', $syslog_obs);
            $produto->jsonData['ecommerce_item_id'] = $idVariantTray;
            $produto->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $produto->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
            $this->produtoEntityHandler->save($produto);
            $this->syslog->info('integraProduto - salvando json_data: OK', $syslog_obs);
            return $idVariantTray;
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }

    public function obterCliente($idClienteECommerce)
    {
        // TODO: Implement obterCliente() method.
    }

    public function obterPedido(string $numPedido): array
    {
        $store = $this->getStore();
        $this->urlLoja = $store['url_loja'];
        $url = $store['url_loja'] . 'web_api/orders/' . $numPedido . '/complete?access_token=' . $this->handleAccessToken($store);
        $response = $this->client->request('GET', $url);
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json;
    }


    public function obterVendas(\DateTime $dtAPartirDe, ?bool $resalvar = false): int
    {
        $store = $this->getStore();
        $accessToken = $this->handleAccessToken($store);
        $url = $store['url_loja'] . 'web_api/orders?limit=50&access_token=' . $accessToken .
            '&modified=' . $dtAPartirDe->format('Y-m-d');
        $response = $this->client->request('GET', $url);
        $bodyContents = $response->getBody()->getContents();
        $result = json_decode($bodyContents, true);
        $totalPaginas = ceil($result['paging']['total'] / 50);
        $pedidos = $result['Orders'];
        for ($i = 2; $i <= $totalPaginas; $i++) {
            $urlProxPagina = $url . '&page=' . $i;
            $response = $this->client->request('GET', $urlProxPagina);
            $bodyContents = $response->getBody()->getContents();
            $result = json_decode($bodyContents, true);
            $pedidos = array_merge($pedidos, $result['Orders']);
        }

        if ($pedidos ?? false) {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
            foreach ($pedidos as $pedido) {

                $url = $store['url_loja'] . 'web_api/orders/' . $pedido['Order']['id'] . '/complete?access_token=' . $accessToken;
                $response = $this->client->request('GET', $url);
                $bodyContents = $response->getBody()->getContents();
                $jsonPedido = json_decode($bodyContents, true);

                $conn->beginTransaction();
                try {
                    $jsonPedido['codigo_loja_tray'] = $store['store_id'];
                    $this->integrarVendaParaCrosier($jsonPedido, $resalvar);
                    $conn->commit();
                } catch (\Throwable $e) {
                    try {
                        if ($conn->isTransactionActive()) {
                            $conn->rollBack();
                        }
                    } catch (ConnectionException $e) {
                        throw new \RuntimeException('rollback err', 0, $e);
                    }
                    $msg = ExceptionUtils::treatException($e) ?? 'Erro n/d';
                    $msg .= ' - Pedido ' . ($pedido['Wspedido']['numero'] ?? '????');
                    throw new ViewException('Erro ao integrar (' . $msg . ')');
                }
            }
        }
        return count($pedidos);
    }

    public function obterVendasPorData(\DateTime $dtVenda)
    {
        // TODO: Implement obterVendasPorData() method.
    }


    /**
     * @param array $pedido
     * @param bool|null $resalvar
     * @throws ViewException
     */
    private function integrarVendaParaCrosier(array $jsonPedido, ?bool $resalvar = false): void
    {
        try {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();

            $pedido = $jsonPedido['Order'];
            $customer = $pedido['Customer'];
            $statuss = $pedido['OrderStatus'];
            $itens = $pedido['ProductsSold'];


            $dtPedido = DateTimeUtils::parseDateStr($pedido['date'] . ' ' . $pedido['hour']);

            $this->syslog->info('Integrando pedido ' . $pedido['id'] . ' de ' .
                $dtPedido->format('d/m/Y H:i:s') . ' Cliente: ' . $customer['name']);


            $venda = $conn->fetchAllAssociative('SELECT * FROM ven_venda WHERE json_data->>"$.ecommerce_loja" = :ecommerce_loja AND json_data->>"$.ecommerce_idPedido" = :ecommerce_idPedido',
                [
                    'ecommerce_loja' => $jsonPedido['codigo_loja_tray'],
                    'ecommerce_idPedido' => $pedido['id'],
                ]);
            $venda = $venda[0] ?? null;
            if ($venda) {
                // se já existe, só confere o status
                // O único status que pode ser alterado no sentido Simplo7 -> Crosier é quando está em 'Aguardando Pagamento'
                $vendaJsonData = json_decode($venda['json_data'], true);
                if (($vendaJsonData['ecommerce_status_descricao'] === 'Criado') &&
                    (($vendaJsonData['ecommerce_status'] ?? null) != $statuss['id'])) {

                    $vendaJsonData['ecommerce_status'] = $statuss['id'];
                    $vendaJsonData['ecommerce_status_descricao'] = $statuss['status'];
                    $venda_['json_data'] = json_encode($vendaJsonData);
                    try {
                        $conn->update('ven_venda', $venda_, ['id' => $venda['id']]);
                    } catch (\Exception $e) {
                        throw new ViewException('Erro ao alterar status da venda. (ecommerce_idPedido = ' . $pedido['id'] . ')');
                    }
                }

                // Se não estiver pedindo para resalvar as informações novamente (o que irá sobreescrever quaisquer alterações), já retorna...
                if (!$resalvar) {
                    return;
                }

                try {
                    $conn->delete('ven_venda_item', ['venda_id' => $venda['id']]);
                } catch (\Throwable $e) {
                    $erro = 'Erro ao deletar itens da venda (id = "' . $venda['id'] . ')';
                    $this->syslog->err($erro);
                    throw new \RuntimeException($erro);
                }

                try {
                    $conn->executeQuery('DELETE FROM fin_fatura WHERE json_data->>"$.venda_id" = :venda_id', ['venda_id' => $venda['id']]);
                } catch (\Throwable $e) {
                    $erro = 'Erro ao deletar itens da venda (id = "' . $venda['id'] . ')';
                    $this->syslog->err($erro);
                    throw new \RuntimeException($erro);
                }

                /** @var VendaRepository $repoVenda */
                $repoVenda = $this->vendaEntityHandler->getDoctrine()->getRepository(Venda::class);
                $venda = $repoVenda->find($venda['id']);

            } else {
                $venda = new Venda();
            }

            $venda->dtVenda = $dtPedido;

            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->vendaEntityHandler->getDoctrine()->getRepository(Colaborador::class);
            $vendedorNaoIdentificado = $repoColaborador->findOneBy(['cpf' => '99999999999']);
            $venda->vendedor = $vendedorNaoIdentificado;

            $venda->status = 'PV ABERTO';

            $cliente = $conn->fetchAllAssociative('SELECT id FROM crm_cliente WHERE documento = :documento',
                ['documento' => $customer['cpf']]);
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->vendaEntityHandler->getDoctrine()->getRepository(Cliente::class);
            if ($cliente[0]['id'] ?? false) {
                $cliente = $repoCliente->find($cliente[0]['id']);
            } else {
                $cliente = null;
            }

            if (!$cliente || $resalvar) {

                $cliente = $cliente ?? new Cliente();

                $cliente->documento = $customer['cpf'];
                $cliente->nome = $customer['name'];
                $cliente->jsonData['tipo_pessoa'] = strlen($customer['cpf']) === 11 ? 'PF' : 'PJ';
                $cliente->jsonData['rg'] = '';
                $cliente->jsonData['dtNascimento'] = $customer['birth_date'];
                $cliente->jsonData['sexo'] = $customer['gender'] === '0' ? 'M' : 'F';
                $cliente->jsonData['nome_fantasia'] = '';
                $cliente->jsonData['inscricao_estadual'] = $customer['state_inscription'] ?? '';

                $cliente->jsonData['fone1'] = $customer['phone'] ?? '';
                $cliente->jsonData['fone2'] = $customer['cellphone'] ?? '';

                $cliente->jsonData['email'] = $customer['email'];
                $cliente->jsonData['canal'] = 'ECOMMERCE';
                $cliente->jsonData['ecommerce_id'] = $customer['id'];

                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            // Verifica os endereços do cliente
            $enderecoJaSalvo = false;
            if (($cliente->jsonData['enderecos'] ?? false) && count($cliente->jsonData['enderecos']) > 0) {
                foreach ($cliente->jsonData['enderecos'] as $endereco) {
                    if ((($endereco['tipo'] ?? '') === 'ENTREGA,FATURAMENTO') &&
                        (($endereco['logradouro'] ?? '') === $customer['address']) &&
                        (($endereco['numero'] ?? '') === $customer['number']) &&
                        (($endereco['complemento'] ?? '') === $customer['complement']) &&
                        (($endereco['bairro'] ?? '') === $customer['neighborhood']) &&
                        (($endereco['cep'] ?? '') === $customer['zip_code']) &&
                        (($endereco['cidade'] ?? '') === $customer['city']) &&
                        (($endereco['estado'] ?? '') === $customer['state'])) {
                        $enderecoJaSalvo = true;
                    }
                }
            }
            if (!$enderecoJaSalvo) {
                $cliente->jsonData['enderecos'][] = [
                    'tipo' => 'ENTREGA,FATURAMENTO',
                    'logradouro' => $customer['address'],
                    'numero' => $customer['number'],
                    'complemento' => $customer['complement'],
                    'bairro' => $customer['neighborhood'],
                    'cep' => $customer['zip_code'],
                    'cidade' => $customer['city'],
                    'estado' => $customer['state'],
                ];
                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            $venda->cliente = $cliente;

            $venda->jsonData['canal'] = 'ECOMMERCE';
            $venda->jsonData['ecommerce_loja'] = $jsonPedido['codigo_loja_tray'];
            $venda->jsonData['ecommerce_idPedido'] = $pedido['id'];
            $venda->jsonData['ecommerce_numeroPedido'] = $pedido['id'] ?? 'n/d';
            $venda->jsonData['ecommerce_status'] = $statuss['id'];
            $venda->jsonData['ecommerce_status_descricao'] = $statuss['status'];

            $obs = [];

            $venda->jsonData['ecommerce_entrega_tipo'] = $pedido['shipment'] ?? '';
            $venda->jsonData['ecommerce_entrega_integrador'] = $pedido['shipment_integrator'] ?? '';
            $venda->jsonData['ecommerce_entrega_retirarNaLoja'] = '';
            $venda->jsonData['ecommerce_entrega_frete_calculado'] = $pedido['shipment_value'] ?? '0.00';
            $venda->jsonData['ecommerce_entrega_frete_real'] = 0.00;

            $enderecosDoCliente = $customer['CustomerAddresses'] ?? [];
            foreach ($enderecosDoCliente as $enderecoDoCliente) {
                if ($enderecoDoCliente['CustomerAddress']['type_delivery'] ?? '0' === '1') {
                    $enderecoDoCliente = $enderecoDoCliente['CustomerAddress'];
                    $venda->jsonData['ecommerce_entrega_logradouro'] = $enderecoDoCliente['address'] ?? '';
                    $venda->jsonData['ecommerce_entrega_numero'] = $enderecoDoCliente['number'] ?? '';
                    $venda->jsonData['ecommerce_entrega_complemento'] = $enderecoDoCliente['complement'] ?? '';
                    $venda->jsonData['ecommerce_entrega_bairro'] = $enderecoDoCliente['neighborhood'] ?? '';
                    $venda->jsonData['ecommerce_entrega_cidade'] = $enderecoDoCliente['city'] ?? '';
                    $venda->jsonData['ecommerce_entrega_uf'] = $enderecoDoCliente['state'] ?? '';
                    $venda->jsonData['ecommerce_entrega_cep'] = $enderecoDoCliente['zip_code'] ?? '';
                }
            }


            $obs[] = 'IP: ';
            $obs[] = 'Pagamento: ' . $pedido['payment_method'] ?? '';
            $obs[] = 'Envio: ' . $pedido['shipment'];
            if ($pedido['sending_code'] ?? false) {
                $obs[] = 'Rastreio: ' . $pedido['sending_code'];
            }

            $venda->jsonData['obs'] = implode(PHP_EOL, $obs);

            $venda->subtotal = 0.0;// a ser recalculado posteriormente
            $venda->desconto = 0.0;// a ser recalculado posteriormente
            $venda->valorTotal = 0.0;// a ser recalculado posteriormente

            $valorSemFrete = bcsub($pedido['total'], $pedido['shipment_value'], 2);
            $totalProdutos = $pedido['partial_total'];
            $descontoCupom = bcsub($totalProdutos, $valorSemFrete, 2);

            $descontoTotal = bcadd($descontoCupom, bcadd(($pedido['discount'] ?? 0), ($pedido['cart_additional_values_discount'] ?? 0), 2), 2);
            $totalProdutos = 0.0;
            foreach ($itens as $item) {
                $item = $item['ProductsSold'];
                $totalProdutos = bcadd($totalProdutos, $item['price'], 2);
            }
            $pDesconto = bcdiv($descontoTotal, $totalProdutos, 8);

            // Salvo aqui para poder pegar o id
            $this->vendaEntityHandler->save($venda);

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);
            $ordem = 1;
            $i = 0;
            $descontoAcum = 0.0;
            $vendaItem = null;
            foreach ($itens as $item) {
                $item = $item['ProductsSold'];
                /** @var Produto $produto */
                $produto = null;
                try {
                    $sProduto = $conn->fetchAssociative(
                        'SELECT id FROM est_produto WHERE codigo = :codigo',
                        [
                            'codigo' => $item['reference'],
                        ]);
                    $item['codigo_loja_tray'] = $jsonPedido['codigo_loja_tray'];
                    if (!isset($sProduto['id'])) {
                        $sProduto = $this->obterProduto($item);
                    }
                    $produto = $repoProduto->find($sProduto['id']);
                } catch (\Throwable $e) {
                    throw new ViewException('Erro ao integrar venda. Erro ao pesquisar produto (idProduto = ' . $item['product_id'] . ')');
                }

                $vendaItem = new VendaItem();
                $venda->addItem($vendaItem);
                $vendaItem->descricao = $produto->nome;
                $vendaItem->ordem = $ordem++;
                $vendaItem->devolucao = false;

                $vendaItem->unidade = $produto->unidadePadrao;

                $vendaItem->precoVenda = $item['original_price'];
                $vendaItem->qtde = $item['quantity'];
                $vendaItem->subtotal = bcmul($vendaItem->precoVenda, $vendaItem->qtde, 2);
                // Para arredondar para cima
                $vendaItem->desconto = DecimalUtils::round(bcmul($pDesconto, $vendaItem->subtotal, 3));
                $descontoAcum = (float)bcadd($descontoAcum, $vendaItem->desconto, 2);
                $vendaItem->produto = $produto;

                $vendaItem->jsonData['ecommerce_idItemVenda'] = $item['id'];
                $vendaItem->jsonData['ecommerce_codigo'] = $produto->codigo;

                $this->vendaItemEntityHandler->save($vendaItem);
                $i++;
            }
            if ((float)$descontoTotal !== (float)$descontoAcum) {
                $diff = $descontoTotal - $descontoAcum;
                $vendaItem->desconto = bcadd($vendaItem->desconto, $diff, 2);
                $this->vendaItemEntityHandler->save($vendaItem);
            }
            $venda->recalcularTotais();
            // aqui é onde entram descontos de cupons (que é um desconto aplicado globalmente na venda)
            // TODO: verificar na tray como está funcionando isto...
            if ($pedido['total_descontos'] ?? false) {
                $venda->desconto = bcadd($venda->desconto, $pedido['total_descontos'], 2);
                $venda->valorTotal = bcsub($venda->subtotal, $venda->desconto, 2);
            }


            try {
                $conn->delete('ven_venda_pagto', ['venda_id' => $venda->getId()]);
            } catch (\Throwable $e) {
                $erro = 'Erro ao deletar pagtos da venda (id = "' . $venda['id'] . ')';
                $this->syslog->err($erro);
                throw new \RuntimeException($erro);
            }


            /** @var PlanoPagtoRepository $repoPlanoPagto */
            $repoPlanoPagto = $this->vendaEntityHandler->getDoctrine()->getRepository(PlanoPagto::class);
            $arrPlanosPagtosByCodigo = $repoPlanoPagto->arrayByCodigo();

            // Pega a $venda->valorTotal pois ali já constará os possíveis descontos
            $totalPedido = bcadd($venda->valorTotal, $pedido['shipment_value'], 2);

            // O total_pedido pode conter os acréscimos no caso de parcelamentos com qtde de parcelas acima do limite de parcelas sem juros.
            $venda->jsonData['total_pagtos'] = $totalPedido;


            if (!(in_array($venda->jsonData['ecommerce_status_descricao'], ['?????', 'CANCELADO AUT', 'CANCELADO'], true))) {

                $tipoFormaPagamento = mb_strtoupper($pedido['payment_method']);

                $carteiraId = null;

                if (strpos($tipoFormaPagamento, 'YAPAY') !== FALSE) {
                    $carteiraId = $this->getCarteiraYapay($jsonPedido['codigo_loja_tray']);
                    $integrador = 'YAPAY';
                } elseif ($tipoFormaPagamento === 'PIX') {
                    $carteiraId = $this->getCarteiraIndefinidaId();
                    $integrador = $tipoFormaPagamento;
                } else {
                    throw new ViewException('Integrador não configurado: ' . $tipoFormaPagamento . ' (Venda: ' . $pedido['id'] . ')');
                }

                // Não seta o pagamento para pedidos ainda não pagos ou cancelados

                $modoId = null;
                $descricaoPlanoPagto = null;
                $planoPagto = null;
                if (strpos($tipoFormaPagamento, 'CARTÃO') !== FALSE) {
                    $planoPagto = $arrPlanosPagtosByCodigo['010'];
                } elseif ($tipoFormaPagamento === 'PIX') {
                    $planoPagto = $arrPlanosPagtosByCodigo['040'];
                } elseif (strpos($tipoFormaPagamento, 'BOLETO') !== FALSE) {
                    $planoPagto = $arrPlanosPagtosByCodigo['030'];
                } else {
                    $planoPagto = $arrPlanosPagtosByCodigo['999'];
                }
                $descricaoPlanoPagto = $planoPagto['descricao'];
                $modoId = json_decode($planoPagto['json_data'], true)['modo_id'] ?? null;

                $vendaPagto = [
                    'plano_pagto_id' => $planoPagto['id'],
                    'venda_id' => $venda->getId(),
                    'valor_pagto' => $totalPedido,
                    'json_data' => [
                        'nomeFormaPagamento' => $tipoFormaPagamento ?? 'n/d',
                        'integrador' => $integrador,
                        'codigo_transacao' => $pedido['OrderTransactions'][0]['transaction_id'] ?? 'n/d',
                        'carteira_id' => $carteiraId,
                        'modo_id' => $modoId,
                    ],
                    'inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'updated' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'version' => 0,
                    'user_inserted_id' => 1,
                    'user_updated_id' => 1,
                    'estabelecimento_id' => 1
                ];


                $vendaPagto['json_data'] = json_encode($vendaPagto['json_data']);

                try {
                    $conn->insert('ven_venda_pagto', $vendaPagto);
                    $vendaPagtoId = $conn->lastInsertId();
                    $eVendaPagto = $this->vendaEntityHandler->getDoctrine()->getRepository(VendaPagto::class)->find($vendaPagtoId);
                    $venda->addPagto($eVendaPagto);
                    if ($integrador === 'Mercado Pago') { // na tray não está configurado
                        // no caso de pagamento via 'Mercado Pago', já busca as informações lá na API
                        $this->integradorMercadoPago->mlUser = 'defamiliapg@gmail.com';
                        $this->integradorMercadoPago->handleTransacaoParaVendaPagto($eVendaPagto);
                    }
                } catch (\Throwable $e) {
                    throw new ViewException('Erro ao salvar dados do pagamento');
                }


                $venda->jsonData['infoPagtos'] = $descricaoPlanoPagto .
                    ': R$ ' . number_format($pedido['total'], 2, ',', '.');
                if ($eVendaPagto->jsonData['codigo_transacao'] ?? false) {
                    $venda->jsonData['infoPagtos'] .= ' (Transação: ' . $eVendaPagto->jsonData['codigo_transacao'] . ')';
                }
                $venda->jsonData['forma_pagamento'] = $tipoFormaPagamento;
                $venda = $this->vendaEntityHandler->save($venda);
                $this->vendaBusiness->finalizarPV($venda);
            }

            $venda->jsonData['dados_completos_ecommerce'] = $jsonPedido;
            $venda = $this->vendaEntityHandler->save($venda);

        } catch (\Throwable $e) {
            $this->syslog->err('Erro ao integrarVendaParaCrosier', $pedido['id']);
            throw new ViewException('Erro ao integrarVendaParaCrosier', 0, $e);
        }
    }


    /**
     * @param array $item
     * @throws ViewException
     */
    public function obterProduto(array $item)
    {
        try {
            $store = $this->getStore();
            $accessToken = $this->handleAccessToken($store);

            try {
                $url = $store['url_loja'] . 'web_api/products/' . $item['product_id'] . '?access_token=' . $accessToken;
                $response = $this->client->request('GET', $url);
                $bodyContents = $response->getBody()->getContents();
                $jsonProduto = json_decode($bodyContents, true);
                $product = $jsonProduto['Product'];
            } catch (GuzzleException $e) {
                // Pode estar importando venda que tenha um produto já excluído
                if ($e->getCode() === 404) {
                    $this->syslog->info('Produto não encontrado (id: ' . $item['product_id'] . '). Continuando mesmo sem...');
                }
            }

            $produto = new Produto();
            $produto->nome = $item['name'];
            $produto->jsonData['dados_ecommerce'] = $product ?? null;
            $produto->jsonData['ecommerce_id'] = $item['product_id'];
            $produto->jsonData['ecommerce_loja'] = $item['codigo_loja_tray'];
            // nos casos onde tem variação, a referência vem com o prefixo no produto e o sufixo na variação
            // o mais certo seria ir buscar em https://{api_address}/products/variants/
            // mas como o item já manda a correta, pego de lá
            $produto->codigo = $item['reference'] ?? '';
            $produto->status = ($product['available'] ?? false) ? 'ATIVO' : 'INATIVO';
            // TODO: corrigir para colocar no depto/grupo/subgrupo correto
            $produto->depto = $this->deptoIndefinido;
            $produto->grupo = $this->grupoIndefinido;
            $produto->subgrupo = $this->subgrupoIndefinido;
            $produto->fornecedor = $this->fornecedorDefamilia;

            $this->produtoEntityHandler->save($produto);
            return ['id' => $produto->getId()];
        } catch (GuzzleException $e) {
            throw new ViewException('Erro ao obterProduto');
        }
    }

    public function reintegrarVendaParaCrosier(Venda $venda)
    {

    }

    public function integrarVendaParaECommerce(Venda $venda)
    {
        // TODO: Implement integrarVendaParaECommerce() method.
    }

    public function integrarDadosFiscaisNoPedido(int $numPedido)
    {
        try {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
            $existe = $conn->fetchAssociative('SELECT nf.id FROM fis_nf nf WHERE nf.json_data->>"$.num_pedido_tray" = :numPedido', ['numPedido' => $numPedido]);
            if (!$existe) {
                throw new ViewException('Nota Fiscal não encontrada para este pedido');
            }

            /** @var NotaFiscalRepository $repoNotaFiscal */
            $repoNotaFiscal = $this->vendaEntityHandler->getDoctrine()->getRepository(NotaFiscal::class);
            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $repoNotaFiscal->find($existe['id']);

            $store = $this->getStore();
            $url = $store['url_loja'] . 'web_api/orders/' . $numPedido . '/invoices?access_token=' . $this->handleAccessToken($store);
            $arr = [
                'issue_date' => $notaFiscal->dtEmissao->format('Y-m-d'),
                'number' => $notaFiscal->numero,
                'serie' => $notaFiscal->serie,
                'value' => $notaFiscal->valorTotal,
                'key' => $notaFiscal->chaveAcesso,
                'xml_danfe' => $notaFiscal->getXMLDecodedAsString()
            ];
            $jsonRequest = json_encode($arr, JSON_UNESCAPED_SLASHES);
            $method = 'POST';
            $response = $this->client->request($method, $url, [
                'form_params' => $arr
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Created', 'Saved'], true)) {
                throw new ViewException('Erro - integrarVendaParaECommerce2');
            }
            return $json;
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }


    public function atualizaDadosEnvio(int $numPedido)
    {
        try {
            $url = $this->getEndpoint() . 'web_api/orders/' . $numPedido . '?access_token=' . $this->handleAccessToken($store);
            $arr = [
                'Order' => [
                    'status_id' => 124141,
                    'sending_date' => '2021-08-25',
                    'sending_code' => 'PY871797797BR',
                ]
            ];
            $jsonRequest = json_encode($arr, JSON_UNESCAPED_SLASHES);
            $method = 'PUT';
            $response = $this->client->request($method, $url, [
                'form_params' => $arr
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Created', 'Saved'], true)) {
                throw new ViewException('Erro ao criar produto');
            }
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }


    public function cancelarPedido(int $numPedido)
    {
        try {
            $url = $this->getEndpoint() . 'web_api/orders/cancel/' . $numPedido . '?access_token=' . $this->handleAccessToken($store);
            $method = 'PUT';
            $response = $this->client->request($method, $url);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Canceled'], true)) {
                throw new ViewException('Erro ao criar produto');
            }
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }


    /**
     * @param int $numPedido
     * @return int|null
     * @throws ViewException
     * @throws \Doctrine\DBAL\Exception
     */
    public function gerarNFeParaVenda(string $numPedido): ?int
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
        $existe = $conn->fetchAssociative('SELECT id FROM fis_nf WHERE json_data->>"$.num_pedido_tray" = :numPedido', ['numPedido' => $numPedido]);
        if ($existe) {
            return $existe['id'];
        }

        $arrPedido = $this->obterPedido($numPedido);

        $notaFiscal = new NotaFiscal();

        $notaFiscal->jsonData['num_pedido_tray'] = $numPedido;
        $notaFiscal->documentoEmitente = '34411048000104';
        $notaFiscal->tipoNotaFiscal = 'NFE';
        $notaFiscal->naturezaOperacao = 'VENDA';
        $notaFiscal->entradaSaida = 'S';
        $notaFiscal->dtEmissao = new \DateTime();
        $notaFiscal->dtSaiEnt = new \DateTime();
        $notaFiscal->transpModalidadeFrete = 'EMITENTE';
        $notaFiscal->documentoDestinatario = $arrPedido['Order']['Customer']['cpf'] ?? $arrPedido['Order']['Customer']['cnpj'];
        $notaFiscal->xNomeDestinatario = $arrPedido['Order']['Customer']['name'];
        $endereco = $arrPedido['Order']['Customer']['CustomerAddresses'][0]['CustomerAddress'];
        $notaFiscal->logradouroDestinatario = $endereco['address'];
        $notaFiscal->numeroDestinatario = $endereco['number'];
        $notaFiscal->complementoDestinatario = $endereco['complement'] ?? '';
        $notaFiscal->bairroDestinatario = $endereco['neighborhood'] ?? '';
        $notaFiscal->cidadeDestinatario = $endereco['city'];
        $notaFiscal->estadoDestinatario = $endereco['state'];
        $notaFiscal->cepDestinatario = $endereco['zip_code'];
        $notaFiscal->foneDestinatario = $arrPedido['Order']['Customer']['cellphone'] ?? $arrPedido['Order']['Customer']['phone'] ?? '';
        $notaFiscal->emailDestinatario = $arrPedido['Order']['Customer']['email'] ?? '';

        $notaFiscal->infoCompl = 'Envio: ' . $arrPedido['Order']['shipment_integrator'];
        $notaFiscal->infoCompl .= PHP_EOL . 'Pedido: ' . $numPedido;


        foreach ($arrPedido['Order']['ProductsSold'] as $rItem) {
            $item = $rItem['ProductsSold'];
            $notaFiscalItem = new NotaFiscalItem();
            $notaFiscalItem->codigo = $item['reference'];
            $notaFiscalItem->descricao = $item['original_name'];
            $notaFiscalItem->qtde = $item['quantity'];
            $notaFiscalItem->cfop = $endereco['state'] === 'PR' ? '5102' : '6102';
            $notaFiscalItem->csosn = 103;
            $notaFiscalItem->ncm = '63052000';
            $notaFiscalItem->unidade = 'UN';
            $notaFiscalItem->valorUnit = $item['price'];

            $notaFiscal->addItem($notaFiscalItem);
        }

        $this->notaFiscalBusiness->saveNotaFiscal($notaFiscal);

        return $notaFiscal->getId();
    }


    /**
     * @return string
     */
    public function getCarteiraIndefinidaId(): string
    {
        if (!$this->carteiraIndefinidaId) {
            try {
                $repoCarteira = $this->vendaEntityHandler->getDoctrine()->getRepository(Carteira::class);
                $this->carteiraIndefinidaId = $repoCarteira->findOneBy(['codigo' => 99])->getId();
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar - getCarteiraIndefinidaId');
            }
        }
        return $this->carteiraIndefinidaId;
    }

    /**
     * @return string
     */
    public function getCarteiraYapay(string $codigoLojaTray): string
    {
        if (!$this->carteiraYapayId) {
            try {
                $repoCarteira = $this->vendaEntityHandler->getDoctrine()->getRepository(Carteira::class);
                $this->carteiraYapayId = $repoCarteira->findOneBy(['descricao' => 'YAPAY ' . $codigoLojaTray])->getId();
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar - getCarteiraIndefinidaId');
            }
        }
        return $this->carteiraYapayId;
    }


}
