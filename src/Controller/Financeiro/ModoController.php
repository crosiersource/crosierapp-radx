<?php

namespace App\Controller\Financeiro;


use App\Entity\Financeiro\Modo;
use App\EntityHandler\Financeiro\ModoEntityHandler;
use App\Form\Financeiro\ModoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Modo.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ModoController extends FormListController
{

    /**
     * @required
     * @param ModoEntityHandler $entityHandler
     */
    public function setEntityHandler(ModoEntityHandler $entityHandler): void
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
     * @Route("/modo/form/{id}", name="modo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Modo|null $modo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, Modo $modo = null)
    {
        $params = [
            'typeClass' => ModoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'modo_form',
            'formPageTitle' => 'Modo de Movimentação'
        ];
        return $this->doForm($request, $modo, $params);
    }

    /**
     *
     * @Route("/modo/list/", name="modo_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'modo_form',
            'listView' => 'modoList.html.twig',
            'listRoute' => 'modo_list',
            'listRouteAjax' => 'modo_datatablesJsList',
            'listPageTitle' => 'Modos de Movimentação',
            'listId' => 'modoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/modo/datatablesJsList/", name="modo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/modo/delete/{id}/", name="modo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Modo $modo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Modo $modo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $modo);
    }


}