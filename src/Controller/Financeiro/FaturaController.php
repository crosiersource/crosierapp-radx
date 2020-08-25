<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Fatura;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\FaturaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class FaturaController extends FormListController
{

    /**
     * @required
     * @param FaturaEntityHandler $entityHandler
     */
    public function setEntityHandler(FaturaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     *
     * @Route("/fin/fatura/visualizarFaturaVenda/{fatura}", name="fin_fatura_visualizarFaturaVenda", requirements={"fatura"="\d+"})
     * @param Request $request
     * @param Fatura $fatura
     * @return RedirectResponse|Response
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function form(Request $request, Fatura $fatura = null)
    {
        $params = [
            'e' => $fatura
        ];
        return $this->doRender('Financeiro/fatura_venda.html.twig', $params);
    }

    /**
     *
     * @Route("/fin/fatura/list/", name="fatura_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'fatura_form',
            'listView' => 'Financeiro/faturaList.html.twig',
            'listRoute' => 'fatura_list',
            'listRouteAjax' => 'fatura_datatablesJsList',
            'listPageTitle' => 'Faturas',
            'listId' => 'faturaList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/fatura/datatablesJsList/", name="fatura_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/fatura/delete/{id}/", name="fatura_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Fatura $fatura
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Fatura $fatura): RedirectResponse
    {
        return $this->doDelete($request, $fatura, null);
    }


}