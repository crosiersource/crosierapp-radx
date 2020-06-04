<?php

namespace App\Controller\Estoque;


use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\PedidoCompra;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\PedidoCompraItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\PedidoCompraEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\PedidoCompraItemEntityHandler;
use App\Form\Estoque\PedidoCompraItemType;
use App\Form\Estoque\PedidoCompraType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class PedidoCompraController extends FormListController
{

    private SessionInterface $session;

    private EntityIdUtils $entityIdUtils;

    private PedidoCompraItemEntityHandler $pedidoCompraItemEntityHandler;

    /**
     * @required
     * @param PedidoCompraEntityHandler $entityHandler
     */
    public function setEntityHandler(PedidoCompraEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @required
     * @param PedidoCompraItemEntityHandler $pedidoCompraItemEntityHandler
     */
    public function setPedidoCompraItemEntityHandler(PedidoCompraItemEntityHandler $pedidoCompraItemEntityHandler): void
    {
        $this->pedidoCompraItemEntityHandler = $pedidoCompraItemEntityHandler;
    }

    /**
     * @required
     * @param EntityIdUtils $entityIdUtils
     */
    public function setEntityIdUtils(EntityIdUtils $entityIdUtils): void
    {
        $this->entityIdUtils = $entityIdUtils;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['dtEmissao'], 'BETWEEN', 'dtEmissao', $params),
            new FilterData(['fornecedor_nome'], 'EQ', 'fornecedor_nome', $params)
        ];
    }

    /**
     *
     * @Route("/est/pedidoCompra/list/", name="est_pedidoCompra_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_pedidoCompra_form',
            'listView' => 'Estoque/pedidoCompra_list.html.twig',
            'listRoute' => 'est_pedidoCompra_list',
            'listRouteAjax' => 'est_pedidoCompra_datatablesJsList',
            'listPageTitle' => 'Pedidos de Compra',
            'listId' => 'pedidoCompra_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/est/pedidoCompra/datatablesJsList/", name="est_pedidoCompra_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/est/pedidoCompra/form/{id}", name="est_pedidoCompra_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param PedidoCompra|null $pedidoCompra
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function form(Request $request, PedidoCompra $pedidoCompra = null)
    {
        if (!$pedidoCompra) {
            $pedidoCompra = new PedidoCompra();
            $pedidoCompra->dtEmissao = new \DateTime();
            $pedidoCompra->responsavel = $this->getUser()->getNome();
        }
        $params = [
            'typeClass' => PedidoCompraType::class,
            'formView' => 'Estoque/pedidoCompra_form.html.twig',
            'formRoute' => 'est_pedidoCompra_form',
            'formPageTitle' => 'Pedido de Compra'
        ];
        return $this->doForm($request, $pedidoCompra, $params);
    }


    /**
     * @Route("/est/pedidoCompra/adicionar/{produto}/{filial}/{qtdeSugerida}", name="est_pedidoCompra_adicionar")
     * @param Request $request
     * @param Produto $produto
     * @param string $filial
     * @param float $qtdeSugerida
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_RELVENDAS", statusCode=403)
     */
    public function adicionar(Request $request, Produto $produto, string $filial, float $qtdeSugerida): RedirectResponse
    {
        try {
            if ($this->session->has('pedidoCompra')) {
                $pedidoCompra = $this->session->get('pedidoCompra');
                if ($pedidoCompra->status !== 'INICIADO' || ($pedidoCompra->fornecedor->getId() !== $produto->fornecedor->getId())) {
                    $pedidoCompra = $this->getNovoPedidoCompra($produto->fornecedor, $filial);
                    $pedidoCompra = $this->getEntityHandler()->save($pedidoCompra);
                } else {
                    $pedidoCompra = $this->getEntityHandler()->getDoctrine()->getRepository(PedidoCompra::class)->find($pedidoCompra->getId());
                }
            } else {
                $pedidoCompra = $this->getNovoPedidoCompra($produto->fornecedor, $filial);
                $pedidoCompra = $this->getEntityHandler()->save($pedidoCompra);
            }

            $temOProduto = false;
            /** @var PedidoCompra $pedidoCompra */
            /** @var PedidoCompraItem $item */
            foreach ($pedidoCompra->itens as $item) {
                if (($item->jsonData['produto_id'] ?? null) === $produto->getId()) {
                    $temOProduto = true;
                    break;
                }
            }
            if (!$temOProduto) {

                $item = new PedidoCompraItem();
                $item->pedidoCompra = $pedidoCompra;
                $item->jsonData['produto_id'] = $produto->getId();
                $item->descricao = $produto->nome;
                $item->qtde = abs($qtdeSugerida);
                $item->precoCusto = $produto->jsonData['preco_custo'] ?? 0.0;
                $item->total = bcmul($item->qtde, $item->precoCusto, 2);
                $pedidoCompra->itens->add($item);

                $this->pedidoCompraItemEntityHandler->save($item);

                $this->addFlash('success', $produto->getId() . ' - ' . $produto->nome . ' adicionado com sucesso');
            } else {
                $this->addFlash('warn', $produto->getId() . ' - ' . $produto->nome . ' já adicionado ao Pedido de Compra');

            }
            $pedidoCompra = $this->getEntityHandler()->save($pedidoCompra);
            $this->session->set('pedidoCompra', $pedidoCompra);


        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao adicionar produto no pedidoCompra');
        }
        // return to referer
        if ($request->get('rtr')) {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
        return $this->redirectToRoute('est_pedidoCompra_form', ['id' => $pedidoCompra->getId()]);
    }

    /**
     * @param Fornecedor $fornecedor
     * @param string $filial
     * @return PedidoCompra
     * @throws \Exception
     */
    private function getNovoPedidoCompra(Fornecedor $fornecedor, string $filial): PedidoCompra
    {
        $pedidoCompra = new PedidoCompra();
        $pedidoCompra->fornecedor = $fornecedor;
        $pedidoCompra->jsonData['filial'] = $filial;
        $pedidoCompra->responsavel = $this->getUser()->getNome();
        $pedidoCompra->dtEmissao = new \DateTime();
        $pedidoCompra->status = 'INICIADO';
        return $pedidoCompra;
    }


    /**
     *
     * @Route("/est/pedidoCompraItem/form/{pedidoCompra}/{pedidoCompraItem}", name="est_pedidoCompraItem_form", defaults={"pedidoCompraItem"=null}, requirements={"pedidoCompra"="\d+","pedidoCompraItem"="\d+"})
     * @param Request $request
     * @param PedidoCompra|null $pedidoCompra
     * @param PedidoCompraItem|null $pedidoCompraItem
     * @return RedirectResponse|Response
     * @throws \Exception
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function formItem(Request $request, PedidoCompra $pedidoCompra, PedidoCompraItem $pedidoCompraItem = null)
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('est_pedidocompra_json_metadata'), true);

        if (!$pedidoCompraItem) {
            $pedidoCompraItem = new PedidoCompraItem();
            $pedidoCompraItem->pedidoCompra = $pedidoCompra;
        }
        $params = [
            'typeClass' => PedidoCompraItemType::class,
            'formView' => 'Estoque/pedidoCompraItem_form.html.twig',
            'formRoute' => 'est_pedidoCompraItem_form',
            'formPageTitle' => 'Item do PedidoCompra',
            'routeParams' => ['pedidoCompra' => $pedidoCompra->getId()],
            'entityHandler' => $this->pedidoCompraItemEntityHandler,
            'jsonMetadata' => $jsonMetadata
        ];
        return $this->doForm($request, $pedidoCompraItem, $params);
    }

    public function handleRequestOnValid(Request $request, $entity): void
    {
        if ($entity instanceof PedidoCompraItem) {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
            $jsonMetadata = json_decode($repoAppConfig->findByChave('est_pedidocompra_json_metadata'), true);
            if ($jsonMetadata['vinculoAoEstoque'] === 'porProduto') {
                $entity->jsonData['produto'] = $request->get('produto');
            }
        }
    }


    /**
     * @Route("/est/pedidoCompraItem/delete/{pedidoCompraItem}", name="est_pedidoCompraItem_delete", defaults={"pedidoCompraItem"=null}, requirements={"pedidoCompraItem"="\d+"})
     * @param PedidoCompraItem $pedidoCompraItem
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function removerItem(PedidoCompraItem $pedidoCompraItem): Response
    {
        $this->pedidoCompraItemEntityHandler->delete($pedidoCompraItem);
        return $this->redirectToRoute('est_pedidoCompra_form', ['id' => $pedidoCompraItem->pedidoCompra->getId(), '_fragment' => 'itens']);
    }

}
