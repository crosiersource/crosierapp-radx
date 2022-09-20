<?php


namespace App\Business\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
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
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\PlanoPagtoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Doctrine\DBAL\ConnectionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Regras de negócio para a integração com a Simplo7.
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegradorSimplo7 implements IntegradorEcommerce
{

    private Client $client;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private ?string $chave = null;

    private ?string $endpoint = null;

    private ?int $caixaSiteCarteiraId = null;

    private ?int $carteiraMercadoPagoSiteId = null;

    private ?int $carteiraIndefinidaId = null;

    private Depto $deptoIndefinido;
    private Grupo $grupoIndefinido;
    private Subgrupo $subgrupoIndefinido;
    private Fornecedor $fornecedorDefamilia;

    private Security $security;

    private ProdutoEntityHandler $produtoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private ClienteEntityHandler $clienteEntityHandler;

    private ParameterBagInterface $params;

    private MessageBusInterface $bus;

    private SyslogBusiness $syslog;
    
    private VendaBusiness $vendaBusiness;

    private IntegradorMercadoPago $integradorMercadoPago;

    
    public function __construct(AppConfigEntityHandler $appConfigEntityHandler,
                                Security $security,
                                ProdutoEntityHandler $produtoEntityHandler,
                                VendaEntityHandler $vendaEntityHandler,
                                VendaItemEntityHandler $vendaItemEntityHandler,
                                NotaFiscalBusiness $notaFiscalBusiness,
                                VendaBusiness $vendaBusiness,
                                ClienteEntityHandler $clienteEntityHandler,
                                ParameterBagInterface $params,
                                MessageBusInterface $bus,
                                SyslogBusiness $syslog,
                                IntegradorMercadoPago $integradorMercadoPago)
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->notaFiscalBusiness = $notaFiscalBusiness;
        $this->vendaBusiness = $vendaBusiness;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->params = $params;
        $this->bus = $bus;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->integradorMercadoPago = $integradorMercadoPago;
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
     * @return string
     */
    public function getChave(): string
    {
        if (!$this->chave) {
            try {
                $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $rs */
                $rs = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_SIMPLO7_chave'], ['appUUID', 'EQ', $_SERVER['CROSIERAPPRADX_UUID']]]);
                $this->chave = $rs->valor;
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao instanciar IntegradorSimplo7 (chave ecomm_info_integra_SIMPLO7_chave ?)');
            }
        }
        return $this->chave;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        if (!$this->endpoint) {
            try {
                $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $rs */
                $rs = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_SIMPLO7_endpoint'], ['appUUID', 'EQ', $_SERVER['CROSIERAPPRADX_UUID']]]);
                $this->endpoint = $rs->valor;
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar appConfig ecomm_info_integra_SIMPLO7_endpoint');
            }
        }
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getCaixaSiteCarteiraId(): string
    {
        if (!$this->caixaSiteCarteiraId) {
            try {
                $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $rs */
                $rs = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_caixa_site_carteira_id'], ['appUUID', 'EQ', $_SERVER['CROSIERAPPRADX_UUID']]]);
                $this->caixaSiteCarteiraId = (int)$rs->valor;
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar appConfig ecomm_info_caixa_site_carteira_id');
            }
        }
        return $this->caixaSiteCarteiraId;
    }


    /**
     * @return string
     */
    public function getCarteiraMercadoPagoSiteId(): string
    {
        // 
        if (!$this->carteiraMercadoPagoSiteId) {
            try {
                $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $rs */
                $rs = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_mercadopago_site_carteira_id'], ['appUUID', 'EQ', $_SERVER['CROSIERAPPRADX_UUID']]]);
                $this->carteiraMercadoPagoSiteId = (int)$rs->valor;
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar appConfig ecomm_info_caixa_site_carteira_id');
            }
        }
        return $this->carteiraMercadoPagoSiteId;
    }


    /**
     * @return string
     */
    public function getCarteiraIndefinidaId(): string
    {
        if (!$this->carteiraIndefinidaId) {
            try {
                $repoCarteira = $this->appConfigEntityHandler->getDoctrine()->getRepository(Carteira::class);
                $this->carteiraIndefinidaId = $repoCarteira->findOneBy(['codigo' => 99])->getId();
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao pesquisar - getCarteiraIndefinidaId');
            }
        }
        return $this->carteiraIndefinidaId;
    }


    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     * @throws ViewException
     */
    public function obterVendasPorPeriodo(\DateTime $dtIni, \DateTime $dtFim, ?bool $resalvar = false): int
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
        $pedidos = $this->obterJsonVendasPorPeriodo($dtIni, $dtFim);
        if ($pedidos ?? false) {
            foreach ($pedidos as $pedido) {
                $conn->beginTransaction();
                try {
                    $this->integrarVendaParaCrosier($pedido, $resalvar);
                    $conn->commit();
                } catch (\Throwable $e) {
                    try {
                        $conn->rollBack();
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


    /**
     * @param \DateTime $dtVenda
     * @throws ViewException
     */
    public function obterJsonVendasPorPeriodo(\DateTime $dtIni, \DateTime $dtFim)
    {
        $dtIni = (clone $dtIni)->setTime(0, 0);
        $dtFim = (clone $dtFim)->setTime(23, 59, 59, 9999);
        $dtIniS = $dtIni->format('Y-m-d');

        $jsons = [];
        $page = 1;
        do {

            try {
                $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos.json?data_inicio=' . $dtIniS . '&page=' . $page++,
                    [
                        'headers' => [
                            'Content-Type' => 'application/json; charset=UTF-8',
                            'appKey' => $this->getChave(),
                        ]
                    ]
                );
                $bodyContents = $response->getBody()->getContents();
                $json = json_decode($bodyContents, true);

                $results = $json['result'] ?? [];
                foreach ($results as $result) {

                    // Como a simplo7 retorna TODOS os pedidos a partir de tal data, verifica-se aqui para não pegar pedidos além da dtFim
                    // Dessa forma, serão retornados no $jsons apenas os pedidos de $dtVenda
                    $dtPedido = DateTimeUtils::parseDateStr($result['Wspedido']['data_pedido']);
                    $dtPedido->setTime(0, 0);
                    if ($dtPedido > $dtFim) {
                        continue;
                    }

                    $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/' . $result['Wspedido']['id'] . '.json',
                        [
                            'headers' => [
                                'Content-Type' => 'application/json; charset=UTF-8',
                                'appKey' => $this->getChave(),
                            ]
                        ]
                    );

                    $bodyContents = $response->getBody()->getContents();
                    $uJson = json_decode($bodyContents, true);

                    $jsons[] = $uJson['result'];
                }

                $hasNextPage = $json['pagination']['has_next_page'];
            } catch (GuzzleException $e) {
                throw new ViewException('Erro ao obterVendasPorData');
            }
        } while ($hasNextPage);

        return $jsons;
    }


    /**
     * @param int $numero
     * @param bool|null $resalvar
     * @return int
     */
    public function obterVendasPorNumero(int $numero, ?bool $resalvar = false): int
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
        $pedido = $this->obterJsonVendaPorNumero($numero);

        $conn->beginTransaction();
        try {
            $this->integrarVendaParaCrosier($pedido, $resalvar);
            $conn->commit();
        } catch (\Throwable $e) {
            try {
                $conn->rollBack();
            } catch (ConnectionException $e) {
                throw new \RuntimeException('rollback err', 0, $e);
            }
            $msg = ExceptionUtils::treatException($e) ?? 'Erro n/d';
            $msg .= ' - Pedido ' . ($pedido['Wspedido']['numero'] ?? '????');
            throw new ViewException('Erro ao integrar (' . $msg . ')');
        }
        return 1;
    }


    /**
     * @param \DateTime $dtVenda
     * @throws ViewException
     */
    public function obterJsonVendaPorNumero(int $numero)
    {
        try {
            $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/numero/' . $numero . '.json',
                [
                    'headers' => [
                        'Content-Type' => 'application/json; charset=UTF-8',
                        'appKey' => $this->getChave(),
                    ]
                ]
            );
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            $result = $json['result'] ?? [];
            return $result;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro ao obterVendasPorData');
        }
    }

    /**
     * @param string $status
     * @return null|array
     * @throws ViewException
     */
    public function obterVendasPorStatus(string $status): ?array
    {
        $jsons = [];
        $page = 1;
        do {

            try {
                $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos.json?status=' . $status . '&page=' . $page++,
                    [
                        'headers' => [
                            'Content-Type' => 'application/json; charset=UTF-8',
                            'appKey' => $this->getChave(),
                        ],
                        'timeout' => 30
                    ]
                );
                $bodyContents = $response->getBody()->getContents();
                $json = json_decode($bodyContents, true);
                $results = $json['result'] ?? [];
                foreach ($results as $result) {
                    $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/' . $result['Wspedido']['id'] . '.json',
                        [
                            'headers' => [
                                'Content-Type' => 'application/json; charset=UTF-8',
                                'appKey' => $this->getChave(),
                            ]
                        ]
                    );

                    $bodyContents = $response->getBody()->getContents();
                    $uJson = json_decode($bodyContents, true);

                    $jsons[] = $uJson['result'];
                }
                $hasNextPage = $json['pagination']['has_next_page'];
            } catch (GuzzleException $e) {
                throw new ViewException('Erro ao obterVendasPorStatus', 0, $e);
            }
        } while ($hasNextPage);

        return $jsons;
    }


    /**
     * @param array $pedido
     * @param bool|null $resalvar
     * @throws ViewException
     */
    private function integrarVendaParaCrosier(array $wsPedido, ?bool $resalvar = false): void
    {
        $pedido = null;
        try {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();

            $itens = $wsPedido['Item'];
            $pagamento = $wsPedido['Pagamento'] ?? null;
            $status_id = $wsPedido['Status']['id'];
            $status_nome = $wsPedido['Status']['nome'];
            $pedido = $wsPedido['Wspedido'];
            $pedido['cliente_cpfcnpj'] = preg_replace("/[^0-9]/", "", $pedido['cliente_cpfcnpj']);
            $dtPedido = DateTimeUtils::parseDateStr($pedido['data_pedido']);

            $this->syslog->info('Integrando pedido ' . $pedido['id'] . ' de ' .
                $dtPedido->format('d/m/Y H:i:s') . ' Cliente: ' . $pedido['cliente_razaosocial']);


            $venda = $conn->fetchAllAssociative('SELECT * FROM ven_venda WHERE json_data->>"$.ecommerce_idPedido" = :ecommerce_idPedido',
                ['ecommerce_idPedido' => $pedido['id']]);
            $venda = $venda[0] ?? null;
            if ($venda) {
                // se já existe, só confere o status
                // O único status que pode ser alterado no sentido Simplo7 -> Crosier é quando está em 'Aguardando Pagamento'
                $vendaJsonData = json_decode($venda['json_data'], true);
                if (($vendaJsonData['ecommerce_status_descricao'] === 'Criado') &&
                    (($vendaJsonData['ecommerce_status'] ?? null) != $pedido['pedidostatus_id'])) {

                    $vendaJsonData['ecommerce_status'] = $status_id;
                    $vendaJsonData['ecommerce_status_descricao'] = $status_nome;
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
                ['documento' => $pedido['cliente_cpfcnpj']]);
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->vendaEntityHandler->getDoctrine()->getRepository(Cliente::class);
            if ($cliente[0]['id'] ?? false) {
                $cliente = $repoCliente->find($cliente[0]['id']);
            } else {
                $cliente = null;
            }

            if (!$cliente || $resalvar) {

                $cliente = $cliente ?? new Cliente();

                $cliente->documento = $pedido['cliente_cpfcnpj'];
                $cliente->nome = $pedido['cliente_razaosocial'];
                $cliente->jsonData['tipo_pessoa'] = strlen($pedido['cliente_cpfcnpj']) === 11 ? 'PF' : 'PJ';
                $cliente->jsonData['rg'] = '';
                $cliente->jsonData['dtNascimento'] = $pedido['cliente_data_nascimento'];
                $cliente->jsonData['sexo'] = '';
                $cliente->jsonData['nome_fantasia'] = '';
                $cliente->jsonData['inscricao_estadual'] = $pedido['cliente_ie'];

                $cliente->jsonData['fone1'] = $pedido['cliente_telefone'];
                $cliente->jsonData['fone2'] = $pedido['cliente_celular'];

                $cliente->jsonData['email'] = $pedido['cliente_email'];
                $cliente->jsonData['canal'] = 'ECOMMERCE';
                $cliente->jsonData['ecommerce_id'] = '';

                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            // Verifica os endereços do cliente
            $enderecoJaSalvo = false;
            if (($cliente->jsonData['enderecos'] ?? false) && count($cliente->jsonData['enderecos']) > 0) {
                foreach ($cliente->jsonData['enderecos'] as $endereco) {
                    if ((($endereco['tipo'] ?? '') === 'ENTREGA,FATURAMENTO') &&
                        (($endereco['logradouro'] ?? '') === $pedido['entrega_logradouro']) &&
                        (($endereco['numero'] ?? '') === $pedido['entrega_numero']) &&
                        (($endereco['complemento'] ?? '') === $pedido['entrega_informacoes_adicionais']) &&
                        (($endereco['bairro'] ?? '') === $pedido['entrega_bairro']) &&
                        (($endereco['cep'] ?? '') === $pedido['entrega_cep']) &&
                        (($endereco['cidade'] ?? '') === $pedido['entrega_cidade']) &&
                        (($endereco['estado'] ?? '') === $pedido['entrega_estado'])) {
                        $enderecoJaSalvo = true;
                    }
                }
            }
            if (!$enderecoJaSalvo) {
                $cliente->jsonData['enderecos'][] = [
                    'tipo' => 'ENTREGA,FATURAMENTO',
                    'logradouro' => $pedido['entrega_logradouro'],
                    'numero' => $pedido['entrega_numero'],
                    'complemento' => $pedido['entrega_informacoes_adicionais'],
                    'bairro' => $pedido['entrega_bairro'],
                    'cep' => $pedido['entrega_cep'],
                    'cidade' => $pedido['entrega_cidade'],
                    'estado' => $pedido['entrega_estado'],
                ];
                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            $venda->cliente = $cliente;

            $venda->jsonData['canal'] = 'ECOMMERCE';
            $venda->jsonData['ecommerce_idPedido'] = $pedido['id'];
            $venda->jsonData['ecommerce_numeroPedido'] = $pedido['numero'] ?? 'n/d';
            $venda->jsonData['ecommerce_status'] = $status_id;
            $venda->jsonData['ecommerce_status_descricao'] = $status_nome;

            $obs = [];
            $venda->jsonData['ecommerce_entrega_retirarNaLoja'] = '';
            $venda->jsonData['ecommerce_entrega_logradouro'] = $pedido['entrega_logradouro'];
            $venda->jsonData['ecommerce_entrega_numero'] = $pedido['entrega_numero'];
            $venda->jsonData['ecommerce_entrega_complemento'] = $pedido['entrega_informacoes_adicionais'];
            $venda->jsonData['ecommerce_entrega_bairro'] = $pedido['entrega_bairro'];
            $venda->jsonData['ecommerce_entrega_cidade'] = $pedido['entrega_cidade'];
            $venda->jsonData['ecommerce_entrega_uf'] = $pedido['entrega_estado'];
            $venda->jsonData['ecommerce_entrega_cep'] = $pedido['entrega_cep'];
            $venda->jsonData['ecommerce_entrega_telefone'] = $pedido['entrega_telefone'];
            $venda->jsonData['ecommerce_entrega_frete_calculado'] = $pedido['total_frete'];
            $venda->jsonData['ecommerce_entrega_frete_real'] = 0.00;


            $obs[] = 'IP: ';
            $obs[] = 'Pagamento: ' . $pedido['pagamento_forma'];
            $obs[] = 'Envio: ' . $pedido['envio_servico'];
            if ($pedido['envio_codigo_objeto'] ?? false) {
                $obs[] = 'Rastreio: ' . $pedido['envio_codigo_objeto'];
            }

            $venda->jsonData['obs'] = implode(PHP_EOL, $obs);

            $venda->subtotal = 0.0;// a ser recalculado posteriormente
            $venda->desconto = 0.0;// a ser recalculado posteriormente
            $venda->valorTotal = 0.0;// a ser recalculado posteriormente


            $descontoTotal = $pedido['desconto_avista'];
            $totalProdutos = 0.0;
            foreach ($itens as $item) {
                $totalProdutos = bcadd($totalProdutos, $item['valor_total'], 2);
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
                /** @var Produto $produto */
                $produto = null;
                try {
                    // verifica se já existe uma ven_venda com o json_data.ecommerce_idPedido
                    $sProduto = $conn->fetchAssociative('SELECT id FROM est_produto WHERE json_data->>"$.ecommerce_id" = :idProduto', ['idProduto' => $item['produto_id']]);
                    if (!isset($sProduto['id'])) {
                        $sProduto = $this->obterProduto($item);
                    }
                    $produto = $repoProduto->find($sProduto['id']);
                } catch (\Throwable $e) {
                    throw new ViewException('Erro ao integrar venda. Erro ao pesquisar produto (idProduto = ' . $item['produto_id'] . ')');
                }

                $vendaItem = new VendaItem();
                $venda->addItem($vendaItem);
                $vendaItem->descricao = $produto->nome;
                $vendaItem->ordem = $ordem++;
                $vendaItem->devolucao = false;

                $vendaItem->unidade = $produto->unidadePadrao;

                $vendaItem->precoVenda = $item['valor_unitario'];
                $vendaItem->qtde = $item['quantidade'];
                $vendaItem->subtotal = bcmul($vendaItem->precoVenda, $vendaItem->qtde, 2);
                // Para arredondar para cima
                $vendaItem->desconto = DecimalUtils::round(bcmul($pDesconto, $vendaItem->subtotal, 3));
                $descontoAcum = (float)bcadd($descontoAcum, $vendaItem->desconto, 2);
                $vendaItem->produto = $produto;

                $vendaItem->jsonData['ecommerce_idItemVenda'] = $item['id'];
                $vendaItem->jsonData['ecommerce_codigo'] = $item['sku'];

                $this->vendaItemEntityHandler->save($vendaItem);
                $i++;
            }
            if ($descontoTotal !== $descontoAcum) {
                $diff = $descontoTotal - $descontoAcum;
                $vendaItem->desconto = bcadd($vendaItem->desconto, $diff, 2);
                $this->vendaItemEntityHandler->save($vendaItem);
            }
            $venda->recalcularTotais();
            // aqui é onde entram descontos de cupons (que é um desconto aplicado globalmente na venda)
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
            $totalPedido = bcadd($venda->valorTotal, $pedido['total_frete'], 2);

            // O total_pedido pode conter os acréscimos no caso de parcelamentos com qtde de parcelas acima do limite de parcelas sem juros.
            $venda->jsonData['total_pagtos'] = $pedido['total_pedido'];

            $tipoFormaPagamento = $pedido['pagamento_forma'];

            $carteiraId = null;


            switch ($pagamento['integrador']) {
                case "Cartão de Crédito":
                case "Boleto":
                case "Mercado Pago":
                    $carteiraId = $this->getCarteiraMercadoPagoSiteId();
                    $integrador = "Mercado Pago";
                    break;
                case "Depósito Bancário":
                case "Pix":
                    $carteiraId = $this->getCarteiraIndefinidaId();
                    $integrador = $pagamento['integrador'];
                    break;
                default:
                    throw new ViewException('Integrador não configurado: ' . $pagamento['integrador'] . ' (Venda: ' . $pedido['numero'] . ')');
            }

            if (!(in_array($venda->jsonData['ecommerce_status_descricao'], ['Criado','Cancelado'], true))) {
                $modoId = null;
                $descricaoPlanoPagto = null;
                $planoPagto = null;
                switch ($tipoFormaPagamento) {
                    case 'Depósito Bancário':
                        $planoPagto = $arrPlanosPagtosByCodigo['020'];
                        break;
                    case 'Boleto':
                        $planoPagto = $arrPlanosPagtosByCodigo['030'];
                        break;
                    case 'Pix':
                        $planoPagto = $arrPlanosPagtosByCodigo['040'];
                        break;
                    default:
                        $planoPagto = $arrPlanosPagtosByCodigo['010'];
                        break;
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
                        'codigo_transacao' => $pagamento['codigo_transacao'] ?? 'n/d',
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
                    if ($integrador === 'Mercado Pago') {
                        // no caso de pagamento via 'Mercado Pago', já busca as informações lá na API
                        $this->integradorMercadoPago->mlUser = 'defamiliapg@gmail.com';
                        $this->integradorMercadoPago->handleTransacaoParaVendaPagto($eVendaPagto);
                    }
                } catch (\Throwable $e) {
                    throw new ViewException('Erro ao salvar dados do pagamento');
                }


                $venda->jsonData['infoPagtos'] = $descricaoPlanoPagto .
                    ': R$ ' . number_format($venda->valorTotal, 2, ',', '.');
                if ($eVendaPagto->jsonData['codigo_transacao'] ?? false) {
                    $venda->jsonData['infoPagtos'] .= ' (Transação: ' . $eVendaPagto->jsonData['codigo_transacao'] . ')';
                }
                $venda->jsonData['forma_pagamento'] = mb_strtoupper($pagamento['pagamento_forma']);
            }
            
            $venda->jsonData['dados_completos_ecommerce'] = $wsPedido;
            

            $venda = $this->vendaEntityHandler->save($venda);

            if (!in_array($status_nome, ['Criado', 'Cancelado'])) {
                $this->vendaBusiness->finalizarPV($venda);
            }

        } catch (\Throwable $e) {
            $this->syslog->err('Erro ao integrarVendaParaCrosier', $pedido['id'] ?? null);
            throw new ViewException('Erro ao integrarVendaParaCrosier', 0, $e);
        }
    }


    /**
     * @param Venda $venda
     * @return void
     * @throws ViewException
     */
    public function reintegrarVendaParaCrosier(Venda $venda): void
    {
        if (!($venda->jsonData['ecommerce_idPedido'] ?? false)) {
            throw new ViewException('Venda sem ecommerce_idPedido');
        }

        $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/' . $venda->jsonData['ecommerce_idPedido'] . '.json',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'appKey' => $this->getChave(),
                ]
            ]
        );

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        $arrPedido = $json['result'] ?? null;

        $this->integrarVendaParaCrosier($arrPedido, true);
    }


    /**
     * @param Venda $venda
     * @return null
     */
    public function integrarVendaParaEcommerce(Venda $venda)
    {
        return null;
    }


    /**
     * @return mixed
     * @throws ViewException
     */
    public function obterStatusPedidos()
    {
        try {
            $cache = new FilesystemAdapter($_SERVER['CROSIERAPP_ID'] . '.ecommerce.status', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            return $cache->get('obterStatusPedidos', function (ItemInterface $item) {

                $status = [];
                $rsStatus = json_decode((new Client())->request('GET', $this->getEndpoint() . '/ws/wsstatuspedidos.json',
                        [
                            'headers' => [
                                'Content-Type' => 'application/json; charset=UTF-8',
                                'appKey' => $this->getChave(),
                            ]
                        ]
                    )->getBody()->getContents(), true)['result'] ?? [];

                foreach ($rsStatus as $s) {
                    $status[$s['Wsstatuspedido']['id']] = $s['Wsstatuspedido'];
                }

                return $status;

            });
        } catch (\Psr\Cache\InvalidArgumentException | \Throwable $e) {
            $msg = 'Erro ao obter array com status de pedidos';
            $this->syslog->err($msg);
            throw new ViewException($msg);
        }
    }


    /**
     * @param int $codVenda
     * @return int|null
     * @throws ViewException
     * @throws \Doctrine\DBAL\Exception
     */
    public function gerarNFeParaVenda(int $codVenda): ?int
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
        $existe = $conn->fetchAssociative('SELECT id FROM fis_nf WHERE json_data->>"$.pedido_simplo7" = :codVenda', ['codVenda' => $codVenda]);
        if ($existe) {
            return $existe['id'];
        }

        $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/' . $codVenda . '.json',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'appKey' => $this->getChave(),
                ]
            ]
        );

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        $arrPedido = $json['result'] ?? null;

        $notaFiscal = new NotaFiscal();

        $notaFiscal->jsonData['pedido_simplo7'] = $codVenda;
        $notaFiscal->documentoEmitente = '34411048000104';
        $notaFiscal->tipoNotaFiscal = 'NFE';
        $notaFiscal->naturezaOperacao = 'VENDA';
        $notaFiscal->entradaSaida = 'S';
        $notaFiscal->dtEmissao = new \DateTime();
        $notaFiscal->dtSaiEnt = new \DateTime();
        $notaFiscal->transpModalidadeFrete = 'EMITENTE';
        $notaFiscal->documentoDestinatario = $arrPedido['Wspedido']['cliente_cpfcnpj'];
        $notaFiscal->xNomeDestinatario = $arrPedido['Wspedido']['cliente_razaosocial'];
        $notaFiscal->logradouroDestinatario = $arrPedido['Wspedido']['cliente_logradouro'];
        $notaFiscal->numeroDestinatario = $arrPedido['Wspedido']['cliente_numero'];
        $notaFiscal->complementoDestinatario = $arrPedido['Wspedido']['cliente_informacoes_adicionais'];
        $notaFiscal->bairroDestinatario = $arrPedido['Wspedido']['cliente_bairro'];
        $notaFiscal->cidadeDestinatario = $arrPedido['Wspedido']['cliente_cidade'];
        $notaFiscal->estadoDestinatario = $arrPedido['Wspedido']['cliente_estado'];
        $notaFiscal->cepDestinatario = $arrPedido['Wspedido']['cliente_cep'];
        $notaFiscal->foneDestinatario = $arrPedido['Wspedido']['cliente_telefone'];
        $notaFiscal->emailDestinatario = $arrPedido['Wspedido']['cliente_email'];

        $notaFiscal->infoCompl = 'Envio: ' . $arrPedido['Wspedido']['envio_servico'];
        $notaFiscal->infoCompl .= PHP_EOL . 'Pedido: ' . $arrPedido['Wspedido']['numero'];
        if (($arrPedido['WsPedido']['cliente_observacao'] ?? false) && $arrPedido['Wspedido']['cliente_observacao']) {
            $notaFiscal->infoCompl .= PHP_EOL . PHP_EOL . 'Atenção: ' . $arrPedido['Wspedido']['cliente_observacao'];
        }

        foreach ($arrPedido['Item'] as $item) {
            $notaFiscalItem = new NotaFiscalItem();
            $notaFiscalItem->codigo = $item['sku'];
            $notaFiscalItem->descricao = $item['nome_produto'];
            $notaFiscalItem->qtde = $item['quantidade'];
            $notaFiscalItem->cfop = $arrPedido['Wspedido']['cliente_estado'] === 'PR' ? '5102' : '6102';
            $notaFiscalItem->csosn = 103;
            $notaFiscalItem->ncm = '63052000';
            $notaFiscalItem->unidade = 'UN';
            $notaFiscalItem->valorUnit = $item['valor_unitario'];

            $notaFiscal->addItem($notaFiscalItem);
        }

        $this->notaFiscalBusiness->saveNotaFiscal($notaFiscal);

        return $notaFiscal->getId();
    }

    /**
     *
     */
    public function atualizarPedidosMelhorEnvio()
    {
        $return = [];
        $pedidosEnviados = $this->obterVendasPorStatus('enviado');

        foreach ($pedidosEnviados as $pedidoEnviado) {
            if (stripos(($pedidoEnviado['Wspedido']['envio_servico'] ?? ''), 'jadlog') !== FALSE) {
                $return[] = 'Pedido: ' . $pedidoEnviado['Wspedido']['id'] . ' (' . $pedidoEnviado['Wspedido']['cliente_razaosocial'] . ')';
                $tracking = $pedidoEnviado['Wspedido']['envio_codigo_objeto'] ?? '';
                if (!$tracking) {
                    $return[] = 'sem código de rastreio';
                    $return[] = '---';
                    continue;
                }
                $rMelhorRastreio = $this->client->request('GET', 'https://api.melhorrastreio.com.br/api/v1/trackings/' . $tracking, ['timeout' => 10]);
                $bodyMelhorRastreio = $rMelhorRastreio->getBody()->getContents();
                $jsonMelhorRastreio = json_decode($bodyMelhorRastreio, true);

                $status = $jsonMelhorRastreio['data']['status'] ?? $jsonMelhorRastreio['message'] ?? '';
                if ($status !== 'delivered') {
                    $return[] = 'não entregue: ' . $status;
                    $return[] = '---';
                    continue;
                }
                $return[] = 'entregue... atualizando status';
                $data['Wspedido']['Status']['id'] = 3;
                $rAtualizaPedido = $this->client->request('PUT', $this->getEndpoint() . '/ws/wspedidos/' . $pedidoEnviado['Wspedido']['id'] . '.json',
                    [
                        'headers' => [
                            'Content-Type' => 'application/json; charset=UTF-8',
                            'appKey' => $this->getChave(),
                        ],
                        'body' => json_encode($data),
                        'timeout' => 10
                    ]
                );
                $bodyAtualizaPedido = $rAtualizaPedido->getBody()->getContents();
                $jsonAtualizaPedido = json_decode($bodyAtualizaPedido, true);
                if (($jsonAtualizaPedido['result']['Status']['nome'] ?? '') === 'Entregue') {
                    $return[] = 'OK';
                } else {
                    $return[] = 'ERRO';
                    $return[] = json_encode($jsonAtualizaPedido);
                }
                $return[] = '---';

            }
        }
        return $return;
    }

    public function obterCliente($idClienteEcommerce)
    {
        // TODO: Implement obterCliente() method.
    }

    /**
     * @param array $item
     * @throws ViewException
     */
    public function obterProduto(array $item)
    {
        try {
            $idProduto = $item['produto_id'];
            $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wsprodutos/' . $idProduto . '.json',
                [
                    'headers' => [
                        'Content-Type' => 'application/json; charset=UTF-8',
                        'appKey' => $this->getChave(),
                    ],
                    'timeout' => 10
                ]
            );
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            $eproduto = $json['result'] ?? false;

            $produto = new Produto();
            $produto->jsonData['dados_ecommerce'] = $eproduto ?? $item;
            $produto->jsonData['ecommerce_id'] = $item['produto_id'];
            $produto->codigo = $item['sku'];
            $produto->status = $eproduto['Wsproduto']['situacao'] ?? 'INATIVO';
            $produto->depto = $this->deptoIndefinido;
            $produto->grupo = $this->grupoIndefinido;
            $produto->subgrupo = $this->subgrupoIndefinido;
            $produto->fornecedor = $this->fornecedorDefamilia;
            $produto->nome = $item['nome_produto'];

            $this->produtoEntityHandler->save($produto);

            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();

            return $conn->fetchAssociative('SELECT id FROM est_produto WHERE json_data->>"$.ecommerce_id" = :idProduto', ['idProduto' => $idProduto]);
        } catch (GuzzleException $e) {
            throw new ViewException('Erro ao obterVendasPorStatus');
        }
    }

    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int
    {
        return $this->obterVendasPorPeriodo($dtVenda, $dtVenda, $resalvar);
    }

    public function obterVendasPorData(\DateTime $dtVenda)
    {
        return $this->obterVendasPorPeriodo($dtVenda);
    }

    /**
     * @param int $id
     * @param \DateTime $dtPagto
     * @throws GuzzleException
     */
    public function atualizarDtPagtoPedido(int $id, \DateTime $dtPagto)
    {
        $response = $this->client->request('GET', $this->getEndpoint() . '/ws/wspedidos/' . $id . '.json',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'appKey' => $this->getChave(),
                ]
            ]
        );
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        $result = $json['result'] ?? [];
        $pagamento = $json['result']['Pagamento'];
        $pagamento['data'] = $dtPagto->format('Y-m-d');
        $json = [
            'Wspedido' => [ 'Pagamento' => $pagamento ]
        ];
        $response = $this->client->request('PUT', $this->getEndpoint() . '/ws/wspedidos/' . $id . '.json',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'appKey' => $this->getChave(),
                ],
                'timeout' => 10,
                'json' => $json
            ]
        );
        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json;
    }

    public function integraProduto(Produto $produto, ?bool $integrarImagens = true, ?bool $respeitarDelay = false)
    {
        // TODO: Implement integraProduto() method.
    }


}
