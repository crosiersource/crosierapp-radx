<?php

namespace App\Controller\Estoque;

use App\Entity\Estoque\Atributo;
use App\Entity\Estoque\GrupoAtributo;
use App\EntityHandler\Estoque\GrupoAtributoEntityHandler;
use App\Form\Estoque\GrupoAtributoType;
use App\Repository\Estoque\AtributoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoAtributoController extends FormListController
{

    /**
     * @required
     * @param GrupoAtributoEntityHandler $entityHandler
     */
    public function setEntityHandler(GrupoAtributoEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['label'], 'LIKE', 'label', $params)
        ];
    }

    /**
     *
     * @Route("/est/grupoAtributo/form/{id}", name="est_grupoAtributo_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param GrupoAtributo|null $grupoAtributo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request, GrupoAtributo $grupoAtributo = null)
    {
        $params = [
            'listRoute' => 'est_grupoAtributo_list',
            'typeClass' => GrupoAtributoType::class,
            'formView' => 'Estoque/grupoAtributo_form.html.twig',
            'formRoute' => 'est_grupoAtributo_form',
            'formPageTitle' => 'Grupo de Atributos'
        ];

        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->getDoctrine()->getRepository(Atributo::class);

        if ($grupoAtributo && $grupoAtributo->getId()) {
            $atributoId = $request->get('atributo');
            if ($atributoId) {
                $atributo = $repoAtributo->find($atributoId);
                if (!$grupoAtributo->getAtributos()->contains($atributo)) {
                    $grupoAtributo->getAtributos()->add($atributo);
                }
            }
        }

        $atributosOptions = $repoAtributo->findBy(['primaria' => 'S'], ['label' => 'ASC']);
        $atributosOptionsSelect2 = Select2JsUtils::toSelect2DataFn($atributosOptions, function ($e) {
            /** @var Atributo $e */
            return $e->getDescricao();
        });
        array_unshift($atributosOptionsSelect2, ['id' => -1, 'text' => 'Selecione...']);
        $params['atributosOptions'] = json_encode($atributosOptionsSelect2);

        return $this->doForm($request, $grupoAtributo, $params);
    }

    /**
     *
     * @Route("/est/grupoAtributo/list/", name="est_grupoAtributo_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_grupoAtributo_form',
            'listView' => '@CrosierLibBase/list.html.twig',
            'listJS' => 'Estoque/grupoAtributo_list.js',
            'listRoute' => 'est_grupoAtributo_list',
            'listRouteAjax' => 'est_grupoAtributo_datatablesJsList',
            'listPageTitle' => 'Grupos de Atributos',
            'deleteRoute' => 'est_grupoAtributo_delete',
            'listId' => 'grupoAtributo_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/est/grupoAtributo/datatablesJsList/", name="est_grupoAtributo_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/est/grupoAtributo/delete/{id}/", name="est_grupoAtributo_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param GrupoAtributo $grupoAtributo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, GrupoAtributo $grupoAtributo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $grupoAtributo);
    }


    /**
     *
     * @Route("/est/grupoAtributo/removerAtributo/{grupoAtributo}/{atributo}/", name="est_grupoAtributo_removerAtributo", requirements={"atributo"="\d+"})
     * @param GrupoAtributo $grupoAtributo
     * @param Atributo $atributo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function removerAtributo(GrupoAtributo $grupoAtributo, Atributo $atributo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        try {
            $grupoAtributo->getAtributos()->removeElement($atributo);
            $this->entityHandler->save($grupoAtributo);
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('est_grupoAtributo_form', ['id' => $grupoAtributo->getId()]);
    }


}