<?php


namespace App\Business\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringAssembler;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Vendas\VendaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use App\Entity\Ecommerce\ClienteConfig;
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
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\SubgrupoEntityHandler;
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
use Doctrine\DBAL\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Regras de negócio para a integração com a Tray.
 *
 * (como está classe ficou muito específica para a Conecta, foi movida do crosierlib-radx para cá).
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegradorTray implements IntegradorEcommerce
{

    private ?Client $client = null;

    public ?string $endpoint = null;

    public ?array $trayConfigs = null;

    private Security $security;

    private SyslogBusiness $syslog;

    private DeptoEntityHandler $deptoEntityHandler;
    private GrupoEntityHandler $grupoEntityHandler;
    private SubgrupoEntityHandler $subgrupoEntityHandler;

    private ProdutoEntityHandler $produtoEntityHandler;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private ?array $deptosNaTray = null;

    private VendaBusiness $vendaBusiness;

    private ClienteEntityHandler $clienteEntityHandler;

    private ?Depto $deptoIndefinido = null;
    private ?Grupo $grupoIndefinido = null;
    private ?Subgrupo $subgrupoIndefinido = null;

    private ?int $carteiraIndefinidaId = null;

    private ?int $carteiraYapayId = null;

    private ?string $accessToken = null;

    private ?int $delayEntreIntegracoesDeProduto = null;


    public function __construct(Security               $security,
                                ParameterBagInterface  $params,
                                SyslogBusiness         $syslog,
                                DeptoEntityHandler     $deptoEntityHandler,
                                GrupoEntityHandler     $grupoEntityHandler,
                                SubgrupoEntityHandler  $subgrupoEntityHandler,
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
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class)->setEcho(false);
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
        $this->conn = $deptoEntityHandler->getDoctrine()->getConnection();
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->notaFiscalBusiness = $notaFiscalBusiness;
        $this->vendaBusiness = $vendaBusiness;
        $this->client = new Client();
    }

    private function init()
    {
        if (!$this->deptoIndefinido) {
            /** @var DeptoRepository $repoDepto */
            $repoDepto = $this->produtoEntityHandler->getDoctrine()->getRepository(Depto::class);
            $this->deptoIndefinido = $repoDepto->findOneBy(['codigo' => '00']);
        }

        if (!$this->grupoIndefinido) {
            /** @var GrupoRepository $repoGrupo */
            $repoGrupo = $this->produtoEntityHandler->getDoctrine()->getRepository(Grupo::class);
            $this->grupoIndefinido = $repoGrupo->findOneBy(['codigo' => '00']);
        }

        if (!$this->subgrupoIndefinido) {
            /** @var SubgrupoRepository $repoSubgrupo */
            $repoSubgrupo = $this->produtoEntityHandler->getDoctrine()->getRepository(Subgrupo::class);
            $this->subgrupoIndefinido = $repoSubgrupo->findOneBy(['codigo' => '00']);
        }

        $this->loadTrayConfigs();
    }

    /**
     * Carrega as configurações para os casos onde a integração é 1x1 (diferente da Conecta).
     */
    private function loadTrayConfigs(): void
    {
        if ($this->trayConfigs) {
            return;
        }
        try {
            $conn = $this->deptoEntityHandler->getDoctrine()->getConnection();
            $r = $conn->fetchAssociative('SELECT id, valor FROM cfg_app_config WHERE app_uuid = :appUUID AND chave = :chave',
                [
                    'appUUID' => $_SERVER['CROSIERAPP_UUID'],
                    'chave' => 'tray.configs.json'
                ]);
            $rs = json_decode($r['valor'] ?? '{}', true);
            if ($rs) {
                $this->trayConfigs = $rs;
                if (!($this->trayConfigs['cfg_app_config.id'] ?? false)) {
                    $this->trayConfigs['cfg_app_config.id'] = $r['id'];
                    $conn->update('cfg_app_config',
                        [
                            'updated' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'valor' => json_encode($this->trayConfigs)
                        ], ['id' => $r['id']]);
                }
            }
        } catch (Exception $e) {
            throw new ViewException('Erro ao carregar as configurações de tray.configs.json');
        }
    }


    public function autorizarApp(?string $code = null, ?ClienteConfig $clienteConfig = null): array
    {
        $this->init();
        $urlLoja = $this->trayConfigs['url_loja'] ?? null;
        // Como a ativação aqui tbm serve para a arquitetura da Conecta, a url loja nesses casos é específica
        // por ClienteConfig
        if ($clienteConfig) {
            $urlLoja = $clienteConfig->jsonData['url_loja'];
        }

        $consumerKey = $this->trayConfigs['consumer_key'];
        $consumerSecret = $this->trayConfigs['consumer_secret'];
        $code = $code ?? $this->trayConfigs['code'];


        $url = $urlLoja . 'web_api/auth';
        $response = $this->client->request('POST', $url, [
            'form_params' => [
                'consumer_key' => $consumerKey,
                'consumer_secret' => $consumerSecret,
                'code' => $code,
            ]
        ]);

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);

        if ($urlLoja) {
            if (in_array($json['code'] ?? 0, [200, 201], true)) {
                if (!$clienteConfig) {
                    $this->trayConfigs['ativa'] = true;
                    $this->atualizarTrayConfigs($json);
                }
            }
        }

        return $json;
    }

    private function atualizarTrayConfigs(array $json)
    {
        try {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();

            $this->trayConfigs['access_token'] = $json['access_token'];
            $this->trayConfigs['refresh_token'] = $json['refresh_token'];
            $this->trayConfigs['date_expiration_access_token'] = $json['date_expiration_access_token'];
            $this->trayConfigs['date_expiration_refresh_token'] = $json['date_expiration_refresh_token'];
            $this->trayConfigs['date_activated'] = $json['date_activated'];

            $conn->update('cfg_app_config',
                ['valor' => json_encode($this->trayConfigs)],
                ['id' => $this->trayConfigs['cfg_app_config.id']],
            );
        } catch (Exception $e) {
            throw new ViewException('Erro ao atualizarTrayConfigs (appConfigId = ' . $appConfigId . ')');
        }
    }

    public function renewAccessToken(?string $refreshToken = null): array
    {
        try {
            $this->init();
            $refreshToken = $refreshToken ?? $this->trayConfigs['refresh_token'];
            $response = $this->client->request('GET', $this->getEndpoint() . 'web_api/auth?refresh_token=' . $refreshToken);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            // se estiver na arquitetura 1x1, já atualiza o tray.configs.json
            if ($this->trayConfigs['url_loja'] ?? null) {
                $this->atualizarTrayConfigs($json);
            }
            return $json;
        } catch (GuzzleException $e) {
            if ($e instanceof ClientException) {
                $msg = $e->getResponse()->getBody()->getContents();
            } else {
                $msg = ExceptionUtils::treatException($e);
            }
            throw new ViewException('Erro - renewAccessToken (' . $msg . ')', 0, $e);
        }
    }


    private function integraCategoriaTray(string $nome, string $codigo, string $slug, ?int $parentId = null, ?int $idTray = null)
    {
        try {
            $this->init();
            $url = $this->getEndpoint() . 'web_api/categories' . ($idTray ? '/' . $idTray : '') . '?access_token=' . $this->getAccessToken();
            $arr = [
                'form_params' => [
                    'Category' => [
                        'id' => $codigo,
                        'name' => $nome,
                        'slug' => $slug,
                        'parent_id' => $parentId,
                    ]
                ]
            ];
            $method = $idTray ? 'PUT' : 'POST';
            $response = $this->client->request($method, $url, $arr);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if ($method === 'POST' && $json['message'] !== 'Created') {
                throw new ViewException('Erro ao criar categoria');
            } elseif ($method === 'PUT' && $json['message'] !== 'Saved') {
                throw new ViewException('Erro ao alterar categoria');
            }
            return $json['id'];
        } catch (GuzzleException $e) {
            throw new ViewException($e->getResponse()->getBody()->getContents(), 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    public function integraSubgrupo(Subgrupo $subgrupo): int
    {
        $this->init();
        if (!($idDepto_ecommerce = ($subgrupo->grupo->depto->jsonData['ecommerce_id'] ?? false))) {
            $idDepto_ecommerce = $this->integraCategoriaTray(
                $subgrupo->grupo->depto->nome,
                $subgrupo->grupo->depto->codigo,
                mb_strtolower(str_replace(' ', '-', $subgrupo->grupo->depto->nome))
            );
            $subgrupo->grupo->depto->jsonData['ecommerce_id'] = $idDepto_ecommerce;
            $this->deptoEntityHandler->save($subgrupo->grupo->depto);
        }

        // Pois na tray os produtos podem às vezes ter apenas 2 níveis de categoria
        if ($subgrupo->grupo->nome === '<< NÃO INFORMADO >>') {
            return $idDepto_ecommerce;
        }

        if (!($idGrupo_ecommerce = ($subgrupo->grupo->jsonData['ecommerce_id'] ?? false))) {
            $idGrupo_ecommerce = $this->integraCategoriaTray(
                $subgrupo->grupo->nome,
                $subgrupo->grupo->codigo,
                mb_strtolower(str_replace(' ', '-', $subgrupo->grupo->depto->nome . '_' . $subgrupo->grupo->nome)),
                $idDepto_ecommerce);
            $subgrupo->grupo->jsonData['ecommerce_id'] = $idGrupo_ecommerce;
            $this->grupoEntityHandler->save($subgrupo->grupo);
        }

        // Pois na tray os produtos podem às vezes ter apenas 2 níveis de categoria
        if ($subgrupo->nome === '<< NÃO INFORMADO >>') {
            return $idGrupo_ecommerce;
        }

        if (!($idSubgrupo_ecommerce = ($subgrupo->jsonData['ecommerce_id'] ?? false))) {
            $idSubgrupo_ecommerce = $this->integraCategoriaTray(
                $subgrupo->nome,
                $subgrupo->codigo,
                mb_strtolower(str_replace(' ', '-', $subgrupo->grupo->depto->nome . '_' . $subgrupo->grupo->nome . '_' . $subgrupo->nome)),
                $idGrupo_ecommerce);
            $subgrupo->jsonData['ecommerce_id'] = $idSubgrupo_ecommerce;
            $this->subgrupoEntityHandler->save($subgrupo);
        }

        return $idSubgrupo_ecommerce;
    }


    /**
     * @throws ViewException
     */
    public function alteraSubgrupo(Subgrupo $subgrupo): void
    {
        $this->init();
        $idGrupo_ecommerce = $subgrupo->grupo->jsonData['ecommerce_id'];
        $idSubgrupo_ecommerce = $this->integraCategoriaTray(
            $subgrupo->nome,
            $subgrupo->codigo,
            mb_strtolower(str_replace(' ', '-', $subgrupo->grupo->depto->nome . '_' . $subgrupo->grupo->nome . '_' . $subgrupo->nome)),
            $idGrupo_ecommerce, $subgrupo->jsonData['ecommerce_id']);

    }


    /**
     * @throws ViewException
     */
    public function alteraGrupo(Grupo $grupo): void
    {
        $this->init();
        $idDepto_ecommerce = $grupo->depto->jsonData['ecommerce_id'];
        $idGrupo_ecommerce = $this->integraCategoriaTray(
            $grupo->nome,
            $grupo->codigo,
            mb_strtolower(str_replace(' ', '-', $grupo->depto->nome . '_' . $grupo->nome)),
            $idDepto_ecommerce, $grupo->jsonData['ecommerce_id']);
    }


    /**
     * @throws ViewException
     */
    public function alteraDepto(Depto $depto): void
    {
        $this->init();
        $idGrupo_ecommerce = $this->integraCategoriaTray(
            $depto->nome,
            $depto->codigo,
            mb_strtolower(str_replace(' ', '-', $depto->nome)),
            null, $depto->jsonData['ecommerce_id']);
    }


    /**
     * @throws ViewException
     */
    public function integraCategoria(Produto $produto): int
    {
        $this->init();
        if (!($idDepto_ecommerce = ($produto->depto->jsonData['ecommerce_id'] ?? false))) {
            $idDepto_ecommerce = $this->integraCategoriaTray(
                $produto->depto->nome,
                $produto->depto->codigo,
                mb_strtolower(str_replace(' ', '-', $produto->depto->nome)),
            );
            $produto->depto->jsonData['ecommerce_id'] = $idDepto_ecommerce;
            $this->deptoEntityHandler->save($produto->depto);
        }

        if (!($idGrupo_ecommerce = ($produto->grupo->jsonData['ecommerce_id'] ?? false))) {
            $idGrupo_ecommerce = $this->integraCategoriaTray(
                $produto->grupo->nome,
                $produto->depto->codigo . $produto->grupo->codigo,
                mb_strtolower(str_replace(' ', '-', $produto->depto->nome . '_' . $produto->grupo->nome)),
                $idDepto_ecommerce);
            $produto->grupo->jsonData['ecommerce_id'] = $idGrupo_ecommerce;
            $this->grupoEntityHandler->save($produto->grupo);
        }

        // Pois na tray os produtos podem às vezes ter apenas 2 níveis de categoria
        if ($produto->subgrupo->nome === '<< NÃO INFORMADO >>') {
            return $idGrupo_ecommerce;
        }

        if (!($idSubgrupo_ecommerce = ($produto->subgrupo->jsonData['ecommerce_id'] ?? false))) {
            $idSubgrupo_ecommerce = $this->integraCategoriaTray(
                $produto->subgrupo->nome,
                $produto->depto->codigo . $produto->grupo->codigo . $produto->subgrupo->codigo,
                mb_strtolower(str_replace(' ', '-', $produto->depto->nome . '_' . $produto->grupo->nome . '_' . $produto->subgrupo->nome)),
                $idGrupo_ecommerce);
            $produto->subgrupo->jsonData['ecommerce_id'] = $idSubgrupo_ecommerce;
            $this->subgrupoEntityHandler->save($produto->subgrupo);
        }

        return $idSubgrupo_ecommerce;
    }


    private function selectMarcas()
    {
        try {
            $cache = new FilesystemAdapter('integrador_tray.cache', 600, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            return $cache->get('select_marcas', function (ItemInterface $item) {
                $temResults = true;
                $page = 1;
                $rs = [];
                while ($temResults) {
                    $url = $this->getEndpoint() . 'web_api/products/brands/?limit=50&access_token=' . $this->getAccessToken() . '&page=' . $page;
                    $method = 'GET';
                    $response = $this->client->request($method, $url);
                    $bodyContents = $response->getBody()->getContents();
                    $json = json_decode($bodyContents, true);

                    if (count($json['Brands'] ?? []) > 0) {
                        $rs = array_merge($rs, $json['Brands']);
                        $page++;
                    } else {
                        $temResults = false;
                    }
                }
                return $rs;
            });
        } catch (\Exception $e) {
            throw new ViewException('Erro ao buscar marcas na tray', 0, $e);
        }
    }


    /**
     * @param string $marca
     * @return int
     * @throws ViewException
     */
    public function integraMarca(string $marca): int
    {
        try {
            $marca = mb_strtoupper(trim($marca));
            $rsMarcas = $this->selectMarcas();
            foreach ($rsMarcas as $rMarca) {
                if (mb_strtoupper(trim($rMarca['Brand']['brand'])) === $marca) {
                    return (int)$rMarca['Brand']['id'];
                }
            }// else...
            $this->syslog->info('integraMarca: ini', 'marca = ' . $marca);
            $url = $this->getEndpoint() . 'web_api/brands?access_token=' . $this->getAccessToken();
            $method = 'POST';
            $arr = [
                'Brand' => [
                    'slug' => mb_strtolower((new StringAssembler([$marca]))->kebab()),
                    'brand' => $marca,
                ]
            ];
            $response = $this->client->request($method, $url, [
                'form_params' => $arr
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            if (!in_array($json['message'], ['Created', 'Saved'], true)) {
                throw new ViewException('Erro ao criar marca');
            }
            $cache = new FilesystemAdapter('integrador_tray.cache', 600, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            $cache->clear('select_marcas');
            return $json['id'];
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            throw new ViewException('Erro ao integrar a marca: ' . $marca . ' (' . $msg . ')');
        }
    }

    /**
     * @throws ViewException
     */
    public function integraProduto(Produto $produto, ?bool $integrarImagens = true, ?bool $respeitarDelay = false): void
    {
        // Ainda só funciona para a arquitetura 1x1
        try {
            if (!$produto->marca) {
                throw new ViewException('É necessário informar a marca do produto para realizar a integração');
            }
            
            $this->init();
            $syslog_obs = 'produto = ' . $produto->nome . ' (' . $produto->getId() . ')';
            $this->syslog->debug('integraProduto - ini', $syslog_obs);

            if ($respeitarDelay) {
                if ($this->getDelayEntreIntegracoesDeProduto()) {
                    $this->syslog->info('integraProduto - delay de ' . $this->getDelayEntreIntegracoesDeProduto(), $syslog_obs);
                    sleep($this->getDelayEntreIntegracoesDeProduto());
                } else {
                    $this->syslog->info('integraProduto - sem delay entre integrações');
                }
            }

            $start = microtime(true);

            $idMarca_ecommerce = $this->integraMarca($produto->marca ?? '');

            $idSubgrupo_ecommerce = $this->integraCategoria($produto);

            $preco = 0;
            $precoPromocional = null;

            $sobConsulta = $produto->jsonData['preco_sob_consulta'] ?? false;
            
            if (!$sobConsulta) {
                if ((float)($produto->jsonData['preco_ecommerce'] ?? 0) > 0) {
                    $preco = (float)$produto->jsonData['preco_ecommerce'];
                } else if ((float)($produto->jsonData['preco_tabela'] ?? 0) > 0) {
                    $preco = (float)$produto->jsonData['preco_tabela'];
                    if ((float)($produto->jsonData['preco_promocao'] ?? 0) > 0) {
                        $precoPromocional = (float)$produto->jsonData['preco_promocao'];
                    } elseif ((float)($produto->jsonData['preco_venda_com_desconto'] ?? 0) > 0) {
                        $precoPromocional = (float)$produto->jsonData['preco_venda_com_desconto'];
                    }
                }
                if ($preco <= 0) {
                    throw new ViewException('Não é possível integrar ao e-commerce produto sem preço');
                }
            }


            $arrProduct = [
                'Product' => [
                    'category_id' => $idSubgrupo_ecommerce,
                    'ean' => $produto->ean,
                    'available' => $produto->status === 'ATIVO' ? 1 : 0,
                    'brand' => $produto->marca,
                    'name' => $produto->nome,
                    'reference' => $produto->codigo,
//                    'title' => $produto->jsonData['titulo'],
//                    'description' => $produto->jsonData['descricao_produto'],
//                    'additional_message' => $produto->jsonData['caracteristicas'],
//                    "picture_source_1" => "https://49839.cdn.simplo7.net/static/49839/sku/panos-de-cera-pano-de-cera-kit-p-m-g-estampa-abelhas--p-1619746505558.jpg",
//                    "picture_source_2" => "https://49839.cdn.simplo7.net/static/49839/sku/panos-de-cera-pano-de-cera-kit-p-m-g-estampa-abelhas--p-1619746502208.jpg",
                    'available' => $produto->status === 'ATIVO' ? 1 : 0,
//                    'has_variation' => 0,
//                    'hot' => 1,
                    'price' => $preco,
                    'promotional_price' => 0,
                    'start_promotion' => null,
                    'end_promotion' => null,
                    'cost_price' => $produto->jsonData['preco_custo'],
//                    'weight' => 20,
                    'stock' => $produto->qtdeTotal,
                ],
            ];
            if (!$sobConsulta && $precoPromocional && ($preco !== $precoPromocional)) {
                $ontem = DateTimeUtils::addDays(new \DateTime(), -1);
                $arrProduct['Product']['promotional_price'] = $precoPromocional;
                $arrProduct['Product']['start_promotion'] = $ontem->format('Y-m-d');
                $arrProduct['Product']['end_promotion'] = '2040-12-01';
            }

            $url = $this->getEndpoint() . 'web_api/products?access_token=' . $this->getAccessToken();
            $method = 'POST';
            if ($produto->jsonData['ecommerce_id'] ?? false) {
                //$arrProduto['id'] = $produto->jsonData['ecommerce_id'];
                $url = $this->getEndpoint() . 'web_api/products/' . $produto->jsonData['ecommerce_id'] . '?access_token=' . $this->getAccessToken();
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
            $agora = (new \DateTime());
            $this->syslog->info('integraProduto - integrado', $syslog_obs);
            $this->syslog->info('integraProduto - salvando json_data', $syslog_obs);
            $produto->jsonData['ecommerce_id'] = $idProdutoTray;
            $produto->jsonData['ecommerce_integr_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
            $produto->jsonData['ecommerce_desatualizado'] = 'N';
            $produto->ecommerce = true;
            $produto->dtUltIntegracaoEcommerce = $agora;
            $this->produtoEntityHandler->save($produto);

            $tt = (microtime(true) - $start);
            $this->syslog->info('integraProduto - OK (em ' . $tt . ' ms)', $syslog_obs);

            $this->syslog->info('integraProduto - salvando json_data: OK', $syslog_obs);

        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            throw new ViewException('Erro ao integrar produto na tray (Id: ' . $produto->getId() . '): ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array $item
     * @throws ViewException
     */
    public function obterProduto(array $item)
    {
        // Ainda só funciona para a arquitetura 1x1
        try {
            try {
                $url = $this->getEndpoint() . 'web_api/products/' . $item['product_id'] . '?access_token=' . $this->getAccessToken();
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

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);
            
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
            $produto->ncm = $repoProduto->getNcmPadrao();
            

            /** @var FornecedorRepository $repoFornecedor */
            $repoFornecedor = $this->produtoEntityHandler->getDoctrine()->getRepository(Fornecedor::class);

            $produto->fornecedor = $repoFornecedor->findOneBy(['nome' => 'DEFAMILIA']);

            $this->produtoEntityHandler->save($produto);
            return ['id' => $produto->getId()];
        } catch (GuzzleException $e) {
            throw new ViewException('Erro ao obterProduto');
        }
    }

    /**
     * @throws ViewException
     */
    public function integraVariacaoProduto(Produto $produto): int
    {
        // Ainda só funciona para a arquitetura 1x1
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
            $url = $this->getEndpoint() . 'web_api/products/variants?access_token=' . $this->getAccessToken();
            $method = 'POST';
            if ($produto->jsonData['ecommerce_item_id'] ?? false) {
                //$arrProduto['id'] = $produto->jsonData['ecommerce_id'];
                $url = $this->getEndpoint() . 'web_api/products/variants/' . $produto->jsonData['ecommerce_item_id'] . '?access_token=' . $this->getAccessToken();
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


    /**
     * Obtém as vendas somente da data
     *
     * @param \DateTime $dtApartirDe
     * @param bool|null $resalvar
     * @return int
     * @throws Exception
     * @throws GuzzleException
     * @throws ViewException
     */
    public function obterVendas(\DateTime $dtApartirDe, ?bool $resalvar = false): int
    {
        $this->init();
        $url = $this->getEndpoint() . 'web_api/orders?limit=50&access_token=' . $this->getAccessToken() .
            '&modified=' . $dtApartirDe->format('Y-m-d');
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

                $url = $this->getEndpoint() . 'web_api/orders/' . $pedido['Order']['id'] . '/complete?access_token=' . $this->getAccessToken();
                $response = $this->client->request('GET', $url);
                $bodyContents = $response->getBody()->getContents();
                $jsonPedido = json_decode($bodyContents, true);

                try {
                    $jsonPedido['codigo_loja_tray'] = $this->trayConfigs['store_id'];
                    $this->integrarVendaParaCrosier($jsonPedido, $resalvar);
                } catch (\Throwable $e) {
                    $msg = ExceptionUtils::treatException($e) ?? 'Erro n/d';
                    $msg .= ' - Pedido ' . ($pedido['Wspedido']['numero'] ?? '????');
                    $this->syslog->err('Erro ao integrar (' . $msg . ')');
                    continue;
                }
            }
        }
        return count($pedidos);
    }

    public function obterVendasPorData(\DateTime $dtVenda)
    {
        // TODO: Implement obterVendasPorData() method.
    }

    public function obterCliente($idClienteEcommerce)
    {
        // TODO: Implement obterCliente() method.
    }

    public function reintegrarVendaParaCrosier(Venda $venda)
    {
        // TODO: Implement reintegrarVendaParaCrosier() method.
    }


    public function obterPedidoDoEcommerce(int $numPedido)
    {
        $this->init();
        $url = $this->getEndpoint() . 'web_api/orders/' . $numPedido . '/complete?access_token=' . $this->getAccessToken();
        $response = $this->client->request('GET', $url);
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        $json['codigo_loja_tray'] = $this->trayConfigs['store_id'];
        return $json;
    }


    public function atualizaDadosEnvio(int $numPedido)
    {
        try {
            $url = $this->getEndpoint() . 'web_api/orders/' . $numPedido . '?access_token=' . $this->getAccessToken();
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
            $url = $this->getEndpoint() . 'web_api/orders/cancel/' . $numPedido . '?access_token=' . $this->getAccessToken();
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

        $arrPedido = $this->obterPedidoDoEcommerce($numPedido);

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


    public function integrarDadosFiscaisNoPedido(int $numPedido)
    {
        try {
            $this->init();
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
            $existe = $conn->fetchAssociative('SELECT nf.id FROM fis_nf nf WHERE nf.json_data->>"$.num_pedido_tray" = :numPedido', ['numPedido' => $numPedido]);
            if (!$existe) {
                throw new ViewException('Nota Fiscal não encontrada para este pedido');
            }

            /** @var NotaFiscalRepository $repoNotaFiscal */
            $repoNotaFiscal = $this->vendaEntityHandler->getDoctrine()->getRepository(NotaFiscal::class);
            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $repoNotaFiscal->find($existe['id']);

            $url = $this->getEndpoint() . 'web_api/orders/' . $numPedido . '/invoices?access_token=' . $this->getAccessToken();
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
                throw new ViewException('Erro - integrarVendaParaEcommerce2');
            }
            return $json;
        } catch (GuzzleException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }


    /**
     * @param array $pedido
     * @param bool|null $resalvar
     * @throws ViewException
     */
    private function integrarVendaParaCrosier(array $jsonPedido, ?bool $resalvar = false): void
    {
        try {
            $this->init();

            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
            $conn->beginTransaction();

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
                    $conn->rollBack();
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

            $venda->jsonData['dados_completos_ecommerce'] = $jsonPedido;

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
                    $msg = ExceptionUtils::treatException($e);
                    throw new ViewException('Erro ao integrar venda. Erro ao pesquisar produto (idProduto = ' . $item['product_id'] . ') [' . $msg . ']');
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
                $erro = 'Erro ao deletar pagtos da venda (id = "' . $venda->getId() . ')';
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

                if ((strpos($tipoFormaPagamento, 'YAPAY') !== FALSE) || 
                    (strpos($tipoFormaPagamento, 'VINDI') !== FALSE)) {
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


            $venda = $this->vendaEntityHandler->save($venda);

            $conn->commit();

        } catch (\Throwable $e) {
            if ($conn && $conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    $this->syslog->err('Erro ao rollback');
                }
            }
            $this->syslog->err('Erro ao integrarVendaParaCrosier', $pedido['id']);
            throw new ViewException('Erro ao integrarVendaParaCrosier', 0, $e);
        }
    }


    /**
     * @param Venda $venda
     * @return \SimpleXMLElement|null
     */
    public function integrarVendaParaEcommerce(Venda $venda)
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
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint ?? $this->trayConfigs['url_loja'];
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        $this->init();
        if ($this->accessToken) {
            return $this->accessToken;
        } else {
            if (!($this->trayConfigs['date_expiration_access_token'] ?? false) || DateTimeUtils::diffInMinutes(DateTimeUtils::parseDateStr($this->trayConfigs['date_expiration_access_token']), new \DateTime()) < 60) {
                $this->renewAccessToken();
            }
            return $this->trayConfigs['access_token'];
        }
    }

    public function apagarCategorias()
    {
        $this->init();
        $temResults = true;
        $page = 1;
        $rs = [];
        while ($temResults) {
            $url = $this->getEndpoint() . 'web_api/categories/?limit=50&access_token=' . $this->getAccessToken() . '&page=' . $page;
            $method = 'GET';
            $response = $this->client->request($method, $url);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);

            if (count($json['Categories'] ?? []) > 0) {
                $rs = array_merge($rs, $json['Categories']);
                $page++;
            } else {
                $temResults = false;
            }
        }

        foreach ($rs as $r) {
            try {
                $url = $this->getEndpoint() . 'web_api/categories/' . $r['Category']['id'] . '?access_token=' . $this->getAccessToken();
                $method = 'DELETE';
                $response = $this->client->request($method, $url);
                $bodyContents = $response->getBody()->getContents();
                $json = json_decode($bodyContents, true);
            } catch (GuzzleException $e) {
                $f = $e;
            }
        }

    }


    /**
     * @return string
     */
    public function getDelayEntreIntegracoesDeProduto(): string
    {
        if ($this->delayEntreIntegracoesDeProduto === null) {
            try {
                $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
                $rs = $conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                    [
                        'chave' => 'ecomm_info_delay_entre_integracoes_de_produto',
                        'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                    ]);
                $this->delayEntreIntegracoesDeProduto = (int)($rs['valor'] ?? 1);
            } catch (\Throwable $e) {
                $this->syslog->err('Erro ao pesquisar valor para "ecomm_info_delay_entre_integracoes_de_produto". Default para 0');
                $this->delayEntreIntegracoesDeProduto = 0;
            }
        }
        return $this->delayEntreIntegracoesDeProduto;
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
                throw new \RuntimeException('Erro ao pesquisar - getCarteiraYapay');
            }
        }
        return $this->carteiraYapayId;
    }


    public function consultaProduto(Produto $produto): array
    {
        $this->init();
        $url = $this->getEndpoint() . 'web_api/products/' . $produto->jsonData['ecommerce_id'] . '&access_token=' . $this->getAccessToken();
        $method = 'GET';
        $response = $this->client->request($method, $url);
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json;
    }


}
