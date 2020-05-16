<?php

namespace App\Controller\Vendas;

use App\Form\Vendas\VendaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaItemRepository;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['dtVenda'], 'EQ', 'dtVenda', $params, 'date'),
            new FilterData(['canal'], 'EQ', 'canal', $params, null, true),
        ];
    }

    /**
     *
     * @Route("/ven/venda/listPorDia", name="ven_venda_listPorDia")
     * @param Request $request
     * @param \DateTime $dia
     * @return Response
     *
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendasPorDia(Request $request): Response
    {
        $params = [
            'formRoute' => 'ven_venda_form',
            'listView' => 'Vendas/vendasPorDia_list.html.twig',
            'listRoute' => 'ven_venda_listPorDia',
            'listPageTitle' => 'Vendas',
            'listId' => 'ven_venda_listPorDia'
        ];

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('ven_venda_json_metadata'), true);
        $sugestoes = $jsonMetadata['campos']['canal']['sugestoes'];
        $sugestoes = array_combine($sugestoes, $sugestoes);
        $params['canais'] = json_encode(array_merge(['TODAS' => null], $sugestoes));

        $filter = $request->get('filter');

        if (!isset($filter['dtVenda'])) {
            $dtVenda = new \DateTime();
            $params['fixedFilters']['filter']['dtVenda'] = $dtVenda->format('Y-m-d');
        } else {
            $dtVenda = DateTimeUtils::parseDateStr($filter['dtVenda']);
        }
        // $params['filter']['dtVenda'] = $dtVenda;
        return $this->doListSimpl($request, $params);
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


}