<?php

namespace App\Controller\Estoque;

use App\Entity\Estoque\Atributo;
use App\EntityHandler\Estoque\AtributoEntityHandler;
use App\Form\Estoque\AtributoType;
use App\Repository\Estoque\AtributoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class AtributoController extends FormListController
{

    /**
     * @required
     * @param AtributoEntityHandler $entityHandler
     */
    public function setEntityHandler(AtributoEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['label'], 'LIKE', 'str', $params),
            new FilterData(['primaria'], 'EQ', 'primaria', $params)
        ];
    }

    /**
     *
     * @Route("/est/atributo/form/{id}", name="est_atributo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Atributo|null $atributo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request, Atributo $atributo = null)
    {
        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->getDoctrine()->getRepository(Atributo::class);

        /** @var Atributo $atributoPai */
        $atributoPai = null;
        $atributoPaiUUID = $request->get('atributoPaiUUID');
        if ($atributoPaiUUID) {
            $atributoPai = $repoAtributo->findOneBy(['UUID' => $atributoPaiUUID]);
        }

        $params = [
            'listRoute' => 'est_atributo_list',
            'typeClass' => AtributoType::class,
            'formView' => 'Estoque/atributo_form.html.twig',
            'formRoute' => 'est_atributo_form',
            'routeParams' => ['atributoPai' => ($atributoPai ? $atributoPai->getId() : '')]
        ];
        $params['formPageTitle'] = $atributoPai ? 'Subatributo de Produto' : 'Atributo de Produto';
        $params['formPageSubTitle'] = $atributoPai ? '(Atributo Pai: ' . $atributoPai->getDescricao() . ')' : '';
        $params['atributoPai'] = $atributoPai;
        if (!$atributo) {
            $atributo = new Atributo();
        }
        if ($atributoPai) {
            $atributo->setPaiUUID($atributoPai->getUUID());
        }
        $repoAtributo->fillSubatributos($atributo);
        return $this->doForm($request, $atributo, $params);
    }

    /**
     *
     * @Route("/est/atributo/list/", name="est_atributo_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_atributo_form',
            'listView' => '@CrosierLibBase/list.html.twig',
            'listJS' => 'Estoque/atributo_list.js',
            'listRoute' => 'est_atributo_list',
            'listRouteAjax' => 'est_atributo_datatablesJsList',
            'listPageTitle' => 'Atributos de Produtos',
            'page_subTitle' => '(Primários)',
            'deleteRoute' => 'est_atributo_delete',
            'listId' => 'atributo_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/est/atributo/datatablesJsList/", name="est_atributo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        $defaultFilters['filter']['primaria'] = 'S';
        return $this->doDatatablesJsList($request, $defaultFilters);
    }

    /**
     *
     * @Route("/est/atributo/delete/{id}/", name="est_atributo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Atributo $atributo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Atributo $atributo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $atributo, ['listRoute' => 'est_atributo_list']);
    }


}