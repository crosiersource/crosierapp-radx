<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MovimentacaoExtratoController.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoExtratoController extends FormListController
{

    private MovimentacaoBusiness $business;

    /**
     * @required
     * @param MovimentacaoBusiness $business
     */
    public function setBusiness(MovimentacaoBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     *
     * @Route("/fin/movimentacao/extrato/", name="movimentacao_extrato")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function extrato(Request $request)
    {
        $parameters = $request->query->all();

        if (!array_key_exists('filter', $parameters)) {

            if ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_extrato')) {
                $parameters['filter']['dts'] = $sviParams['dts'];
                $parameters['filter']['carteira'] = $sviParams['carteira'];
            } else {
                // inicializa para evitar o erro
                $parameters['filter'] = array();
                $parameters['filter']['dts'] = date('d/m/Y') . ' - ' . date('d/m/Y');
                $parameters['filter']['carteira'] = 1;
            }
        }

        $dtIni = DateTimeUtils::parseDateStr(substr($parameters['filter']['dts'], 0, 10));
        $dtFim = DateTimeUtils::parseDateStr(substr($parameters['filter']['dts'], 13, 10));

        $parameters['filter']['dt']['i'] = $dtIni->format('Y-m-d');
        $parameters['filter']['dt']['f'] = $dtFim->format('Y-m-d');

        $filterDatas = $this->getFilterDatas($parameters);

        /** @var Carteira $carteira */
        $carteira = $this->getDoctrine()->getRepository(Carteira::class)->find($parameters['filter']['carteira']);

        /** @var MovimentacaoRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Movimentacao::class);
        $orders = [
            'e.dtUtil' => 'asc',
            'categ.codigoSuper' => 'asc',
            'e.valorTotal' => 'asc'
        ];
        $dados = $repo->findByFilters($filterDatas, $orders, 0, null);


        $dtAnterior = (clone $dtIni)->setTime(12, 0, 0, 0)->modify('last day');
//        $parameters['anteriores']['movs'] = $repo->findAbertasAnteriores($dtAnterior, $carteira);
        $parameters['anteriores']['saldos'] = $this->business->calcularSaldos($dtAnterior, $carteira);

        $dia = null;
        $dias = array();
        $i = -1;
        /** @var Movimentacao $movimentacao */
        foreach ($dados as $movimentacao) {
            if (in_array($movimentacao->categoria->codigo, [191,291])) continue;
            if ($movimentacao->dtUtil && $movimentacao->dtUtil->format('d/m/Y') !== $dia) {
                $i++;
                $dia = $movimentacao->dtUtil->format('d/m/Y');
                $dias[$i]['dtUtil'] = $movimentacao->dtUtil;
                $dias[$i]['saldos'] = $this->business->calcularSaldos($movimentacao->dtUtil, $carteira);
            }
            $dias[$i]['movs'][] = $movimentacao;
        }


        $parameters['dias'] = $dias;

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);

        $diaUtilFinanceiro = $carteira->caixa ? false : true;
        $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true, true, $diaUtilFinanceiro);
        $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false, true, $diaUtilFinanceiro);
        $parameters['antePeriodoI'] = $ante['dtIni'];
        $parameters['antePeriodoF'] = $ante['dtFim'];
        $parameters['proxPeriodoI'] = $prox['dtIni'];
        $parameters['proxPeriodoF'] = $prox['dtFim'];
        $parameters['dtFim'] = $dtFim->format('d/m/Y');

        $parameters['carteira']['id'] = $carteira->getId();
        $parameters['carteira']['operadoraCartao'] = $carteira->operadoraCartao;
        $parameters['carteira']['cheque'] = $carteira->cheque;
        $parameters['carteira']['options'] = $this->business->getFilterCarteiraOptions($filterDatas);

        $parameters['page_title'] = 'Extrato de Movimentações';


        $sviParams = [
            'carteira' => $carteira->getId(),
            'dts' => $parameters['filter']['dts']
        ];
        $this->storedViewInfoBusiness->store('movimentacao_extrato', $sviParams);

        if ($carteira->operadoraCartao) {
            $parameters['totaisExtratoCartao'] = $repo->findTotaisExtratoCartoes($carteira, $dtIni, $dtFim);
        } else {
            $parameters['totaisExtrato'] = $repo->findTotaisExtrato($carteira, $dtIni, $dtFim);
        }

        return $this->doRender('Financeiro/movimentacaoExtratoList.html.twig', $parameters);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['dtUtil'], 'BETWEEN_DATE', 'dt', $params),
            new FilterData('carteira', 'IN', 'carteira', $params),
            new FilterData('status', 'IN', 'status', $params),
        ];
    }


}
