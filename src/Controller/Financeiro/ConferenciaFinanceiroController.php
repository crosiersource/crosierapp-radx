<?php

namespace App\Controller\Financeiro;

use App\Business\Financeiro\ConferenciaFinanceiroBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConferenciaFinanceiroController extends AbstractController
{

    /** @var ConferenciaFinanceiroBusiness */
    private $business;

    /**
     * ConferenciaFinanceiroController constructor.
     * @param ConferenciaFinanceiroBusiness $business
     */
    public function __construct(ConferenciaFinanceiroBusiness $business)
    {
        $this->business = $business;
    }

    /**
     *
     * @Route("/fin/conferencia/list/", name="conferencia_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function list(Request $request)
    {
        $vParams = $request->query->all();
        if ($request->get('btnMesAtual') or !array_key_exists('filter', $vParams)) {
            // inicializa para evitar o erro
            $hj = new \DateTime();
            $vParams['filter'] = [];
            $vParams['filter']['dtUtil']['i'] = $hj->format('Y-m-') . '01';
            $vParams['filter']['dtUtil']['f'] = $hj->format('Y-m-t');
        }

        $dtIni = \DateTime::createFromFormat('Y-m-d', $vParams['filter']['dtUtil']['i']);
        $dtFim = \DateTime::createFromFormat('Y-m-d', $vParams['filter']['dtUtil']['f']);

        $vParams['lists'] = $this->business->buildLists($dtIni, $dtFim);

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);

        $prox = $repoDiaUtil->incPeriodo(true, $dtIni, $dtFim);
        $ante = $repoDiaUtil->incPeriodo(false, $dtIni, $dtFim);
        $vParams['antePeriodoI'] = $ante['dtIni'];
        $vParams['antePeriodoF'] = $ante['dtFim'];
        $vParams['proxPeriodoI'] = $prox['dtIni'];
        $vParams['proxPeriodoF'] = $prox['dtFim'];

        return $this->doRender('Financeiro/conferenciaFinanceiro.html.twig', $vParams);
    }

}