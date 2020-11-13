<?php

namespace App\Controller\Vendas;

use App\Form\Vendas\VendaType;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorECommerceFactory;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Vendas\VendaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\FinalidadeNF;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\RH\Colaborador;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\PlanoPagto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaPagto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CarteiraRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\PlanoPagtoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Doctrine\DBAL\Connection;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package Cliente\Controller\Crediario
 * @author Carlos Eduardo Pauluk
 */
class VendaController extends FormListController
{

    private Pdf $knpSnappyPdf;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private VendaBusiness $vendaBusiness;

    private NotaFiscalEntityHandler $notaFiscalEntityHandler;

    /**
     * @required
     * @param Pdf $knpSnappyPdf
     */
    public function setKnpSnappyPdf(Pdf $knpSnappyPdf): void
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    /**
     * @required
     * @param VendaEntityHandler $entityHandler
     */
    public function setEntityHandler(VendaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param VendaItemEntityHandler $vendaItemEntityHandler
     */
    public function setVendaItemEntityHandler(VendaItemEntityHandler $vendaItemEntityHandler): void
    {
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
    }

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }

    /**
     * @required
     * @param VendaBusiness $vendaBusiness
     */
    public function setVendaBusiness(VendaBusiness $vendaBusiness): void
    {
        $this->vendaBusiness = $vendaBusiness;
    }

    /**
     * @required
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $notaFiscalEntityHandler): void
    {
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
    }

    /**
     * @return SyslogBusiness
     */
    public function getSyslog(): SyslogBusiness
    {
        return $this->syslog->setApp('radx')->setComponent(self::class);
    }

    /**
     *
     * @Route("/ven/venda/form/dados/{id}", name="ven_venda_form_dados", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendaFormDados(Request $request, Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listVendasEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form_dados.html.twig',
            'formRoute' => 'ven_venda_form_dados',
            'formPageTitle' => 'Venda'
        ];

        if (!$request->get('btnSalvar')) {
            if (!$venda) {
                $venda = new Venda();
                $venda->dtVenda = new \DateTime();
                $venda->status = 'PV ABERTO';

                /** @var ClienteRepository $repoCliente */
                $repoCliente = $this->getDoctrine()->getRepository(Cliente::class);
                /** @var Cliente $consumidorNaoIdentificado */
                $consumidorNaoIdentificado = $repoCliente->findOneBy(['documento' => '99999999999']);
                $venda->cliente = $consumidorNaoIdentificado;

                /** @var PlanoPagtoRepository $repoPlanoPagto */
                $repoPlanoPagto = $this->getDoctrine()->getRepository(PlanoPagto::class);
                /** @var PlanoPagto $planoPagto */
                $planoPagto = $repoPlanoPagto->findOneBy(['codigo' => '001']);
                $venda->planoPagto = $planoPagto;

                $venda->jsonData['cliente_documento'] = '99999999999';
                $venda->jsonData['cliente_nome'] = 'NÃO IDENTIFICADO';
                $venda->subtotal = 0.0;
                $venda->desconto = 0.0;

                $conn = $this->entityHandler->getDoctrine()->getConnection();
                $ultimaVenda = $conn->fetchAllAssociative('SELECT vendedor_id, json_data FROM ven_venda ORDER BY updated DESC limit 1');
                $ultimaVenda_jsonData = json_decode($ultimaVenda[0]['json_data'] ?? '{}', true);
                $venda->jsonData['canal'] = $ultimaVenda_jsonData['canal'] ?? 'LOJA FÍSICA';

                /** @var ColaboradorRepository $repoColaborador */
                $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);
                if ($ultimaVenda[0]['vendedor_id'] ?? false) {
                    /** @var Colaborador $colaborador */
                    $colaborador = $repoColaborador->find($ultimaVenda[0]['vendedor_id']);
                } else {
                    /** @var Colaborador $colaborador */
                    $colaborador = $repoColaborador->findOneBy(['cpf' => '99999999999']);
                }
                $venda->vendedor = $colaborador;
            }
        }

        $fnHandleRequestOnValid = function (Request $request, Venda $venda, ?array &$params = []): void {
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->getDoctrine()->getRepository(Cliente::class);
            $documento = preg_replace("/[^G^0-9]/", '', ($venda->jsonData['cliente_documento'] ?? ''));
            if ($documento) {
                /** @var Cliente $cliente */
                $cliente = $repoCliente->findOneBy(['documento' => $documento]);
                $venda->cliente = $cliente;
            } else {
                if (($request->get('cliente_nome') ?? false)) {
                    $venda->cliente = null;
                    $venda->jsonData['cliente_nome'] = $request->get('cliente_nome');
                } else {
                    /** @var Cliente $cliente */
                    $cliente = $repoCliente->findOneBy(['documento' => '99999999999']);
                    $venda->cliente = $cliente;
                }
            }
            if ($venda->getId() && $venda->cliente) {
                if ($documento !== $venda->cliente->documento) {
                    $venda->cliente = null; // o documento foi alterado, deve ser salvo como um novo cliente
                }
            }
            $params['formView'] = 'Vendas/venda_form_itens.html.twig';
            // redireciona para...
            $params['formRoute'] = 'ven_venda_form_itens';
        };

        return $this->doForm($request, $venda, $params, false, $fnHandleRequestOnValid);
    }


    /**
     *
     * @Route("/ven/venda/form/itens/{id}", name="ven_venda_form_itens", requirements={"id"="\d+"})
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendaFormItens(Venda $venda)
    {
        $itens = $venda->itens->toArray();
        usort($itens, function (VendaItem $a, VendaItem $b) {
            return $a->ordem > $b->ordem;
        });
        $venda->itens = $itens;

        $params = [
            'listRoute' => 'ven_venda_listVendasEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form_dados.html.twig',
            'formRoute' => 'ven_venda_form_dados',
            'formPageTitle' => 'Venda',
            'formRouteParams' => ['id' => $venda->getId()],
            'e' => $venda,
        ];

        return $this->doRender('Vendas/venda_form_itens.html.twig', $params);
    }


    /**
     *
     * @Route("/ven/venda/saveOrdemItens", name="ven_venda_saveOrdemItens")
     * @param Request $request
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function saveOrdemItens(Request $request)
    {
        try {
            $ids = $request->get('ids');
            $idsArr = explode(',', $ids);
            $ordens = $this->vendaItemEntityHandler->salvarOrdens($idsArr);
            $r = ['result' => 'OK', 'ids' => $ordens];
            return new JsonResponse($r);
        } catch (ViewException $e) {
            return new JsonResponse(['result' => 'FALHA']);
        }
    }

    /**
     *
     * @Route("/ven/venda/saveItem/{venda}", name="ven_venda_saveItem", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function saveItem(Request $request, Venda $venda)
    {
        try {
            if ($venda->status !== 'PV ABERTO') {
                throw new ViewException('Status difere de "PV ABERTO"');
            }

            $item = $request->get('item');

            if (!isset($item['id']) && !isset($item['produto'])) {
                throw new ViewException('Produto não informado');
            }

            $conn = $this->entityHandler->getDoctrine()->getConnection();

            $qtde = abs(DecimalUtils::parseStr($item['qtde']));
            $desconto = abs(DecimalUtils::parseStr($item['desconto'] ?: '0,00'));
            $devolucao = ($item['devolucao'] ?? 0) ? 1 : 0;

            if ($item['id'] ?? false) {
                /** @var VendaItem $vendaItem_ */
                $vendaItem_ = $this->getDoctrine()->getRepository(VendaItem::class)->find((int)$item['id']);
                $produtoId = $vendaItem_->produto->getId();
                $unidadeId = $vendaItem_->unidade->getId();
            } else {
                $produtoId = (int)$item['produto'];
                $unidadeId = $item['unidade'] ?? null;
            }

            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->find($produtoId);

            // quando o form é submetido automaticamente numa pesquisa por EAN exato, vem sem a unidade
            if (!$unidadeId) {
                $unidadeId = $produto->unidadePadrao->getId();
            }


            if (!($item['id'] ?? false)) {
                foreach ($venda->itens as $itemNaVenda) {
                    if ($itemNaVenda->produto->getId() === $produtoId &&
                        $itemNaVenda->unidade->getId() === $unidadeId &&
                        $itemNaVenda->devolucao === (bool)$devolucao) {
                        if ($item['id'] ?? false) {
                            // se está alterando para um produto já existente, deleta e incrementa a qtde no outro produto
                            $conn->delete('ven_venda_item', ['id' => $item['id']]);
                        }
                        $itemNaVenda->qtde = bcadd($qtde, abs($itemNaVenda->qtde), 2);

                        $itemNaVenda->desconto = $desconto;
                        $this->vendaItemEntityHandler->save($itemNaVenda);
                        return $this->redirectToRoute('ven_venda_form_itens', ['id' => $venda->getId()]);
                    }
                }
            }

            $vendaItem = [];


            $vendaItem['produto_id'] = $produtoId;
            $vendaItem['venda_id'] = $venda->getId();

            $repoProdutoPreco = $this->getDoctrine()->getRepository(ProdutoPreco::class);
            $precoAtual = $repoProdutoPreco->findBy(['produto' => $produto, 'atual' => true, 'unidade' => $unidadeId]);
            if (!$precoAtual) {
                throw new ViewException('Preço atual não encontrado');
            }
            /** @var ProdutoPreco $precoAtual */
            $precoAtual = $precoAtual[0];

            if ($item['precoVenda'] ?? false) {
                $vendaItem['preco_venda'] = DecimalUtils::parseStr($item['precoVenda']);
            } else {
                $vendaItem['preco_venda'] = $precoAtual->precoPrazo;
            }

            $vendaItem['descricao'] = $produto->nome;
            if (!($item['id'] ?? false)) {
                $vendaItem['ordem'] = $venda->itens->count() + 1;
            }

            $repoUnidade = $this->getDoctrine()->getRepository(Unidade::class);
            /** @var Unidade $unidade */
            $unidade = $repoUnidade->find($unidadeId);

            $vendaItem['unidade_id'] = $unidadeId;

            $vendaItem['devolucao'] = $devolucao;
            $vendaItem['qtde'] = bcmul(($vendaItem['devolucao'] ? -1 : 1) * abs($qtde), 1, $unidade->casasDecimais);

            $vendaItem['subtotal'] = DecimalUtils::roundUp(bcmul($vendaItem['qtde'], $vendaItem['preco_venda'], 4));
            $vendaItem['desconto'] = ($vendaItem['devolucao'] ? -1 : 1) * abs($desconto);
            $vendaItem['total'] = bcsub($vendaItem['subtotal'], $vendaItem['desconto'], 2);

            $vendaItem['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
            $vendaItem['inserted'] = (new \DateTime())->format('Y-m-d H:i:s');
            $vendaItem['estabelecimento_id'] = 1;
            $vendaItem['user_inserted_id'] = 1;
            $vendaItem['user_updated_id'] = 1;

            if ($item['id'] ?? false) {
                $conn->update('ven_venda_item', $vendaItem, ['id' => $item['id']]);
            } else {
                $conn->insert('ven_venda_item', $vendaItem);
            }

            $this->vendaBusiness->recalcularTotais($venda->getId());

        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erro ao inserir item');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('ven_venda_form_itens', ['id' => $venda->getId()]);
    }


    /**
     *
     * @Route("/ven/venda/form/pagto/{id}", name="ven_venda_form_pagto", requirements={"id"="\d+"})
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendaFormPagto(Venda $venda)
    {
        if ($venda->itens->count() < 1) {
            $this->addFlash('warn', 'Nenhum item adicionado na compra');
            return $this->redirectToRoute('ven_venda_form_itens', ['id' => $venda->getId()]);
        }
        $params = [
            'listRoute' => 'ven_venda_listVendasEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form_pagamento.html.twig',
            'formRoute' => 'ven_venda_form_dados',
            'formPageTitle' => 'Venda',
            'e' => $venda,
        ];

        $this->vendaBusiness->recalcularTotais($venda->getId());

        $rsTotalPagtos = $this->entityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT sum(valor_pagto) totalPagtos FROM ven_venda_pagto WHERE venda_id = :vendaId', ['vendaId' => $venda->getId()]);
        $params['pagtos_total'] = $rsTotalPagtos[0]['totalPagtos'] ?? 0.0;
        $params['pagtos_diferenca'] = '0.00';
        if ((float)$venda->valorTotal > (float)($rsTotalPagtos[0]['totalPagtos'] ?? 0.0)) {
            $params['pagtos_diferenca'] = bcsub($venda->valorTotal, ($rsTotalPagtos[0]['totalPagtos'] ?? 0.0), 2);
        }
        $params['permiteMaisPagtos'] = (float)$params['pagtos_diferenca'] !== 0.0;
        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
        foreach ($venda->pagtos as $pagto) {
            // Para exibir na lista, por padrão pega a carteira_destino (caso seja null, então é movimentação direto no caixa)
            $carteiraId = $pagto->jsonData['carteira_destino_id'] ?? $carteiraId = $pagto->jsonData['carteira_id'];
            $pagto->carteira = $repoCarteira->find($carteiraId);
        }

        $carteirasCaixa = $repoCarteira->findByFiltersSimpl([['caixa', 'EQ', true]], ['descricao' => 'ASC']);
        $params['carteirasCaixa'] = json_encode(Select2JsUtils::toSelect2Data($carteirasCaixa, '%s', ['descricaoMontada']));


        /** @var PlanoPagtoRepository $repoPlanoPagto */
        $repoPlanoPagto = $this->getDoctrine()->getRepository(PlanoPagto::class);
        $params['planosPagto'] = json_encode($repoPlanoPagto->findAtuaisSelect2JS());


        return $this->doRender('Vendas/venda_form_pagamento.html.twig', $params);
    }

    /**
     *
     * @Route("/ven/venda/savePagto/{venda}", name="ven_venda_savePagto", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function savePagto(Request $request, Venda $venda)
    {
        try {
            if ($venda->status !== 'PV ABERTO') {
                throw new ViewException('Status difere de "PV ABERTO"');
            }

            $venda->recalcularTotais();
            if ($venda->getTotalPagtos() >= $venda->valorTotal) {
                throw new ViewException('Total de pagtos maior ou igual ao valor da venda');
            }

            $pagto = $request->get('pagto');

            $vendaPagto = [];

            $vendaPagto['venda_id'] = $venda->getId();
            $vendaPagto['plano_pagto_id'] = $pagto['planoPagto'];
            $vendaPagto['valor_pagto'] = abs(DecimalUtils::parseStr($pagto['valorPagto']));
            if ($vendaPagto['valor_pagto'] > $venda->valorTotal) {
                $vendaPagto['valor_pagto'] = $venda->valorTotal;
            }
            $vendaPagto['json_data'] = json_encode([
                'carteira_id' => $pagto['carteira'],
                'carteira_destino_id' => $pagto['carteira_destino'] ?? null,
                'num_parcelas' => $pagto['numParcelas'] ?? 0
            ]);


            $vendaPagto['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
            $vendaPagto['inserted'] = (new \DateTime())->format('Y-m-d H:i:s');
            $vendaPagto['estabelecimento_id'] = 1;
            $vendaPagto['user_inserted_id'] = 1;
            $vendaPagto['user_updated_id'] = 1;
            $conn = $this->entityHandler->getDoctrine()->getConnection();

            $conn->insert('ven_venda_pagto', $vendaPagto);


        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao inserir pagto');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $rsTotalPagtos = $this->entityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT sum(valor_pagto) totalPagtos FROM ven_venda_pagto WHERE venda_id = :vendaId', ['vendaId' => $venda->getId()]);
        $pagtos_diferenca = (float)bcsub($venda->valorTotal, ($rsTotalPagtos[0]['totalPagtos'] ?? 0.0), 2);
        $permiteMaisPagtos = $pagtos_diferenca !== 0.0;

        if ($permiteMaisPagtos) {
            return $this->redirectToRoute('ven_venda_form_pagto', ['id' => $venda->getId()]);
        } else {
            return $this->redirectToRoute('ven_venda_form_resumo', ['id' => $venda->getId()]);
        }
    }


    /**
     *
     * @Route("/ven/venda/form/resumo/{id}", name="ven_venda_form_resumo", requirements={"id"="\d+"})
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendaFormResumo(Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listVendasEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form_itens.html.twig', // para redirecionar
            'formRoute' => 'ven_venda_form_dados',
            'formPageTitle' => 'Venda',
            'e' => $venda
        ];

        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
        foreach ($venda->pagtos as $pagto) {
            // Para exibir na lista, por padrão pega a carteira_destino (caso seja null, então é movimentação direto no caixa)
            $carteiraId = $pagto->jsonData['carteira_destino_id'] ?? $carteiraId = $pagto->jsonData['carteira_id'];
            $pagto->carteira = $repoCarteira->find($carteiraId);
        }

        return $this->doRender('Vendas/venda_form_resumo.html.twig', $params);
    }


    /**
     *
     * @Route("/ven/venda/ecommerceForm/{id}", name="ven_venda_ecommerceForm", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param IntegradorECommerceFactory $integradorBusinessFactory
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function ecommerceForm(Request $request, IntegradorECommerceFactory $integradorBusinessFactory, Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listVendasPorDiaComEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_ecommerceForm.html.twig',
            'formRoute' => 'ven_venda_ecommerceForm',
            'formPageTitle' => 'Venda'
        ];

        if (!$venda) {
            // Este formulário não serve para inserir novas vendas
            return $this->redirectToRoute('ven_venda_listVendasPorDiaComEcommerce');
        }

        $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);
        if ($notaFiscal) {
            $params['notaFiscalVendaId'] = $notaFiscal->getId();
        }

        $fnHandleRequestOnValid = function (Request $request, $venda) use ($integradorBusinessFactory): void {
            $this->integrarVendaParaECommerce($venda, $integradorBusinessFactory);
        };

        return $this->doForm($request, $venda, $params, false, $fnHandleRequestOnValid);
    }

    /**
     *
     * @Route("/ven/venda/finalizarPV/{venda}", name="ven_venda_finalizarPV", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function finalizarPV(Request $request, Venda $venda): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('ven_venda_finalizarPV', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                $this->vendaBusiness->finalizarPV($venda);
                $this->addFlash('success', 'PV finalizado com sucesso');
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('ven_venda_form_resumo', ['id' => $venda->getId()]);
    }

    /**
     *
     * @Route("/est/venda/integrarVendaParaECommerce/{venda}", name="est_venda_integrarVendaParaECommerce")
     *
     * @param Venda|null $venda
     * @param IntegradorECommerceFactory $integradorBusinessFactory
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function integrarVendaParaECommerce(Venda $venda, IntegradorECommerceFactory $integradorBusinessFactory)
    {
        try {
            $integrador = $integradorBusinessFactory->getIntegrador();
            $integrador->integrarVendaParaECommerce($venda);
            $this->addFlash('success', 'Venda integrada com sucesso');
            $this->getSyslog()->info('Venda integrada com sucesso (id: ' . $venda->getId() . ')');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao integrar venda');
            $this->getSyslog()->info('Erro ao integrar venda (id: ' . $venda->getId() . ')', $e->getTraceAsString());
        }
        // agora está sendo chamado sempre após resalvar a venda
//        $route = $request->get('rtr') ?? 'ven_venda_ecommerceForm';
//        return $this->redirectToRoute($route, ['id' => $venda->getId()]);
    }


    /**
     *
     * @Route("/ven/venda/gerarNotaFiscal/{venda}", name="ven_venda_gerarNotaFiscal", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda $venda
     * @return RedirectResponse
     */
    public function gerarNotaFiscal(Request $request, Venda $venda): RedirectResponse
    {
        try {
            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);
            if (!$notaFiscal) {
                $notaFiscal = new NotaFiscal();
                $notaFiscal->setTipoNotaFiscal('NFE');
                $notaFiscal->setFinalidadeNf(FinalidadeNF::NORMAL['key']);
                $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscalVenda($venda, $notaFiscal, false);
            }
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            $route = $request->get('rtr') ?? 'ven_venda_ecommerceForm';
            return $this->redirectToRoute($route, ['id' => $venda->getId()]);
        }
    }


    /**
     *
     * @Route("/ven/venda/gerarNotaFiscalEcommerce/{venda}", name="ven_venda_gerarNotaFiscalEcommerce", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda $venda
     * @return RedirectResponse
     */
    public function gerarNotaFiscalEcommerce(Request $request, Venda $venda): RedirectResponse
    {
        try {

            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);

            if ($notaFiscal) {
                return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
            }

            $this->vendaBusiness->verificarPermiteFaturamento($venda);

            if (!$notaFiscal) {
                $notaFiscal = new NotaFiscal();
                $notaFiscal->setTipoNotaFiscal('NFE');
                $notaFiscal->setFinalidadeNf(FinalidadeNF::NORMAL['key']);

                $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscalVenda($venda, $notaFiscal, false);

                $rInfo = $this->notaFiscalEntityHandler->getDoctrine()->getConnection()
                    ->fetchAllAssociative('SELECT valor FROM cfg_app_config WHERE app_uuid = :appUUID AND chave = :chave',
                        ['appUUID' => $_SERVER['CROSIERAPPRADX_UUID'], 'chave' => 'fiscal.ecommerce.text_padrao_info_compl']
                    );
                $infoCompl = 'Pedido E-commerce: ' . $venda->jsonData['ecommerce_idPedido'] ?? '????';
                $infoCompl .= "\n\r" . ($rInfo[0]['valor'] ?? '');
                $notaFiscal->setInfoCompl($infoCompl);
                $this->notaFiscalEntityHandler->save($notaFiscal);
            }
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
        } catch (\Exception $e) {
            if ($e instanceof ViewException) {
                $msg = $e->getMessage();
            } else {
                $msg = 'Ocorreu um erro ao faturar';
            }
            $this->addFlash('error', $msg);
            $route = $request->get('rtr') ?? 'ven_venda_ecommerceForm';
            return $this->redirectToRoute($route, ['id' => $venda->getId()]);
        }
    }


    /**
     *
     * @Route("/ven/venda/consultarStatus/{notaFiscal}/{venda}", name="ven_venda_consultarStatus")
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse
     * @throws ViewException
     */
    public function consultarStatus(Request $request, NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        $this->notaFiscalBusiness->consultarStatus($notaFiscal);
        $route = $request->get('rtr') ?? 'ven_venda_form_dados';
        return $this->redirectToRoute($route, ['venda' => $venda->getId()]);
    }


    /**
     *
     * @Route("/ven/venda/listVendasPorDiaComECommerce", name="ven_venda_listVendasPorDiaComEcommerce")
     * @param Request $request
     * @return Response
     *
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function listVendasPorDiaComEcommerce(Request $request): Response
    {
        $params = [
            'formRoute' => 'ven_venda_form_dados',
            'listView' => 'Vendas/venda_listVendasPorDiaComEcommerce.html.twig',
            'listRoute' => 'ven_venda_listVendasPorDiaComEcommerce',
            'listPageTitle' => 'Vendas',
            'listId' => 'ven_venda_listVendasPorDiaComEcommerce'
        ];

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('ven_venda_json_metadata'), true);
        $sugestoes = $jsonMetadata['campos']['canal']['sugestoes'];
        $sugestoes = array_combine($sugestoes, $sugestoes);
        $params['canais'] = json_encode(Select2JsUtils::arrayToSelect2Data($sugestoes, null, '...'));

        $status = $jsonMetadata['status']['opcoes'] ?? [];
        $params['statuss'] = json_encode(Select2JsUtils::arrayToSelect2Data(array_combine($status, $status)));
        $statusECommerce = $jsonMetadata['campos']['ecommerce_status']['sugestoes'] ?? [];
        $params['statusECommerce'] = json_encode(Select2JsUtils::arrayToSelect2Data($statusECommerce));


        $filter = $request->get('filter');

        if (!isset($filter['dtsVenda'])) {
            $hj = new \DateTime();
            $dtsVenda = ['i' => $hj, 'f' => $hj];
            $params['fixedFilters']['filter']['dtsVenda'] = $dtsVenda['i']->format('d/m/Y') . ' - ' . $dtsVenda['i']->format('d/m/Y');
        } else {
            $dtsVenda = DateTimeUtils::parseConcatDates($filter['dtsVenda']);
        }
        /** @var VendaRepository $repoVenda */
        $repoVenda = $this->getRepository();
        $vendedoresNoPeriodo = $repoVenda->findVendedoresComVendasNoPeriodo_select2js($dtsVenda['i'], $dtsVenda['f']);
        $params['vendedores'] = json_encode($vendedoresNoPeriodo);

        $params['ecomm_info_integra'] = $repoAppConfig->findByChave('ecomm_info_integra');

        $params['orders'] = ['dtVenda' => 'ASC'];

        $fnGetFilterDatas = function (array $params) {
            return [
                new FilterData(['dtVenda'], 'BETWEEN_DATE_CONCAT', 'dtsVenda', $params),
                new FilterData(['canal'], 'EQ', 'canal', $params, null, true),
                new FilterData(['status'], 'EQ', 'status', $params),
                new FilterData(['statusECommerce'], 'EQ', 'statusECommerce', $params, null, true),
                new FilterData(['vendedor_codigo'], 'EQ', 'vendedor', $params, null, true),
            ];
        };

        $fnHandleDadosList = function (&$dados) {
            $dia = null;
            $dias = [];
            $i = -1;
            /** @var Venda $venda */
            $total = 0.0;
            foreach ($dados as $venda) {
                if ($venda->dtVenda->format('d/m/Y') !== $dia) {
                    $i++;
                    $dias[$i]['totalDia'] = 0.0;
                    $dia = $venda->dtVenda->format('d/m/Y');
                    $dias[$i]['dtVenda'] = $venda->dtVenda;
                }
                $dias[$i]['vendas'][] = $venda;
                $dias[$i]['totalDia'] = bcadd($dias[$i]['totalDia'], $venda->valorTotal, 2);
                $total = bcadd($total, $venda->valorTotal, 2);
            }
            $dados['dias'] = $dias;
            $dados['total'] = $total;
        };

        return $this->doListSimpl($request, $params, $fnGetFilterDatas, $fnHandleDadosList);
    }

    /**
     *
     * @Route("/ven/venda/deleteItem/{item}", name="ven_venda_deleteItem", requirements={"item"="\d+"})
     * @param Request $request
     * @param VendaItem $item
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function deleteItem(Request $request, VendaItem $item): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('ven_venda_deleteItem', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                $this->vendaItemEntityHandler->delete($item);
                $this->vendaBusiness->recalcularTotais($item->venda->getId());
                $this->addFlash('success', 'Registro deletado com sucesso.');
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }

        return $this->redirectToRoute('ven_venda_form_itens', ['id' => $item->venda->getId()]);
    }

    /**
     *
     * @Route("/ven/venda/deletePagto/{pagto}", name="ven_venda_deletePagto", requirements={"pagto"="\d+"})
     * @param Request $request
     * @param VendaPagto $pagto
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function deletePagto(Request $request, VendaPagto $pagto): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('ven_venda_deletePagto', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                $this->entityHandler->getDoctrine()->getConnection()->delete('ven_venda_pagto', ['id' => $pagto->getId()]);
                $this->addFlash('success', 'Registro deletado com sucesso.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }

        return $this->redirectToRoute('ven_venda_form_pagto', ['id' => $pagto->venda->getId()]);
    }

    /**
     *
     * @Route("/ven/venda/obterVendasECommerce/{dtVenda}", name="ven_venda_obterVendasECommerce", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorECommerceFactory $integradorBusinessFactory
     * @param \DateTime $dtVenda
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function obterVendasECommerce(Request $request, IntegradorECommerceFactory $integradorBusinessFactory, ?\DateTime $dtVenda = null)
    {
        if (!$dtVenda) {
            $dtVenda = new \DateTime();
        }
        $integrador = $integradorBusinessFactory->getIntegrador();

        $resalvar = $request->get('resalvar') === 'S';

        $vendasObtidas = $integrador->obterVendas($dtVenda, $resalvar);

        if ($vendasObtidas) {
            $this->addFlash('success', $vendasObtidas . ' venda(s) obtida(s)');
        } else {
            $this->addFlash('warn', ' Nenhuma venda obtida');
        }

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }


    /**
     * Lista apenas as vendas do ecommerce.
     *
     * @Route("/ven/venda/listVendasEcommerce", name="ven_venda_listVendasEcommerce")
     * @param Request $request
     * @return Response
     *
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function listVendasEcommercePorDia(Request $request): Response
    {
        $params = [
            'listView' => 'Vendas/venda_listVendasEcommerce.html.twig',
            'listRoute' => 'ven_venda_listVendasEcommerce',
            'listPageTitle' => 'Vendas E-commerce',
        ];

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('ven_venda_json_metadata'), true);
        $sugestoes = $jsonMetadata['campos']['canal']['sugestoes'];
        $sugestoes = array_combine($sugestoes, $sugestoes);
        $params['canais'] = json_encode(Select2JsUtils::arrayToSelect2Data($sugestoes, null, '...'));

        $statusECommerce = $jsonMetadata['campos']['ecommerce_status']['sugestoes'] ?? [];
        $arrStatusECommerce = [];
        foreach ($statusECommerce as $s) {
            $arrStatusECommerce[] = $s;
        }
        $params['statusECommerce'] = implode(',', $arrStatusECommerce);

        $coresStatus = json_decode($repoAppConfig->findByChave('ecomm_info.status.json'), true);
        $params['coresStatus'] = $coresStatus;

        $filter = $request->get('filter');

        if (!isset($filter['dtsVenda'])) {
            $hj = new \DateTime();
            $dtsVenda = ['i' => $hj, 'f' => $hj];
            $params['fixedFilters']['filter']['dtsVenda'] = $dtsVenda['i']->format('d/m/Y') . ' - ' . $dtsVenda['i']->format('d/m/Y');
        }
        $params['fixedFilters']['filter']['canal'] = 'ECOMMERCE';

        $params['ecomm_info_integra'] = $repoAppConfig->findByChave('ecomm_info_integra');

        $params['orders'] = ['dtVenda' => 'DESC'];

        $fnGetFilterDatas = function (array $params) {
            return [
                new FilterData(['dtVenda'], 'BETWEEN_DATE_CONCAT', 'dtsVenda', $params),
                new FilterData(['canal'], 'EQ', 'canal', $params, null, true),
                new FilterData(['status'], 'IN', 'status', $params),
                new FilterData(['ecommerce_status_descricao'], 'IN', 'statusECommerce', $params, null, true),
                new FilterData(['vendedor_codigo'], 'EQ', 'vendedor', $params, null, true),
            ];
        };

        $fnHandleDadosList = function (&$dados) {
            $dia = null;
            $dias = [];
            $i = -1;
            /** @var Venda $venda */
            foreach ($dados as $venda) {
                if ($venda->dtVenda->format('d/m/Y') !== $dia) {
                    $i++;
                    $dias[$i]['totalDia'] = 0.0;
                    $dia = $venda->dtVenda->format('d/m/Y');
                    $dias[$i]['dtVenda'] = $venda->dtVenda;
                }
                $dias[$i]['vendas'][] = $venda;
                $dias[$i]['totalDia'] = bcadd($dias[$i]['totalDia'], $venda->valorTotal, 2);
            }
            $dados = $dias;
        };

        return $this->doListSimpl($request, $params, $fnGetFilterDatas, $fnHandleDadosList);
    }


    /**
     *
     * @Route("/ven/venda/findProdutosByCodigoOuNomeJson/", name="ven_venda_findProdutosByCodigoOuNomeJson")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function findProdutosByCodigoOuNomeJson(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');

            $conn = $this->entityHandler->getDoctrine()->getConnection();

            // Pesquisa o produto e seu preço já levando em consideração a unidade padrão
            $sql = 'SELECT prod.id, prod.codigo, prod.nome, prod.json_data->>"$.qtde_min_para_atacado" as qtde_min_para_atacado, ' .
                'preco.preco_prazo as precoVenda, u.id as unidade_id, ' .
                'u.label as unidade_label, u.casas_decimais as unidade_casas_decimais ' .
                'FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'JOIN est_unidade u ON preco.unidade_id = u.id AND u.id = prod.unidade_padrao_id ' .
                'JOIN est_lista_preco lista ON preco.lista_id = lista.id ' .
                'WHERE preco.atual AND lista.descricao = \'VAREJO\' AND (' .
                'prod.nome LIKE :nome OR ' .
                'prod.codigo LIKE :codigo) ORDER BY prod.nome LIMIT 20';

            $rs = $conn->fetchAllAssociative($sql,
                [
                    'nome' => '%' . $str . '%',
                    'codigo' => '%' . $str
                ]);
            $results = $this->handleResultProdutoSelect2($rs);

            if (count($results) === 1 && $results[0]['codigo'] === $str) {
                $results[0]['codigoExato'] = true;
            }

            return new JsonResponse(
                ['results' => $results]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }


    /**
     *
     * @Route("/ven/venda/findProdutoById/", name="ven_venda_findProdutoById")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function findProdutoById(Request $request): JsonResponse
    {
        try {
            $id = $request->get('term');

            // Pesquisa o produto e seu preço já levando em consideração a unidade padrão
            $sql = 'SELECT prod.id, prod.codigo, prod.nome, prod.json_data->>"$.qtde_min_para_atacado" as qtde_min_para_atacado, ' .
                'preco.preco_prazo as precoVenda, u.id as unidade_id, ' .
                'u.label as unidade_label, u.casas_decimais as unidade_casas_decimais ' .
                'FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'JOIN est_unidade u ON preco.unidade_id = u.id AND u.id = prod.unidade_padrao_id ' .
                'JOIN est_lista_preco lista ON preco.lista_id = lista.id ' .
                'WHERE preco.atual AND lista.descricao = \'VAREJO\' AND prod.id = :id';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAllAssociative($sql,
                [
                    'id' => $id,
                ]);
            if (!$rs) {
                $this->logger->error('Produto não encontrado para id ="' . $id . '"');
            }

            $results = $this->handleResultProdutoSelect2($rs);

            return new JsonResponse(
                ['results' => $results]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }


    /**
     * Monta o resultado esperado pelo campo select2js do produto.
     * Este método é chamado tanto quando a buscar vem pelo , quando pelo findProdutoById
     *
     * @param array $rs
     * @return array
     */
    private function handleResultProdutoSelect2(array $rs): array
    {
        $conn = $this->entityHandler->getDoctrine()->getConnection();

        $results = [];


        $sqlUnidades = 'SELECT preco.produto_id, u.id, u.label as text ' .
            'FROM est_produto_preco preco, est_unidade u ' .
            'WHERE preco.unidade_id = u.id AND preco.atual IS TRUE AND preco.produto_id = :produtoId GROUP BY preco.produto_id, u.id, u.label ORDER BY u.label';
        $stmtUnidades = $conn->prepare($sqlUnidades);


        // Melhora a disposição do array para facilitar o acesso no javascript
        function handlePrecos(Connection $conn, array $rsUnidades)
        {
            $sqlPrecos = 'SELECT u.id, u.label as text, preco.preco_prazo, lista.descricao as lista ' .
                'FROM est_produto_preco preco, est_unidade u, est_lista_preco lista ' .
                'WHERE preco.unidade_id = u.id AND ' .
                'u.label = :unidade AND ' .
                'preco.lista_id = lista.id AND ' .
                'lista.descricao = :listaDescricao AND ' .
                'preco.atual IS TRUE AND ' .
                'preco.produto_id = :produtoId';
            $stmtPrecos = $conn->prepare($sqlPrecos);

            $rs = [];
            foreach ($rsUnidades as $rUnidadesPrecos) {

                $stmtPrecos->bindValue('produtoId', $rUnidadesPrecos['produto_id']);
                $stmtPrecos->bindValue('unidade', $rUnidadesPrecos['text']);
                $stmtPrecos->bindValue('listaDescricao', 'ATACADO');
                $stmtPrecos->execute();
                $rPrecoAtacado = $stmtPrecos->fetchAssociative();

                $stmtPrecos->bindValue('listaDescricao', 'VAREJO');
                $stmtPrecos->execute();
                $rPrecoVarejo = $stmtPrecos->fetchAssociative();

                // Se não tiver preço no ATACADO, utiliza o de VAREJO
                $rs[$rUnidadesPrecos['text']]['ATACADO'] = $rPrecoAtacado ?: $rPrecoVarejo;
                $rs[$rUnidadesPrecos['text']]['VAREJO'] = $rPrecoVarejo;
            }
            return $rs;
        }

        foreach ($rs as $r) {
            $codigo = str_pad($r['codigo'], 9, '0', STR_PAD_LEFT);

            $stmtUnidades->bindValue('produtoId', $r['id']);
            $stmtUnidades->execute();
            $rUnidades = $stmtUnidades->fetchAllAssociative();


            $results[] = [
                'id' => $r['id'],
                'text' => $codigo . ' - ' . $r['nome'] . '(' . $r['unidade_label'] . ')',
                'codigo' => $codigo,
                'preco_venda' => $r['precoVenda'],
                'qtde_min_para_atacado' => $r['qtde_min_para_atacado'],
                'unidade_id' => $r['unidade_id'],
                'unidade_label' => $r['unidade_label'],
                'unidade_casas_decimais' => $r['unidade_casas_decimais'],
                'unidades' => $rUnidades,
                'precos' => handlePrecos($conn, $rUnidades)
            ];
        }

        return $results;
    }


    /**
     *
     * @Route("/ven/venda/imprimirPV/{venda}", name="ven_venda_imprimirPV", requirements={"venda"="\d+"})
     * @param Venda $venda
     * @return Response
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function imprimirPV(Venda $venda): Response
    {
        $html = $this->renderView('/Vendas/pv.html.twig', ['venda' => $venda]);

        $this->knpSnappyPdf->setOption('page-width', '8cm');
        $this->knpSnappyPdf->setOption('page-height', '20cm');

        return new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html),
            'pv_' . $venda->getId() . '.pdf', 'application/pdf', 'inline'
        );
    }


    /**
     *
     * @Route("/ven/venda/findClienteByStr/", name="ven_venda_findClienteByStr")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function findClienteByStr(Request $request): JsonResponse
    {
        $str = $request->get('term') ?? '';

        $rs = $this->entityHandler->getDoctrine()->getConnection()
            ->fetchAllAssociative('SELECT id, documento, nome, json_data FROM crm_cliente WHERE documento = :documento OR nome LIKE :nome LIMIT 30',
                [
                    'documento' => preg_replace("/[^0-9]/", "", $str),
                    'nome' => '%' . $str . '%'
                ]);

        $clientes = [];

        foreach ($rs as $r) {
            $clientes[] = [
                'id' => $r['id'],
                'text' => $r['nome'],
                'documento' => StringUtils::mascararCnpjCpf($r['documento']),
                'json_data' => json_decode($r['json_data'], true)
            ];
        }

        return new JsonResponse(
            ['results' => $clientes]
        );
    }


    /**
     *
     * @Route("/venda/venda/clonar/{venda}/", name="ven_venda_clonar", requirements={"venda"="\d+"})
     *
     * @param Request $request
     * @param Venda $venda
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function clonar(Request $request, Venda $venda): RedirectResponse
    {
        try {
            if (!$this->isCsrfTokenValid('ven_venda_clonar', $request->get('token'))) {
                throw new ViewException('Token inválido');
            }
            $clone = $this->entityHandler->doClone($venda);
            $this->addFlash('success', 'Registro clonado com sucesso');
            return $this->redirectToRoute('ven_venda_form_dados', ['id' => $clone->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao clonar o registro');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('ven_venda_form_dados', ['id' => $venda->getId()]);
        }
    }


}