<?php

namespace App\Controller\Vendas;

use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VendasResultsController
 * @package App\Controller\Vendas
 */
class VendasResultsController extends BaseController
{


    /** @var VendaRepository */
    private $vendaRepository;

    /**
     * @required
     * @param VendaRepository $vendaRepository
     */
    public function setVendaRepository(VendaRepository $vendaRepository): void
    {
        $this->vendaRepository = $vendaRepository;
    }

    /**
     *
     * @Route("/ven/vendasResults/vendasPorPeriodo", name="ven_vendasResults_vendasPorPeriodo")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function vendasPorPeriodo(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $parameters = $request->query->all();
        if (!is_array($parameters)) {
            $parameters = array();
        }
        if ($request->get('btnHoje') or !array_key_exists('dtVenda', $parameters)) {
            // inicializa para evitar o erro
            $dt = new \DateTime();
            $parameters['dtVenda']['i'] = $dt->format('Y-m') . '-01';
            $parameters['dtVenda']['f'] = $dt->format('Y-m-d');
        }
        if (!array_key_exists('codVendedor', $parameters)) {
            $parameters['codVendedor']['i'] = 0;
            $parameters['codVendedor']['f'] = 99;
        }

        $dtIni = \DateTime::createFromFormat('Y-m-d', $parameters['dtVenda']['i']);
        $dtFim = \DateTime::createFromFormat('Y-m-d', $parameters['dtVenda']['f']);

        $codVendedorIni = $parameters['codVendedor']['i'];
        $codVendedorFim = $parameters['codVendedor']['f'];

        $dados = $this->vendaRepository->findTotalVendasPorPeriodoVendedores($dtIni, $dtFim, $codVendedorIni, $codVendedorFim);

        $parameters['dados'] = $dados;

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);

        $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true, true, true);
        $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false, true, true);
        $parameters['antePeriodoI'] = $ante['dtIni'];
        $parameters['antePeriodoF'] = $ante['dtFim'];
        $parameters['proxPeriodoI'] = $prox['dtIni'];
        $parameters['proxPeriodoF'] = $prox['dtFim'];

        $parameters['page_title'] = 'Vendas por Período';

        return $this->doRender('Vendas/vendasResults/vendasPorPeriodo.html.twig', $parameters);
    }

}