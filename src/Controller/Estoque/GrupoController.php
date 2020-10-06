<?php

namespace App\Controller\Estoque;

use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\GrupoEntityHandler;
use App\Form\Estoque\GrupoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package App\Controller\Estoque
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
            new FilterData(['nome'], 'LIKE', 'str', $params)
        ];
    }

    /**
     *
     * @Route("/est/grupo/form/{id}", name="est_grupo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Grupo|null $grupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request, Grupo $grupo = null)
    {
        $params = [
            'typeClass' => GrupoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'grupo_form',
            'formPageTitle' => 'Grupo'
        ];
        return $this->doForm($request, $grupo, $params);
    }

    /**
     *
     * @Route("/grupo/list/", name="grupo_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'grupo_form',
            'listView' => 'grupoList.html.twig',
            'listRoute' => 'grupo_list',
            'listRouteAjax' => 'grupo_datatablesJsList',
            'listPageTitle' => 'Grupos',
            'listId' => 'grupoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/grupo/datatablesJsList/", name="grupo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/grupo/delete/{id}/", name="grupo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Grupo $grupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Grupo $grupo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $grupo);
    }

    /**
     *
     * @Route("/est/grupo/select2json/{depto}", name="est_grupo_select2json", requirements={"id"="\d+"})
     * @param Depto $depto
     * @return JsonResponse
     *
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function grupoSelect2json(Depto $depto): JsonResponse
    {
        $grupos = $depto->grupos->toArray();

        $select2js = Select2JsUtils::toSelect2DataFn($grupos, function ($e) {
            /** @var Grupo $e */
            return $e->getDescricaoMontada();
        });
        return new JsonResponse(
            ['results' => $select2js]
        );
    }


}