<?php

namespace App\Controller\Financeiro;


use App\Entity\Financeiro\BandeiraCartao;
use App\EntityHandler\Financeiro\BandeiraCartaoEntityHandler;
use App\Form\Financeiro\BandeiraCartaoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para BandeiraCartao.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class BandeiraCartaoController extends FormListController
{

    /**
     * @required
     * @param BandeiraCartaoEntityHandler $entityHandler
     */
    public function setEntityHandler(BandeiraCartaoEntityHandler $entityHandler): void
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
     * @Route("/fin/bandeiraCartao/form/{id}", name="bandeiraCartao_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param BandeiraCartao|null $bandeiraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, BandeiraCartao $bandeiraCartao = null)
    {
        $params = [
            'typeClass' => BandeiraCartaoType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'bandeiraCartao_form',
            'formPageTitle' => 'Bandeira de Cartão'
        ];
        return $this->doForm($request, $bandeiraCartao, $params);
    }

    /**
     *
     * @Route("/fin/bandeiraCartao/list/", name="bandeiraCartao_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'bandeiraCartao_form',
            'listView' => 'Financeiro/bandeiraCartaoList.html.twig',
            'listRoute' => 'bandeiraCartao_list',
            'listRouteAjax' => 'bandeiraCartao_datatablesJsList',
            'listPageTitle' => 'Bandeira de Cartão',
            'listId' => 'bandeiraCartaoList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/bandeiraCartao/datatablesJsList/", name="bandeiraCartao_datatablesJsList")
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
     * @Route("/fin/bandeiraCartao/delete/{id}/", name="bandeiraCartao_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param BandeiraCartao $bandeiraCartao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, BandeiraCartao $bandeiraCartao): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $bandeiraCartao);
    }


}