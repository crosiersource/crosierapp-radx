<?php

namespace App\Controller\Vendas;

use App\Form\Vendas\VendaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package Cliente\Controller\Crediario
 * @author Carlos Eduardo Pauluk
 */
class VendaController extends FormListController
{

    private Pdf $knpSnappyPdf;


    /**
     * @required
     * @param Pdf $knpSnappyPdf
     */
    public function setKnpSnappyPdf(Pdf $knpSnappyPdf): void
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    /**
     * @required
     * @param VendaEntityHandler $entityHandler
     */
    public function setEntityHandler(VendaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['nome', 'cpf'], 'LIKE', 'str', $params),
        ];
    }

    /**
     *
     * @Route("/ven/venda/form/{id}", name="ven_venda_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Venda|null $venda
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function form(Request $request, Venda $venda = null)
    {
        $params = [
            'listRoute' => 'ven_venda_listPorDia',
            'typeClass' => VendaType::class,
            'formView' => 'Vendas/venda_form.html.twig',
            'formRoute' => 'ven_venda_form',
            'formPageTitle' => 'Venda'
        ];

        if (!$venda) {
            $venda = new Venda();
            $venda->dtVenda = new \DateTime();
            $venda->status = 'PV';
            $venda->subtotal = 0.0;
            $venda->desconto = 0.0;
        }

        return $this->doForm($request, $venda, $params);
    }

    /**
     *
     * @Route("/ven/venda/listPorDia/{dia}", name="ven_venda_listPorDia")
     * @param Request $request
     * @param \DateTime $dia
     * @return Response
     *
     * @throws ViewException
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function vendasPorDia(Request $request, \DateTime $dia = null): Response
    {
        if (!$dia) {
            $dia = new \DateTime();
        }
        $params = [];

        $params['dia'] = $dia->format('d/m/Y');

        $params = [
            'formRoute' => 'ven_venda_form',
            'listView' => 'Vendas/vendasPorDia_list.html.twig',
            'listRoute' => 'ven_venda_listPorDia',
            'listPageTitle' => 'Vendas',
            'listPageSubtitle' => $params['dia'],
            'listId' => 'ven_venda_listPorDia'
        ];
        return $this->doListSimpl($request, $params);
    }


}