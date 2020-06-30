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
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\FinalidadeNF;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaItemRepository;
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
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
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
            $venda->status = 'PV';
            $venda->jsonData['canal'] = 'LOJA FÍSICA';
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->getDoctrine()->getRepository(Cliente::class);
            /** @var Cliente $consumidorNaoIdentificado */
            $consumidorNaoIdentificado = $repoCliente->findOneBy(['documento' => '99999999999']);
            $venda->cliente = $consumidorNaoIdentificado;
            $venda->subtotal = 0.0;
            $venda->desconto = 0.0;
        }

        return $this->doForm($request, $venda, $params);
    }


    /**
     *
     * @Route("/ven/venda/ecommerceForm/{id}", name="ven_venda_ecommerceForm", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
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
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
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
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
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
     * @Route("/ven/venda/obterVendasECommerce/{dtVenda}", name="ven_venda_obterVendasECommerce", defaults={"dtVenda": null})
     * @ParamConverter("dtVenda", options={"format": "Y-m-d"})
     *
     * @param Request $request
     * @param IntegradorBusinessFactory $integradorBusinessFactory
     * @param \DateTime $dtVenda
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
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
     * @Route("/ven/venda/findProdutosByIdOuNomeJson/", name="ven_venda_findProdutosByIdOuNomeJson")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutosByIdOuNomeJson(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');

            $sql = 'SELECT prod.id, prod.nome, prod.json_data, preco.preco_prazo FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'WHERE preco.atual AND (' .
                'prod.id LIKE :str OR ' .
                'prod.nome LIKE :str OR ' .
                'json_data->>"$.codigo" LIKE :str) ORDER BY prod.nome LIMIT 20';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAll($sql, ['str' => '%' . $str . '%']);
            $results = [];
            foreach ($rs as $r) {
                $jsonData = json_decode($r['json_data'], true);
                $precoVenda = $r['preco_prazo'] ?? $jsonData['preco_tabela'] ?? 0.0;
                $codigo = str_pad($jsonData['codigo'] ?? $r['id'], 6, '0', STR_PAD_LEFT);
                $results[] = [
                    'id' => $r['id'],
                    'nome' => $codigo . ' - ' . $r['nome'],
                    'preco_venda' => $precoVenda,
                    'unidade' => $jsonData['unidade'] ?? 'UN'
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


}