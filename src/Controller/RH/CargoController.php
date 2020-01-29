<?php

namespace App\Controller;

use App\Entity\Cargo;
use App\EntityHandler\CargoEntityHandler;
use App\Form\CargoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller CRUD para a entidade Cargo.
 * @package App\Controller\Cargo
 *
 * @author Carlos Eduardo Pauluk
 */
class CargoController extends FormListController
{

    protected $crudParams =
        [
            'typeClass' => CargoType::class,

            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'cargo_form',
            'formPageTitle' => 'Função',
            'form_PROGRAM_UUID' => '0b122db0-bb1a-4140-be1b-f0b91a82ac1d',

            'listView' => 'cargoList.html.twig',
            'listRoute' => 'cargo_list',
            'listRouteAjax' => 'cargo_datatablesJsList',
            'listPageTitle' => 'Funções/Cargos',
            'listId' => 'cargoList',
            'list_PROGRAM_UUID' => 'df585cca-1c0a-4be5-9e7a-96a002dff03e',

            'normalizedAttrib' => [
                'id',
                'descricao',
            ],

        ];

    /**
     * @required
     * @param CargoEntityHandler $entityHandler
     */
    public function setEntityHandler(CargoEntityHandler $entityHandler): void
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
     * @Route("/cargo/form/{id}", name="cargo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Cargo|null $cargo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function form(Request $request, Cargo $cargo = null)
    {
        return $this->doForm($request, $cargo);
    }

    /**
     *
     * @Route("/cargo/list/", name="cargo_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function list(Request $request): Response
    {
        return $this->doList($request);
    }

    /**
     *
     * @Route("/cargo/datatablesJsList/", name="cargo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/cargo/delete/{id}/", name="cargo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Cargo $cargo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Cargo $cargo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $cargo);
    }


}