<?php

namespace App\Controller\Vendas;

use App\Business\ECommerce\IntegradorBusinessFactory;
use App\Form\Vendas\VendaType;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Vendas\VendaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
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
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\PlanoPagtoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaItemRepository;
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
     * @Route("/ven/venda/form/{id}", name="ven_venda_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function form(Request $request, Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listPorDia',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form.html.twig',
            'formRoute' => 'ven_venda_form',
            'formPageTitle' => 'Venda'
        ];

        if (!$venda) {
            $venda = new Venda();
            $venda->dtVenda = new \DateTime();
            $venda->status = 'PV ABERTO';
            $venda->jsonData['canal'] = 'LOJA FÍSICA';

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

            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);
            /** @var Colaborador $colaborador */
            $colaborador = $repoColaborador->findOneBy(['nome' => 'NÃO INFORMADO']);
            $venda->vendedor = $colaborador;

            $venda->jsonData['cliente_documento'] = '99999999999';
            $venda->jsonData['cliente_nome'] = 'NÃO IDENTIFICADO';
            $venda->subtotal = 0.0;
            $venda->desconto = 0.0;
        } else {
            $rsTotalPagtos = $this->entityHandler->getDoctrine()->getConnection()->fetchAll('SELECT sum(valor_pagto) totalPagtos FROM ven_venda_pagto WHERE venda_id = :vendaId', ['vendaId' => $venda->getId()]);
            $params['pagtos_total'] = $rsTotalPagtos[0]['totalPagtos'] ?? 0.0;
            $params['pagtos_diferenca'] = bcsub($venda->getValorTotal(), $rsTotalPagtos[0]['totalPagtos'] ?? 0.0, 2);
        }

        /** @var PlanoPagtoRepository $repoPlanoPagto */
        $repoPlanoPagto = $this->getDoctrine()->getRepository(PlanoPagto::class);
        $params['planosPagto'] = json_encode($repoPlanoPagto->findAtuaisSelect2JS());


        return $this->doForm($request, $venda, $params);
    }


    /**
     *
     * @Route("/ven/venda/formItem/{venda}", name="ven_venda_formItem", defaults={"venda"=null}, requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function formItem(Request $request, Venda $venda = null)
    {
        try {
            if ($venda->status !== 'PV ABERTO') {
                throw new ViewException('Status difere de "PV ABERTO"');
            }

            $conn = $this->entityHandler->getDoctrine()->getConnection();

            $item = $request->get('item');

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
                $unidadeId = (int)$item['unidade'];
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
                        $itemNaVenda->qtde = $itemNaVenda->devolucao ? $itemNaVenda->qtde * -1 : $itemNaVenda->qtde;

                        $itemNaVenda->desconto = $desconto;
                        $this->vendaItemEntityHandler->save($itemNaVenda);
                        return $this->redirectToRoute('ven_venda_form', ['id' => $venda->getId()]);
                    }
                }
            }

            $vendaItem = [];


            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->find($produtoId);

            $vendaItem['produto_id'] = $produtoId;

            $vendaItem['venda_id'] = $venda->getId();


            $repoProdutoPreco = $this->getDoctrine()->getRepository(ProdutoPreco::class);
            $precoAtual = $repoProdutoPreco->findBy(['produto' => $produto, 'atual' => true, 'unidade' => $unidadeId]);
            if (!$precoAtual) {
                throw new ViewException('Preço atual não encontrado');
            }
            /** @var ProdutoPreco $precoAtual */
            $precoAtual = $precoAtual[0];

            $vendaItem['preco_venda'] = $precoAtual->precoPrazo;

            $vendaItem['descricao'] = $produto->nome;
            if (!($item['id'] ?? false)) {
                $vendaItem['ordem'] = $venda->itens->count() + 1;
            }

            $vendaItem['unidade_id'] = $unidadeId;

            $vendaItem['devolucao'] = $devolucao;
            $vendaItem['qtde'] = ($vendaItem['devolucao']) ? abs($qtde) * -1 : abs($qtde);


            $vendaItem['subtotal'] = bcmul($vendaItem['qtde'], $vendaItem['preco_venda'], 2);
            $vendaItem['desconto'] = $desconto;
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

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao inserir item');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('ven_venda_form', ['id' => $venda->getId()]);
    }


    /**
     *
     * @Route("/ven/venda/formPagto/{venda}", name="ven_venda_formPagto", defaults={"venda"=null}, requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function formPagto(Request $request, Venda $venda = null)
    {
        try {
            if ($venda->status !== 'PV ABERTO') {
                throw new ViewException('Status difere de "PV ABERTO"');
            }

            $pagto = $request->get('pagto');


            $vendaPagto = [];

            $vendaPagto['venda_id'] = $venda->getId();
            $vendaPagto['plano_pagto_id'] = $pagto['planoPagto'];
            $vendaPagto['valor_pagto'] = abs(DecimalUtils::parseStr($pagto['valorPagto']));

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
        return $this->redirectToRoute('ven_venda_form', ['id' => $venda->getId()]);
    }


    /**
     *
     * @Route("/ven/venda/ecommerceForm/{id}", name="ven_venda_ecommerceForm", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function ecommerceForm(Request $request, Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listVendasPorDiaComEcommerce',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_ecommerceForm.html.twig',
            'formRoute' => 'ven_venda_ecommerceForm',
            'formPageTitle' => 'Venda'
        ];

        $params['permiteFaturamento'] = ($venda->jsonData['ecommerce_status_descricao'] ?? '') === 'Pedido em Separação';

        if (!$venda) {
            // Este formulário não serve para inserir novas vendas
            return $this->redirectToRoute('ven_venda_listVendasPorDiaComEcommerce');
        }

        return $this->doForm($request, $venda, $params);
    }

    /**
     *
     * @Route("/est/venda/integrarVendaParaECommerce/{venda}", name="est_venda_integrarVendaParaECommerce")
     *
     * @param Request $request
     * @param Venda|null $venda
     * @param IntegradorBusinessFactory $integradorBusinessFactory
     * @return Response
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function integrarVendaParaECommerce(Request $request, Venda $venda, IntegradorBusinessFactory $integradorBusinessFactory)
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
        $route = $request->get('rtr') ?? 'ven_venda_ecommerceForm';
        return $this->redirectToRoute($route, ['id' => $venda->getId()]);
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
                $notaFiscal->setDocumentoDestinatario($venda->cliente->documento);
                $notaFiscal->setXNomeDestinatario($venda->cliente->nome);
                $notaFiscal->setLogradouroDestinatario($venda->jsonData['ecommerce_entrega_logradouro']);
                $notaFiscal->setNumeroDestinatario($venda->jsonData['ecommerce_entrega_numero']);
                $notaFiscal->setBairroDestinatario($venda->jsonData['ecommerce_entrega_bairro']);
                $notaFiscal->setCepDestinatario($venda->jsonData['ecommerce_entrega_cep']);
                $notaFiscal->setCidadeDestinatario($venda->jsonData['ecommerce_entrega_cidade']);
                $notaFiscal->setEstadoDestinatario($venda->jsonData['ecommerce_entrega_uf']);
                $notaFiscal->setFoneDestinatario($venda->jsonData['ecommerce_entrega_telefone']);
                $this->notaFiscalEntityHandler->save($notaFiscal);
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
        $route = $request->get('rtr') ?? 'ven_venda_form';
        return $this->redirectToRoute($route, ['venda' => $venda->getId()]);
    }

    /**
     * @param Request $request
     * @param $venda
     */
    public function handleRequestOnValid(Request $request, /** @var Venda @venda */ $venda): void
    {
        if ($request->get('item')) {

            $itemArr = $request->get('item');

            if (!isset($itemArr['produto'])) {
                return;
            }

            /** @var VendaItem $vendaItem */
            if ($itemArr['id'] ?? null) {
                /** @var VendaItemRepository $repoVendaItem */
                $repoVendaItem = $this->getDoctrine()->getRepository(VendaItem::class);
                $vendaItem = $repoVendaItem->find($itemArr['id']);
            } else {
                $vendaItem = new VendaItem();
            }

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->find($itemArr['produto']);

            $vendaItem->produto = $produto;

            $vendaItem->qtde = DecimalUtils::parseStr($itemArr['qtde']);
            $vendaItem->precoVenda = DecimalUtils::parseStr($itemArr['precoVenda']);
            $vendaItem->desconto = DecimalUtils::parseStr($itemArr['desconto']);
            $vendaItem->valorTotal = DecimalUtils::parseStr($itemArr['valorTotal']);

            $vendaItem->venda = $venda;

            try {
                $this->vendaItemEntityHandler->save($vendaItem);
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
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
            'formRoute' => 'ven_venda_form',
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
        $vendedoresNoPeriodo = $this->getRepository()->findVendedoresComVendasNoPeriodo_select2js($dtsVenda['i'], $dtsVenda['f']);
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
            foreach ($dados as $venda) {
                if ($venda->dtVenda->format('d/m/Y') !== $dia) {
                    $i++;
                    $dias[$i]['totalDia'] = 0.0;
                    $dia = $venda->dtVenda->format('d/m/Y');
                    $dias[$i]['dtVenda'] = $venda->dtVenda;
                }
                $dias[$i]['vendas'][] = $venda;
                $dias[$i]['totalDia'] = bcadd($dias[$i]['totalDia'], $venda->getValorTotal(), 2);
            }
            $dados = $dias;
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
                $this->addFlash('success', 'Registro deletado com sucesso.');
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }

        return $this->redirectToRoute('ven_venda_form', ['id' => $item->venda->getId()]);
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
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }

        return $this->redirectToRoute('ven_venda_form', ['id' => $pagto->venda->getId()]);
    }

    /**
     *
     * @Route("/ven/venda/obterVendasECommerce/{dtVenda}", name="ven_venda_obterVendasECommerce", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorBusinessFactory $integradorBusinessFactory
     * @param \DateTime $dtVenda
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_VENDAS_ADMIN", statusCode=403)
     */
    public function obterVendasECommerce(Request $request, IntegradorBusinessFactory $integradorBusinessFactory, ?\DateTime $dtVenda = null)
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

        $status = $jsonMetadata['status']['opcoes'] ?? [];
        $params['statuss'] = json_encode(Select2JsUtils::arrayToSelect2Data(array_combine($status, $status)));
        $statusECommerce = $jsonMetadata['campos']['ecommerce_status']['sugestoes'] ?? [];
        $params['statusECommerce'] = json_encode(Select2JsUtils::arrayToSelect2Data($statusECommerce));


        $filter = $request->get('filter');

        if (!isset($filter['dtsVenda'])) {
            $hj = new \DateTime();
            $dtsVenda = ['i' => $hj, 'f' => $hj];
            $params['fixedFilters']['filter']['dtsVenda'] = $dtsVenda['i']->format('d/m/Y') . ' - ' . $dtsVenda['i']->format('d/m/Y');
        }
        $params['fixedFilters']['filter']['canal'] = 'ECOMMERCE';

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
            foreach ($dados as $venda) {
                if ($venda->dtVenda->format('d/m/Y') !== $dia) {
                    $i++;
                    $dias[$i]['totalDia'] = 0.0;
                    $dia = $venda->dtVenda->format('d/m/Y');
                    $dias[$i]['dtVenda'] = $venda->dtVenda;
                }
                $dias[$i]['vendas'][] = $venda;
                $dias[$i]['totalDia'] = bcadd($dias[$i]['totalDia'], $venda->getValorTotal(), 2);
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

            // Pesquisa o produto e seu preço já levando em consideração a unidade padrão
            $sql = 'SELECT prod.id, prod.codigo, prod.nome, preco.preco_prazo as precoVenda, u.id as unidade_id, u.label as unidade_label, u.casas_decimais as unidade_casas_decimais ' .
                'FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'JOIN est_unidade u ON prod.unidade_padrao_id = u.id AND preco.unidade_id = u.id ' .
                'WHERE preco.atual AND (' .
                'prod.nome LIKE :nome OR ' .
                'prod.codigo LIKE :codigo) ORDER BY prod.nome LIMIT 20';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAll($sql,
                [
                    'nome' => '%' . $str . '%',
                    'codigo' => '%' . $str
                ]);
            $results = [];

            $sqlUnidades = 'SELECT u.id, u.label as text, preco.preco_prazo FROM est_produto_preco preco, est_unidade u WHERE preco.unidade_id = u.id AND preco.atual IS TRUE AND preco.produto_id = :produtoId';
            $stmtUnidades = $this->entityHandler->getDoctrine()->getConnection()->prepare($sqlUnidades);

            foreach ($rs as $r) {
                $codigo = str_pad($r['codigo'], 9, '0', STR_PAD_LEFT);
                $stmtUnidades->bindValue('produtoId', $r['id']);
                $stmtUnidades->execute();
                $rUnidades = $stmtUnidades->fetchAll();
                $results[] = [
                    'id' => $r['id'],
                    'text' => $codigo . ' - ' . $r['nome'],
                    'preco_venda' => $r['precoVenda'],
                    'unidade_id' => $r['unidade_id'],
                    'unidade_label' => $r['unidade_label'],
                    'unidade_casas_decimais' => $r['unidade_casas_decimais'],
                    'unidades' => $rUnidades
                ];
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
     * @Route("/ven/venda/findProdutoPrecoByProdutoEUnidadeJson/{produto}/{unidade}/", name="ven_venda_findProdutoPrecoByProdutoEUnidadeJson", requirements={"produto"="\d+","unidade"="\d+"})
     * @param Produto $produto
     * @param Unidade $unidade
     * @return JsonResponse
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function findProdutoPrecoByProdutoEUnidadeJson(Produto $produto, Unidade $unidade): JsonResponse
    {
        try {
            foreach ($produto->precos as $produtoPreco) {
                if ($produtoPreco->unidade->getId() === $unidade->getId()) {
                    return new JsonResponse(['result' => [
                        'preco_prazo' => $produtoPreco->precoPrazo
                    ]]);
                }
            }
            return new JsonResponse(['err' => 'Nenhum preço encontrado para produto (id = "' . $produto->getId() . '") e unidade "' . $unidade->label . '"']);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => 'Erro ao pesquisar preços - produto (id = "' . $produto->getId() . '") e unidade "' . $unidade->label . '"']);
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

            $sql = 'SELECT prod.id, prod.codigo, prod.nome, preco.preco_prazo as precoVenda, u.label as unidade_label, u.casas_decimais as unidade_casas_decimais ' .
                'FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'JOIN est_unidade u ON prod.unidade_id = u.id ' .
                'WHERE preco.atual AND prod.id = :id';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAll($sql,
                [
                    'id' => $id,
                ]);
            if (!$rs) {
                $this->logger->error('Produto não encontrado para id ="' . $id . '"');
            }
            $r = $rs[0];
            $results = [];

            $codigo = str_pad($r['codigo'], 9, '0', STR_PAD_LEFT);
            $results[] = [
                'id' => $r['id'],
                'text' => $codigo . ' - ' . $r['nome'],
                'preco_venda' => $r['precoVenda'],
                'unidade_label' => $r['unidade_label'],
                'unidade_casas_decimais' => $r['unidade_casas_decimais'],
            ];

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
     * @Route("/ven/venda/imprimirPV/{venda}", name="ven_venda_imprimirPV", defaults={"venda"=null}, requirements={"venda"="\d+"})
     * @param Venda $venda
     * @return Response
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function imprimirDANFCE(Venda $venda): Response
    {
        $html = $this->renderView('/Vendas/pv.html.twig', ['venda' => $venda]);

        $this->knpSnappyPdf->setOption('page-width', '8cm');
        $this->knpSnappyPdf->setOption('page-height', '20cm');

        return new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html),
            'pv_' . $venda->getId() . '.pdf', 'application/pdf', 'inline'
        );
    }


}