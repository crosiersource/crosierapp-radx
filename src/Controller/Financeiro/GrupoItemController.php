<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoItemType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\GrupoBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\GrupoItemRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * CRUD Controller para GrupoItem.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoItemController extends FormListController
{

    private GrupoBusiness $grupoBusiness;

    private MovimentacaoBusiness $movimentacaoBusiness;

    /**
     * @required
     * @param MovimentacaoBusiness $movimentacaoBusiness
     */
    public function setMovimentacaoBusiness(MovimentacaoBusiness $movimentacaoBusiness): void
    {
        $this->movimentacaoBusiness = $movimentacaoBusiness;
    }

    /**
     * @required
     * @param GrupoBusiness $grupoBusiness
     */
    public function setGrupoBusiness(GrupoBusiness $grupoBusiness): void
    {
        $this->grupoBusiness = $grupoBusiness;
    }

    /**
     * @required
     * @param GrupoItemEntityHandler $entityHandler
     */
    public function setEntityHandler(GrupoItemEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['descricao'], 'LIKE', 'descricao', $params)
        ];
    }

    /**
     *
     * @Route("/fin/grupoItem/form/{id}", name="grupoItem_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param GrupoItem|null $grupoItem
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function form(Request $request, GrupoItem $grupoItem = null)
    {
        $params = [
            'typeClass' => GrupoItemType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'grupoItem_form',
            'formPageTitle' => 'Item de Grupo de Movimentações'
        ];
        return $this->doForm($request, $grupoItem, $params);
    }


    /**
     *
     * @Route("/fin/grupoItem/datatablesJsList/", name="grupoItem_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/grupoItem/delete/{id}/", name="grupoItem_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param GrupoItem $grupoItem
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function delete(Request $request, GrupoItem $grupoItem): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $grupoItem, []);
    }


    /**
     *
     * @Route("/fin/grupoItem/gerarNovo/{pai}", name="grupoItem_gerarNovo", requirements={"pai"="\d+"})
     * @param Request $request
     * @param Grupo $pai
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function gerarNovo(Request $request, Grupo $pai)
    {
        try {
            $prox = $request->get('prox');
            $this->grupoBusiness->gerarNovo($pai, $prox);
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->addFlash('error', $msg);
        }
        return $this->redirectToRoute('grupoItem_list', ['pai' => $pai->getId()]);
    }

    /**
     *
     * @Route("/fin/grupoItem/list/{pai}", name="grupoItem_list", requirements={"pai"="\d+"})
     * @param Grupo $pai
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function list(Grupo $pai): Response
    {
        /** @var GrupoItemRepository $repo */
        $repo = $this->getDoctrine()->getRepository(GrupoItem::class);

        $dados = $repo->findBy(['pai' => $pai->getId()], ['dtVencto' => 'DESC']);

        $vParams = [];
        $vParams['dados'] = $dados;
        $vParams['pai'] = $pai;
        $vParams['page_title'] = $pai->getDescricao();
        $vParams['formRoute'] = 'grupoItem_form';

        return $this->doRender('Financeiro/grupoItemList.html.twig', $vParams);

    }

    /**
     *
     * @Route("/fin/grupoItem/select2json", name="grupoItem_select2json")
     * @param Request $request
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function grupoItemSelect2json(Request $request): Response
    {
        $paiId = $request->get('pai');
        if (!$paiId) {
            return null;
        }

        $where = ['pai' => $paiId];
        if ($request->get('fechados')) {
            $where['fechado'] = true;
        }
        $itens = $this->getDoctrine()->getRepository(GrupoItem::class)->findBy($where, ['dtVencto' => 'DESC']);

        $rs = array();
        /** @var GrupoItem $item */
        foreach ($itens as $item) {
            $r['id'] = $item->getId();
            $r['text'] = $item->getDescricao();
            $rs[] = $r;
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($rs, 'json');

        return new Response($json);

    }


    /**
     *
     * @Route("/fin/grupoItem/listMovs/", name="grupoItem_listMovs")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function listMovs(Request $request): Response
    {
        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        $repoGrupoItem = $this->getDoctrine()->getRepository(GrupoItem::class);

        /** @var GrupoItem $grupoItem */
        $grupoItem = $request->get('grupoItem');
        if ($grupoItem) {
            $grupoItem = $repoGrupoItem->find($grupoItem);
        }

        $grupo = $request->get('grupo');
        if ($grupo) {
            $grupo = $repoGrupo->find($grupo);
        }

        if (!$grupo && $grupoItem) {
            $grupo = $grupoItem->getPai();
        }

        /** @var MovimentacaoRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Movimentacao::class);

        // Se não passar um grupo item, pega o da última movimentação editada
        if (!$grupoItem) {
            if (!$grupo) {
                /** @var Movimentacao $ultimaMov */
                $ultimaMov = $repo->findByFiltersSimpl([['grupoItem', 'IS_NOT_NULL']], ['updated' => 'DESC'], 0, 1);
                if (!$ultimaMov) {
                    $this->addFlash('error', 'Nenhum item de grupo de movimentações encontrado');
                    return $this->redirectToRoute('grupo_list');
                }
                /** @var Movimentacao $ultMov */
                $ultMov = $ultimaMov[0];
                $grupoItem = $ultMov->getGrupoItem();
            } else {
                $grupoItem = $grupo->getItens()->last();
            }
            if (!$grupoItem) {
                $grupoItem = $this->grupoBusiness->gerarNovo($grupo);
            }
            return $this->redirectToRoute('grupoItem_listMovs', ['grupoItem' => $grupoItem->getId()]);

        }

        $dados = $repo->findBy(['grupoItem' => $grupoItem->getId()], ['dtMoviment' => 'ASC']);

        $total = $this->movimentacaoBusiness->somarMovimentacoes($dados);

        $rGrupos = $repoGrupo->findAll(['descricao' => 'ASC']);
        $grupos = Select2JsUtils::toSelect2DataFn($rGrupos, function ($e) {
            /** @var Grupo $e */
            return $e->getDescricao();
        });

        $grupoItens = $grupoItem->getPai()->getItens()->toArray();
        uasort($grupoItens, function ($a, $b) {
            /** @var GrupoItem $a */
            /** @var GrupoItem $b */
            return $a->getDtVencto() < $b->getDtVencto();
        });
        $grupoItensOptions = Select2JsUtils::toSelect2DataFn($grupoItens, function ($e) {
            /** @var GrupoItem $e */
            return $e->getDescricao();
        });


        $vParams = [];

        $vParams['gruposOptions'] = json_encode($grupos);
        $vParams['grupoItensOptions'] = json_encode($grupoItensOptions);
        $vParams['dados'] = $dados;
        $vParams['total'] = $total;
        $vParams['grupoItem'] = $grupoItem;
        $vParams['page_title'] = $grupoItem->getDescricao();

        return $this->doRender('Financeiro/grupoItemListMovs.html.twig', $vParams);
    }


}