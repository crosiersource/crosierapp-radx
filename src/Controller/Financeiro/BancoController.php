<?php

namespace App\Controller\Financeiro;


use App\Entity\Financeiro\Banco;
use App\EntityHandler\Financeiro\BancoEntityHandler;
use App\Form\Financeiro\BancoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Banco.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class BancoController extends FormListController
{

    /**
     * @required
     * @param BancoEntityHandler $entityHandler
     */
    public function setEntityHandler(BancoEntityHandler $entityHandler): void
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
     * @Route("/fin/banco/form/{id}", name="banco_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Banco|null $banco
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, Banco $banco = null)
    {
        $params = [
            'typeClass' => BancoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'banco_form',
            'formPageTitle' => 'Banco'
        ];
        return $this->doForm($request, $banco, $params);
    }

    /**
     *
     * @Route("/fin/banco/list/", name="banco_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'banco_form',
            'listView' => 'Financeiro/bancoList.html.twig',
            'listRoute' => 'banco_list',
            'listRouteAjax' => 'banco_datatablesJsList',
            'listPageTitle' => 'Bancos',
            'listId' => 'bancoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/banco/datatablesJsList/", name="banco_datatablesJsList")
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
     * @Route("/fin/banco/delete/{id}/", name="banco_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Banco $banco
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Banco $banco): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $banco);
    }


}