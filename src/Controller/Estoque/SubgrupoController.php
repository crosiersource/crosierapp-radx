<?php

namespace App\Controller\Estoque;

use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Subgrupo;
use App\EntityHandler\Estoque\SubgrupoEntityHandler;
use App\Form\Estoque\SubgrupoType;
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
class SubgrupoController extends FormListController
{

    /**
     * @required
     * @param SubgrupoEntityHandler $entityHandler
     */
    public function setEntityHandler(SubgrupoEntityHandler $entityHandler): void
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
     * @Route("/est/subgrupo/form/{id}", name="est/subgrupo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Subgrupo|null $subgrupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request, Subgrupo $subgrupo = null)
    {
        $params = [
            'typeClass' => SubgrupoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'subgrupo_form',
            'formPageTitle' => 'Subgrupo'
        ];
        return $this->doForm($request, $subgrupo, $params);
    }

    /**
     *
     * @Route("/subgrupo/list/", name="subgrupo_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'subgrupo_form',
            'listView' => 'subgrupoList.html.twig',
            'listRoute' => 'subgrupo_list',
            'listRouteAjax' => 'subgrupo_datatablesJsList',
            'listPageTitle' => 'Subgrupos',
            'listId' => 'subgrupoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/subgrupo/datatablesJsList/", name="subgrupo_datatablesJsList")
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
     * @Route("/subgrupo/delete/{id}/", name="subgrupo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Subgrupo $subgrupo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Subgrupo $subgrupo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $subgrupo);
    }

    /**
     *
     * @Route("/est/subgrupo/select2json/{grupo}", name="est_subgrupo_select2json", requirements={"id"="\d+"})
     * @param Grupo $grupo
     * @return JsonResponse
     *
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function subgrupoSelect2json(Grupo $grupo): JsonResponse
    {
        $subgrupos = $grupo->getSubgrupos()->toArray();

        $select2js = Select2JsUtils::toSelect2DataFn($subgrupos, function ($e) {
            /** @var Subgrupo $e */
            return $e->getDescricaoMontada();
        });
        return new JsonResponse(
            ['results' => $select2js]
        );
    }


}