<?php

namespace App\Controller\CRM;


use App\Form\CRM\ClienteType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Cliente.
 *
 * @package App\Controller\CRM
 * @author Carlos Eduardo Pauluk
 */
class ClienteController extends FormListController
{

    /**
     * @required
     * @param ClienteEntityHandler $entityHandler
     */
    public function setEntityHandler(ClienteEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['documento'], 'LIKE', 'documento', $params),
            new FilterData(['nome'], 'LIKE', 'nome', $params)
        ];
    }

    /**
     *
     * @Route("/crm/cliente/form/{id}", name="cliente_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Cliente|null $cliente
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function form(Request $request, Cliente $cliente = null)
    {
        $params = [
            'typeClass' => ClienteType::class,
            'formRoute' => 'cliente_form',
            'formPageTitle' => 'Cliente'
        ];
        return $this->doForm($request, $cliente, $params);
    }

    /**
     *
     * @Route("/crm/cliente/list/", name="cliente_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'cliente_form',
            'listView' => 'CRM/cliente_list.html.twig',
            'listRoute' => 'cliente_list',
            'listRouteAjax' => 'cliente_datatablesJsList',
            'listPageTitle' => 'Clientes',
            'listId' => 'cliente_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/crm/cliente/datatablesJsList/", name="cliente_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/crm/cliente/delete/{id}/", name="cliente_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Cliente $cliente
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Cliente $cliente): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $cliente, []);
    }


}