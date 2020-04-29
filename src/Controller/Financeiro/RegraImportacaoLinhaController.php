<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\RegraImportacaoLinhaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegraImportacaoLinha;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\RegraImportacaoLinhaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegraImportacaoLinhaController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegraImportacaoLinhaController extends FormListController
{

    /**
     * @required
     * @param RegraImportacaoLinhaEntityHandler $entityHandler
     */
    public function setEntityHandler(RegraImportacaoLinhaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['regraRegexJava'], 'LIKE', 'str', $params)
        ];
    }

    /**
     *
     * @Route("/fin/regraImportacaoLinha/form/{id}", name="regraImportacaoLinha_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param RegraImportacaoLinha|null $regraImportacaoLinha
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, RegraImportacaoLinha $regraImportacaoLinha = null)
    {
        $params = [
            'typeClass' => RegraImportacaoLinhaType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'regraImportacaoLinha_form',
            'formPageTitle' => 'Regra de Importação'
        ];
        return $this->doForm($request, $regraImportacaoLinha, $params);
    }

    /**
     *
     * @Route("/fin/regraImportacaoLinha/list/", name="regraImportacaoLinha_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'regraImportacaoLinha_form',
            'listView' => 'Financeiro/regraImportacaoLinhaList.html.twig',
            'listRoute' => 'regraImportacaoLinha_list',
            'listRouteAjax' => 'regraImportacaoLinha_datatablesJsList',
            'listPageTitle' => 'Regras de Importação',
            'listId' => 'regraImportacaoLinhaList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/regraImportacaoLinha/datatablesJsList/", name="regraImportacaoLinha_datatablesJsList")
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
     * @Route("/fin/regraImportacaoLinha/delete/{id}/", name="regraImportacaoLinha_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param RegraImportacaoLinha $regraImportacaoLinha
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, RegraImportacaoLinha $regraImportacaoLinha): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $regraImportacaoLinha, []);
    }


}