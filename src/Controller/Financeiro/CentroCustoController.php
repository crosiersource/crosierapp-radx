<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\CentroCustoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CentroCustoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para CentroCusto.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CentroCustoController extends FormListController
{

    /**
     * @required
     * @param CentroCustoEntityHandler $entityHandler
     */
    public function setEntityHandler(CentroCustoEntityHandler $entityHandler): void
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
     * @Route("/fin/centroCusto/form/{id}", name="centroCusto_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param CentroCusto|null $centroCusto
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, CentroCusto $centroCusto = null)
    {
        $params = [
            'typeClass' => CentroCustoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'centroCusto_form',
            'formPageTitle' => 'Centro de Custo'
        ];
        return $this->doForm($request, $centroCusto, $params);
    }

    /**
     *
     * @Route("/fin/centroCusto/list/", name="centroCusto_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'centroCusto_form',
            'listView' => 'Financeiro/centroCustoList.html.twig',
            'listRoute' => 'centroCusto_list',
            'listRouteAjax' => 'centroCusto_datatablesJsList',
            'listPageTitle' => 'Centros de Custo',
            'listId' => 'centroCustoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/centroCusto/datatablesJsList/", name="centroCusto_datatablesJsList")
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
     * @Route("/fin/centroCusto/delete/{id}/", name="centroCusto_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param CentroCusto $centroCusto
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, CentroCusto $centroCusto): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $centroCusto);
    }


}