<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\GrupoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Grupo.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoController extends FormListController
{

    /**
     * @required
     * @param GrupoEntityHandler $entityHandler
     */
    public function setEntityHandler(GrupoEntityHandler $entityHandler): void
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
     * @Route("/fin/grupo/form/{id}", name="grupo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Grupo|null $grupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, Grupo $grupo = null)
    {
        $params = [
            'typeClass' => GrupoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'grupo_form',
            'formPageTitle' => 'Grupo de Movimentações'
        ];
        return $this->doForm($request, $grupo, $params);
    }

    /**
     *
     * @Route("/fin/grupo/list/", name="grupo_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'grupo_form',
            'listView' => 'Financeiro/grupoList.html.twig',
            'listRoute' => 'grupo_list',
            'listRouteAjax' => 'grupo_datatablesJsList',
            'listPageTitle' => 'Grupos de Movimentações',
            'listId' => 'grupoList'
        ];

        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/grupo/datatablesJsList/", name="grupo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/grupo/delete/{id}/", name="grupo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Grupo $grupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Grupo $grupo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $grupo, []);
    }


    /**
     *
     * @Route("/fin/grupo/select2json", name="grupo_select2json")
     * @param Request $request
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     * @throws ViewException
     */
    public function grupoSelect2json(Request $request): Response
    {
        /** @var GrupoRepository $repoGrupo */
        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        $grupos = $repoGrupo->findAll(WhereBuilder::buildOrderBy('descricao'));

        $abertos = $request->get('abertos');

        $rs = array();
        /** @var Grupo $grupo */
        foreach ($grupos as $grupo) {
            if ($abertos) {
                // Só retorna grupos que possuam itens abertos
                $itens = $this->getDoctrine()->getRepository(GrupoItem::class)->findBy(['pai' => $grupo, 'fechado' => false], ['dtVencto' => 'DESC']);
                if ($itens and count($itens) > 0) {
                    $r['id'] = $grupo->getId();
                    $r['text'] = $grupo->descricao;
                    $r['itens'] = [];
                    /** @var GrupoItem $item */
                    foreach ($itens as $item) {
                        $rItem['id'] = $item->getId();
                        $rItem['text'] = $item->descricao;
                        $r['itens'][] = $rItem;
                    }
                    $rs[] = $r;
                }
            } else {
                $r['id'] = $grupo->getId();
                $r['text'] = $grupo->descricao;
                $rs[] = $r;
            }
        }

        return new JsonResponse($rs);

    }

}