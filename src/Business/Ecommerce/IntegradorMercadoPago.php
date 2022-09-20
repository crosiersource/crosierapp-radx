<?php


namespace App\Business\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibRadxBundle\Entity\RH\Colaborador;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\PlanoPagto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaPagto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoPrecoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaPagtoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegradorMercadoPago
{

    private Connection $conn;

    private Client $client;

    // Deve ser passado após a instanciação do objeto para saber qual chave buscar, pois podem ter duas ou mais contas configuradas
    public ?string $mlUser = null;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;

    private ProdutoEntityHandler $produtoEntityHandler;

    private ProdutoPrecoEntityHandler $produtoPrecoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private VendaPagtoEntityHandler $vendaPagtoEntityHandler;

    private ClienteEntityHandler $clienteEntityHandler;

    private ParameterBagInterface $params;

    private MessageBusInterface $bus;

    private SyslogBusiness $syslog;

    public const CANAL = 'MERCADOLIVRE';

    
    public function __construct(AppConfigEntityHandler $appConfigEntityHandler,
                                Security $security,
                                ProdutoEntityHandler $produtoEntityHandler,
                                ProdutoPrecoEntityHandler $produtoPrecoEntityHandler,
                                VendaEntityHandler $vendaEntityHandler,
                                VendaItemEntityHandler $vendaItemEntityHandler,
                                VendaPagtoEntityHandler $vendaPagtoEntityHandler,
                                ClienteEntityHandler $clienteEntityHandler,
                                ParameterBagInterface $params,
                                MessageBusInterface $bus,
                                SyslogBusiness $syslog)
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->produtoPrecoEntityHandler = $produtoPrecoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->vendaPagtoEntityHandler = $vendaPagtoEntityHandler;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->params = $params;
        $this->bus = $bus;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->conn = $this->appConfigEntityHandler->getDoctrine()->getConnection();
        $this->client = new Client();
    }

    /**
     * @return mixed
     * @throws ViewException
     */
    private function getMercadoPagoConfigs()
    {
        try {
            if (!$this->mlUser) {
                throw new ViewException('mlUser n/d');
            }
            $cache = new FilesystemAdapter('mercadopago_configs.json', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            return $cache->get('mercadopago_configs.' . preg_replace('/[\W]/', '', $this->mlUser), function (ItemInterface $item) {
                $rsAppConfig = $this->conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave', ['chave' => 'mercadopago_configs.json']);
                $todos = json_decode($rsAppConfig['valor'], true);
                foreach ($todos as $config) {
                    if ($config['user'] === $this->mlUser) {
                        return $config;
                    }
                }
                throw new ViewException('mlUser n/d');
            });
        } catch (InvalidArgumentException $e) {
            throw new ViewException('Erro ao obter mercadopago_configs.json');
        }
    }

    /**
     * @param VendaPagto $pagto
     * @return mixed|null
     * @throws ViewException
     */
    public function handleTransacaoParaVendaPagto(VendaPagto $pagto)
    {
        if (($pagto->jsonData['integrador'] ?? '') !== 'Mercado Pago') {
            return null;
        }

        if (!($pagto->jsonData['codigo_transacao'] ?? false)) {
            return null;
        }

        try {
            $response = $this->client->request('GET', $this->getMercadoPagoConfigs()['endpoint_api'] . '/v1/payments/' . $pagto->jsonData['codigo_transacao'],
                [
                    'headers' => [
                        'Content-Type' => 'application/json; charset=UTF-8',
                        'accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->getMercadoPagoConfigs()['token']
                    ],
                ]
            );
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);


            // pois pode ser
            if ($pagto->venda->jsonData['mlOrder']['shipping']['id'] ?? false) {
                $responseShipments = $this->client->request('GET', 'https://api.mercadolibre.com/shipments/' . $pagto->venda->jsonData['mlOrder']['shipping']['id'],
                    [
                        'headers' => [
                            'Content-Type' => 'application/json; charset=UTF-8',
                            'accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $this->getMercadoPagoConfigs()['token']
                        ],
                    ]
                );
                $bodyContentsShipments = $responseShipments->getBody()->getContents();
                $jsonShipments = json_decode($bodyContentsShipments, true);
                // Atenção: não sei se esta regra é assim mesmo para todos os casos. Decifrei comparando os JSONs de compra onde o frete foi cobrado com outra que não foi.
                $shipping_cost = $jsonShipments['shipping_option']['cost'] ?? 0.0;
                $shipping_listCost = $jsonShipments['shipping_option']['list_cost'] ?? 0.0;
                if ($shipping_listCost > $shipping_cost) {
                    $dif = bcsub($shipping_listCost, $shipping_cost, 2);
                    $json['fee_details'][] =
                        [
                            'amount' => $dif,
                            'fee_payer' => 'collector',
                            'type' => 'DIF FRETE PAGO PELO VENDEDOR',
                            'OBS' => 'RTA CEP, POIS ML NAO RETORNA VALOR DO FRETE QUANDO PAGO PELO VENDEDOR'
                        ];
                }
            }

            $pagto->jsonData['mercadopago_retorno'] = $json;
            $pagto_jsonData = json_encode($pagto->jsonData);

            $this->conn->update('ven_venda_pagto', ['json_data' => $pagto_jsonData], ['id' => $pagto->getId()]);

            return $json;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro na comunicação', 0, $e);
        } catch (\Throwable $e) {
            throw new ViewException('Erro em handleTransacaoParaVendaPagto', 0, $e);
        }
    }

    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return mixed
     * @throws ViewException
     */
    public function obterVendasMercadoLivre(\DateTime $dtVenda, ?bool $resalvar = false)
    {
        try {
            $dtVendaStr = $dtVenda->format('Y-m-d');
            $dtVendaStr_ini = $dtVendaStr . 'T00:00:00.000-03:00'; // '2020-01-01T00:00:00.000-03:00';
            $dtVendaStr_fim = $dtVendaStr . 'T23:59:59.999-03:00';

//            $dtVendaStr_ini = '2020-01-01T00:00:00.000-03:00';
//            $dtVendaStr_fim = '2021-12-31T00:00:00.000-03:00';

            $response = $this->client->request('GET', 'https://api.mercadolibre.com/orders/search?seller=' . $this->getMercadoPagoConfigs()['userid'] . '&order.date_created.from=' . $dtVendaStr_ini . '&order.date_created.to=' . $dtVendaStr_fim,
                [
                    'headers' => [
                        'Content-Type' => 'application/json; charset=UTF-8',
                        'accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->getMercadoPagoConfigs()['token']
                    ],
                ]
            );
            $bodyContents = $response->getBody()->getContents();

            $json = json_decode($bodyContents, true);
            if ($json['results'] ?? false) {
                foreach ($json['results'] as $rVenda) {
                    //try {
                    $this->integrarVendaParaCrosier($rVenda, $resalvar);
                    //} catch (ViewException $e) {
                    //    $this->syslog->err('Erro ao integrarVendaParaCrosier - id (ml)' . ($rVenda['id'] ?? 'n/d'));
                    //}
                }
            }

            return $json;
        } catch (\Throwable $e) {
            $msg = ExceptionUtils::treatException($e);
            throw new ViewException('Erro em obterVendas (' . $msg . ')', 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    private function integrarVendaParaCrosier(array $mlOrder, ?bool $resalvar = false): void
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
        try {
            if (($mlOrder['status'] ?? '') !== 'paid') {
                $this->syslog->info('Venda não importada (status != paid): ' . ($mlOrder['status'] ?? ''));
                return;
            }

            $itens = $mlOrder['order_items'];

            $cliente_cpfcnpj = preg_replace("/[^0-9]/", "", ($mlOrder['buyer']['billing_info']['doc_number'] ?? ''));
            $cliente_nome = trim(($mlOrder['buyer']['first_name'] ?? '') . ' ' . ($mlOrder['buyer']['last_name'] ?? ''));
            $dtPedido = DateTimeUtils::parseDateStr($mlOrder['date_created']);

            $this->syslog->info('Integrando pedido ' . $mlOrder['id'] . ' de ' .
                $dtPedido->format('d/m/Y H:i:s') . ' Cliente: ' . $cliente_nome);


            $venda = $conn->fetchAllAssociative('SELECT * FROM ven_venda WHERE json_data->>"$.canal" = :canal AND json_data->>"$.ecommerce_idPedido" = :ecommerce_idPedido',
                [
                    'canal' => self::CANAL,
                    'ecommerce_idPedido' => $mlOrder['id']
                ]);
            $venda = $venda[0] ?? null;
            if ($venda) {

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
                /** @var VendaRepository $repoVenda */
                $repoVenda = $this->vendaEntityHandler->getDoctrine()->getRepository(Venda::class);
                $venda = $repoVenda->find($venda['id']);

            } else {
                $venda = new Venda();
            }

            $conn->beginTransaction();

            $venda->dtVenda = $dtPedido;

            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->vendaEntityHandler->getDoctrine()->getRepository(Colaborador::class);
            $vendedorNaoIdentificado = $repoColaborador->findOneBy(['cpf' => '99999999999']);
            $venda->vendedor = $vendedorNaoIdentificado;

            $venda->status = 'PV ABERTO';

            $cliente = $conn->fetchAllAssociative('SELECT id FROM crm_cliente WHERE documento = :documento',
                ['documento' => $cliente_cpfcnpj]);
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->vendaEntityHandler->getDoctrine()->getRepository(Cliente::class);
            if ($cliente[0]['id'] ?? false) {
                $cliente = $repoCliente->find($cliente[0]['id']);
            } else {
                $cliente = null;
            }

            if (!$cliente || $resalvar) {

                $cliente = $cliente ?? new Cliente();

                $cliente->documento = $cliente_cpfcnpj;
                $cliente->nome = $cliente_nome;
                $cliente->jsonData['tipo_pessoa'] = strlen($cliente_cpfcnpj) === 11 ? 'PF' : 'PJ';
                $cliente->jsonData['canal'] = self::CANAL;
                $cliente->jsonData['ecommerce_id'] = $mlOrder['buyer']['id'] ?? '';

                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            $venda->cliente = $cliente;

            $venda->jsonData['canal'] = self::CANAL;
            $venda->jsonData['ecommerce_idPedido'] = $mlOrder['id'];
            $venda->jsonData['ecommerce_status'] = $mlOrder['status'];
            $venda->jsonData['ecommerce_status_descricao'] = $mlOrder['status'];

            $venda->subtotal = 0.0;// a ser recalculado posteriormente
            $venda->desconto = 0.0;// a ser recalculado posteriormente
            $venda->valorTotal = 0.0;// a ser recalculado posteriormente

            $totalProdutos = 0.0;
            foreach ($itens as $item) {
                $totalProdutos = bcadd($totalProdutos, $item['full_unit_price'], 2);
            }

            // Salvo aqui para poder pegar o id
            $this->vendaEntityHandler->save($venda);

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);
            $ordem = 1;
            $i = 0;
            $descontoAcum = 0.0;
            $vendaItem = null;
            $totalTaxasItens = 0;
            foreach ($itens as $item) {
                /** @var Produto $produto */
                $produto = null;
                try {
                    // verifica se já existe uma ven_venda com o json_data.ecommerce_idPedido
                    $sProduto = $conn->fetchAssociative('SELECT id FROM est_produto WHERE json_data->>"$.canal" = :canal AND json_data->>"$.ecommerce_id" = :idProdutoEcommerce',
                        [
                            'canal' => self::CANAL,
                            'idProdutoEcommerce' => $item['item']['id']
                        ]);
                    if (!($sProduto['id'] ?? false)) {
                        $produto = new Produto();
                        $produto->jsonData['canal'] = self::CANAL;
                        $produto->jsonData['ecommerce_id'] = $item['item']['id'];
                        $produto->nome = $item['item']['title'];
                        $produto->codigo = $item['item']['id'];


                        $this->produtoEntityHandler->save($produto, false);

                        $preco = new ProdutoPreco();
                        $preco->produto = $produto;
                        $preco->dtPrecoVenda = $dtPedido;
                        $preco->precoCusto = 0.01;
                        $preco->margem = 0.30;
                        $preco->custoFinanceiro = 0.15;
                        $preco->custoOperacional = 0.20;
                        $preco->precoPrazo = $item['full_unit_price'];
                        $this->produtoPrecoEntityHandler->save($preco, false);

                        $this->produtoEntityHandler->save($produto, true);
                    } else {
                        $produto = $repoProduto->find($sProduto['id']);
                    }
                } catch (\Throwable $e) {
                    throw new ViewException('Erro ao integrar venda. Erro ao pesquisar produto (idProduto = ' . $item['produto_id'] . ')');
                }

                $vendaItem = new VendaItem();
                $venda->addItem($vendaItem);
                $vendaItem->descricao = $produto->nome;
                $vendaItem->ordem = $ordem++;
                $vendaItem->devolucao = false;

                $vendaItem->precoVenda = $item['full_unit_price'];
                $vendaItem->qtde = $item['quantity'];
                $vendaItem->subtotal = bcmul($vendaItem->precoVenda, $vendaItem->qtde, 2);
                // Para arredondar para cima

                $descontoAcum = (float)bcadd($descontoAcum, $vendaItem->desconto, 2);
                $vendaItem->produto = $produto;

                $taxaNoItem = $item['sale_fee'];
                $totalTaxasItens = bcadd($taxaNoItem, $totalTaxasItens, 2);
                $vendaItem->jsonData['ecommerce_taxa_venda'] = $taxaNoItem; // taxa cobrada na venda do item
                $vendaItem->jsonData['ecommerce_ml_listing_type_id'] = $item['listing_type_id']; // gold, etc

                $this->vendaItemEntityHandler->save($vendaItem);
                $i++;
            }

            $venda->jsonData['mlOrder'] = $mlOrder;

            $venda->jsonData['ecommerce_total_pago'] = $mlOrder['payments'][0]['total_paid_amount'];
            $venda->jsonData['ecommerce_total_taxas'] = $totalTaxasItens;
            $venda->jsonData['ecommerce_total_frete'] = $mlOrder['payments'][0]['shipping_cost'];
            $venda->jsonData['ecommerce_total_liquido'] = $venda->jsonData['ecommerce_total_pago'] - $totalTaxasItens - $mlOrder['payments'][0]['shipping_cost'];

            $venda->recalcularTotais();


            try {
                $conn->delete('ven_venda_pagto', ['venda_id' => $venda->getId()]);
            } catch (\Throwable $e) {
                $erro = 'Erro ao deletar pagtos da venda (id = "' . $venda['id'] . ')';
                $this->syslog->err($erro);
                throw new \RuntimeException($erro);
            }


            $totalPagto = bcadd($venda->valorTotal, $venda->jsonData['ecommerce_entrega_frete_calculado'] ?? 0.0, 2);

            $integrador = 'Mercado Pago';

            $vendaPagto = new VendaPagto();
            $venda->addPagto($vendaPagto);
            $vendaPagto->valorPagto = $totalPagto;
            $vendaPagto->planoPagto = $this->vendaPagtoEntityHandler->getDoctrine()->getRepository(PlanoPagto::class)->findOneBy(['codigo' => 999]);
            $vendaPagto->jsonData = [
                'integrador' => $integrador,
                'codigo_transacao' => $mlOrder['payments'][0]['id'],
                'carteira_id' => $this->getMercadoPagoConfigs()['carteira_id'],
            ];
            $this->vendaPagtoEntityHandler->save($vendaPagto);

            try {
                $this->handleTransacaoParaVendaPagto($vendaPagto);
                $this->vendaItemEntityHandler->vendaBusiness->finalizarPV($venda);
            } catch (\Throwable $e) {
                throw new ViewException('Erro ao salvar dados do pagamento');
            }

            $conn->commit();
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    throw new ViewException("Erro ao efetuar o rollback - integrarVendaParaCrosier", 0, $e);
                }
            }
            $this->syslog->err('Erro ao integrarVendaParaCrosier', $mlOrder['id']);
            throw new ViewException('Erro ao integrarVendaParaCrosier', 0, $e);
        }
    }

}
