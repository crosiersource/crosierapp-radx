<?php

namespace App\Controller\Estoque;


use App\Form\Estoque\ProdutoType;
use App\Form\Estoque\RomaneioItemType;
use App\Form\Estoque\RomaneioType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Romaneio;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\RomaneioItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\RomaneioEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\RomaneioItemEntityHandler;
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
class RomaneioController extends FormListController
{

    private SessionInterface $session;

    private EntityIdUtils $entityIdUtils;

    private RomaneioItemEntityHandler $romaneioItemEntityHandler;

    /**
     * @required
     * @param RomaneioEntityHandler $entityHandler
     */
    public function setEntityHandler(RomaneioEntityHandler $entityHandler): void
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
     * @param RomaneioItemEntityHandler $romaneioItemEntityHandler
     */
    public function setRomaneioItemEntityHandler(RomaneioItemEntityHandler $romaneioItemEntityHandler): void
    {
        $this->romaneioItemEntityHandler = $romaneioItemEntityHandler;
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
            new FilterData(['fornecedor_nome'], 'EQ', 'fornecedor_nome', $params, null, true)
        ];
    }


    /**
     *
     * @Route("/est/romaneio/list/", name="est_romaneio_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_romaneio_form',
            'listView' => 'Estoque/romaneio_list.html.twig',
            'listRoute' => 'est_romaneio_list',
            'listRouteAjax' => 'est_romaneio_datatablesJsList',
            'listPageTitle' => 'Pedidos de Compra',
            'listId' => 'romaneio_list'
        ];
        return $this->doList($request, $params);
    }


    /**
     *
     * @Route("/est/romaneio/datatablesJsList/", name="est_romaneio_datatablesJsList")
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
     * @Route("/est/romaneio/form/{id}", name="est_romaneio_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Romaneio|null $romaneio
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_PEDIDOCOMPRA", statusCode=403)
     */
    public function form(Request $request, Romaneio $romaneio = null)
    {
        if (!$romaneio) {
            $romaneio = new Romaneio();
            $romaneio->dtEmissao = new \DateTime();
            if ($request->get('notaFiscal')) {
                /** @var NotaFiscal $notaFiscal */
                $notaFiscal = $this->getDoctrine()->getRepository(NotaFiscal::class)->find($request->get('notaFiscal'));
                /** @var RomaneioEntityHandler $romaneioEntityHandler */
                $romaneioEntityHandler = $this->entityHandler;
                $romaneio = $romaneioEntityHandler->buildFromNotaFiscal($notaFiscal);
                return $this->redirectToRoute('est_romaneio_form', ['id' => $romaneio->getId()]);
            }
        }

        $params = [
            'typeClass' => RomaneioType::class,
            'formView' => 'Estoque/romaneio_form.html.twig',
            'formRoute' => 'est_romaneio_form',
            'formPageTitle' => 'Romaneio'
        ];

        $params['unidades'] = $this->getDoctrine()->getRepository(Produto::class)->getUnidadesSelect2js();
        return $this->doForm($request, $romaneio, $params);
    }


    /**
     *
     * @Route("/est/romaneio/marcarProdutoForm/{romaneioItem}", name="est_romaneio_marcarProdutoForm", requirements={"romaneioItem"="\d+"})
     * @param Request $request
     * @param RomaneioItem|null $romaneioItem
     * @return RedirectResponse|Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     * @IsGranted("ROLE_ROMANEIO", statusCode=403)
     */
    public function marcarProdutoForm(Request $request, RomaneioItem $romaneioItem)
    {
        if ($request->get('btnSalvarQtdeConferida')) {
            $item = $request->get('item');
            $qtdeConferida = $item['qtdeConferida'] ?? null;
            $romaneioItem->qtdeConferida = DecimalUtils::parseStr($qtdeConferida);
            $romaneioItem = $this->romaneioItemEntityHandler->save($romaneioItem);
            return $this->redirectToRoute('est_romaneio_marcarProdutoForm', ['romaneioItem' => $romaneioItem->getId()]);
        }

        $anterior = null;
        if ($romaneioItem->ordem > 1) {
            $anterior = $this->entityHandler->getDoctrine()->getConnection()
                ->fetchAll('SELECT id FROM est_romaneio_item WHERE ordem = :ordem AND romaneio_id = :romaneioId',
                    ['ordem' => $romaneioItem->ordem - 1, 'romaneioId' => $romaneioItem->romaneio->getId()]);
        }
        $proximo = null;
        if ($romaneioItem->ordem < $romaneioItem->romaneio->itens->count()) {
            $proximo = $this->entityHandler->getDoctrine()->getConnection()
                ->fetchAll('SELECT id FROM est_romaneio_item WHERE ordem = :ordem AND romaneio_id = :romaneioId',
                    ['ordem' => $romaneioItem->ordem + 1, 'romaneioId' => $romaneioItem->romaneio->getId()]);
        }

        $params = [
            'typeClass' => RomaneioItemType::class,
            'formView' => 'Estoque/romaneio_marcarProdutoForm.html.twig',
            'formRoute' => 'est_romaneio_marcarProdutoForm',
            'formPageTitle' => 'Item do Romaneio',
            // 'routeParams' => ['romaneio' => $romaneio->getId()],
            'entityHandler' => $this->romaneioItemEntityHandler,
            // 'jsonMetadata' => $jsonMetadata,
            'anterior' => $anterior[0]['id'] ?? null,
            'proximo' => $proximo[0]['id'] ?? null,

        ];

        $formProduto = $this->createForm(ProdutoType::class);
        $params['formProduto'] = $formProduto->createView();

        return $this->doForm($request, $romaneioItem, $params);
    }


    /**
     * @Route("/est/romaneioItem/delete/{romaneioItem}", name="est_romaneioItem_delete", defaults={"romaneioItem"=null}, requirements={"romaneioItem"="\d+"})
     * @param RomaneioItem $romaneioItem
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     * @IsGranted("ROLE_ROMANEIO", statusCode=403)
     */
    public function removerItem(RomaneioItem $romaneioItem): Response
    {
        $this->romaneioItemEntityHandler->delete($romaneioItem);
        return $this->redirectToRoute('est_romaneio_form', ['id' => $romaneioItem->romaneio->getId(), '_fragment' => 'itens']);
    }

}
