<?php

namespace App\Controller\Financeiro;


use App\Entity\Financeiro\OperadoraCartao;
use App\EntityHandler\Financeiro\OperadoraCartaoEntityHandler;
use App\Form\Financeiro\OperadoraCartaoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperadoraCartaoController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class OperadoraCartaoController extends FormListController
{

    /**
     * @required
     * @param OperadoraCartaoEntityHandler $entityHandler
     */
    public function setEntityHandler(OperadoraCartaoEntityHandler $entityHandler): void
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
     * @Route("/operadoraCartao/form/{id}", name="operadoraCartao_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param OperadoraCartao|null $operadoraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, OperadoraCartao $operadoraCartao = null)
    {
        $params = [
            'typeClass' => OperadoraCartaoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'operadoraCartao_form',
            'formPageTitle' => 'Função'
        ];
        return $this->doForm($request, $operadoraCartao, $params);
    }

    /**
     *
     * @Route("/operadoraCartao/list/", name="operadoraCartao_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'operadoraCartao_form',
            'listView' => 'operadoraCartaoList.html.twig',
            'listRoute' => 'operadoraCartao_list',
            'listRouteAjax' => 'operadoraCartao_datatablesJsList',
            'listPageTitle' => 'Funções/Cargos',
            'listId' => 'operadoraCartaoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/operadoraCartao/datatablesJsList/", name="operadoraCartao_datatablesJsList")
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
     * @Route("/operadoraCartao/delete/{id}/", name="operadoraCartao_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param OperadoraCartao $operadoraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, OperadoraCartao $operadoraCartao): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $operadoraCartao);
    }


}