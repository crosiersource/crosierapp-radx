<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\OperadoraCartaoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\OperadoraCartaoEntityHandler;
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
     * @Route("/fin/operadoraCartao/form/{id}", name="operadoraCartao_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param OperadoraCartao|null $operadoraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
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
            'formPageTitle' => 'Operadora de Cartão'
        ];
        return $this->doForm($request, $operadoraCartao, $params);
    }

    /**
     *
     * @Route("/fin/operadoraCartao/list/", name="operadoraCartao_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'operadoraCartao_form',
            'listView' => 'Financeiro/operadoraCartaoList.html.twig',
            'listRoute' => 'operadoraCartao_list',
            'listRouteAjax' => 'operadoraCartao_datatablesJsList',
            'listPageTitle' => 'Operadoras de Cartão',
            'listId' => 'operadoraCartaoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/operadoraCartao/datatablesJsList/", name="operadoraCartao_datatablesJsList")
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
     * @Route("/fin/operadoraCartao/delete/{id}/", name="operadoraCartao_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param OperadoraCartao $operadoraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, OperadoraCartao $operadoraCartao): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $operadoraCartao, []);
    }


}