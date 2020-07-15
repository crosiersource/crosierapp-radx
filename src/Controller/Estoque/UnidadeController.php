<?php

namespace App\Controller\Estoque;


use App\Form\Estoque\UnidadeType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\UnidadeEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class UnidadeController extends FormListController
{

    /**
     * @required
     * @param UnidadeEntityHandler $entityHandler
     */
    public function setEntityHandler(UnidadeEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     *
     * @Route("/est/unidade/form/{id}", name="est_unidade_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Unidade|null $unidade
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request, Unidade $unidade = null)
    {
        $params = [
            'typeClass' => UnidadeType::class,
            'formView' => 'Estoque/unidade_form.html.twig',
            'formRoute' => 'est_unidade_form',
            'listRoute' => 'est_unidade_list',
            'formPageTitle' => 'Unidade'
        ];
        return $this->doForm($request, $unidade, $params);
    }

    /**
     *
     * @Route("/est/unidade/list/", name="est_unidade_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_unidade_form',
            'listView' => 'Estoque/unidade_list.html.twig',
            'listRoute' => 'est_unidade_list',
            'listPageTitle' => 'Unidades',
        ];
        return $this->doListSimpl($request, $params);
    }

    /**
     *
     * @Route("/est/unidade/delete/{id}/", name="est_unidade_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Unidade $unidade
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Unidade $unidade): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $unidade, []);
    }





}