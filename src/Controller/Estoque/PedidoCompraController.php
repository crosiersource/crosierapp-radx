<?php

namespace App\Controller\Estoque;


use App\Entity\Estoque\PedidoCompra;
use App\Entity\Estoque\PedidoCompraItem;
use App\EntityHandler\Estoque\PedidoCompraEntityHandler;
use App\EntityHandler\Estoque\PedidoCompraItemEntityHandler;
use App\Form\Estoque\PedidoCompraItemType;
use App\Form\Estoque\PedidoCompraType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
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
            'entityHandler' => $this->pedidoCompraItemEntityHandler
        ];
        return $this->doForm($request, $pedidoCompraItem, $params);
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
