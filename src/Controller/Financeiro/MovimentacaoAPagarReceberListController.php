<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoAPagarReceberListController extends BaseController
{

    private MovimentacaoBusiness $business;

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
     * @param MovimentacaoBusiness $business
     */
    public function setBusiness(MovimentacaoBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     *
     * @Route("/fin/aPagarReceber/list", name="aPagarReceber_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function aPagarReceberList(Request $request)
    {
        $params = $this->buildListData($request);
        return $this->doRender('Financeiro/movimentacaoAPagarReceberList.html.twig', $params);
    }

    /**
     * @param Request $request
     * @return array
     * @throws ViewException
     */
    private function buildListData(Request $request): array
    {
        $params = $request->query->all();
        if (!array_key_exists('filter', $params)) {

            if ($params['r'] ?? null) {
                $this->storedViewInfoBusiness->clear('aPagarReceber_list');
            }
            $svi = $this->storedViewInfoBusiness->retrieve('aPagarReceber_list');
            if (isset($svi['filter'])) {
                $params['filter'] = $svi['filter'];
            } else {
                $params['filter'] = [];
                $params['filter']['dts'] = date('d/m/Y') . ' - ' . date('d/m/Y');
                $params['filter']['carteira'] = 1; // padrão
            }

        }
        $params['filter']['status'] = 'ABERTA';

        $dtIni = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 0, 10)) ?: new \DateTime();
        $dtFim = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 13, 10)) ?: new \DateTime();

        $params['filter']['dt']['i'] = $dtIni->format('Y-m-d');
        $params['filter']['dt']['f'] = $dtFim->format('Y-m-d');

        $filterDatas = $this->getFilterDatas($params);

        /** @var MovimentacaoRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Movimentacao::class);
        $orders = [
            'e.dtUtil' => 'asc',
            'e.valorTotal' => 'asc'
        ];
        $dados = $repo->findByFilters($filterDatas, $orders, 0, null);


        $dtAnterior = clone $dtIni;
        $dtAnterior->setTime(12, 0, 0, 0)->modify('last day');

        $dia = null;
        $dias = array();
        $i = -1;
        /** @var Movimentacao $movimentacao */
        foreach ($dados as $movimentacao) {
            if ($movimentacao->dtVenctoEfetiva && $movimentacao->dtVenctoEfetiva->format('d/m/Y') !== $dia) {
                $i++;
                $dia = $movimentacao->dtVenctoEfetiva->format('d/m/Y');
                $dias[$i]['dtVenctoEfetiva'] = $movimentacao->dtVenctoEfetiva;
            }
            $dias[$i]['movs'][] = $movimentacao;
        }
        foreach ($dias as $k => $dia) {
            $dia['total'] = $this->business->somarMovimentacoes($dia['movs']);
            $dias[$k] = $dia;
        }

        $params['totalGeral'] = $this->business->somarMovimentacoes($dados);


        $params['dias'] = $dias;

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);

        $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true);
        $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false);
        $params['antePeriodoI'] = $ante['dtIni'];
        $params['antePeriodoF'] = $ante['dtFim'];
        $params['proxPeriodoI'] = $prox['dtIni'];
        $params['proxPeriodoF'] = $prox['dtFim'];

        $params['page_title'] = 'Contas a Pagar/Receber';

        $filterChoicesCarteiras = $this->getDoctrine()->getRepository(Carteira::class)->findBy(['atual' => true, 'operadoraCartao' => null], ['codigo' => 'ASC']);
        $filterChoicesCarteiras_selecteds = $params['filter']['carteira'] ?? null;
        $params['filterChoices']['carteiras'] = Select2JsUtils::toSelect2Data($filterChoicesCarteiras, '%s', ['descricaoMontada'], $filterChoicesCarteiras_selecteds);

        $viewInfo = [];
        $viewInfo['filter'] = $params['filter'];
        $this->storedViewInfoBusiness->store('aPagarReceber_list', $viewInfo);

        return $params;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return array(
            new FilterData(['dtVenctoEfetiva'], 'BETWEEN', 'dt', $params),
            new FilterData('carteira', 'IN', 'carteira', $params),
            new FilterData('status', 'IN', 'status', $params),
            new FilterData('modo', 'IN', 'modo', $params),
        );
    }

    /**
     *
     * @Route("/fin/aPagarReceber/fichaMovimentacao/", name="aPagarReceber_fichaMovimentacao")
     *
     * @param Request $request
     * @return void
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function fichaMovimentacaoPDF(Request $request): void
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $movsSel = $request->get('movsSelecionadas');

        $movs = [];
        foreach ($movsSel as $id => $on) {
            $movs[] = $this->getDoctrine()->getRepository(Movimentacao::class)->find($id);
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('Financeiro/fichaMovimentacao.html.twig', ['movs' => $movs]);
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);


        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream('fichaMovimentacao.pdf', [
            'Attachment' => false
        ]);

    }

    /**
     *
     * @Route("/fin/aPagarReceber/fichaMovimentacaoHTML/{movimentacao}", name="aPagarReceber_fichaMovimentacaoHTML", defaults={"movimentacao"=null}, requirements={"movimentacao"="\d+"})
     *
     * @param Movimentacao $movimentacao
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function fichaMovimentacaoHTML(Movimentacao $movimentacao): Response
    {
        return $this->render('Financeiro/fichaMovimentacao.html.twig', ['movs' => [$movimentacao]]);
    }

    /**
     *
     * @Route("/fin/aPagarReceber/rel/", name="aPagarReceber_rel")
     *
     * @param Request $request
     * @return void
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function relPDF(Request $request): void
    {
        $params = $this->buildListData($request);

        $params['hoje'] = (new \DateTime())->format('d/m/Y H:i');

        gc_collect_cycles();
        gc_disable();

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('enable_remote', true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('Financeiro/movimentacaoAPagarRel.html.twig', $params);
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);


        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream('aPagarReceber_rel.pdf', [
            'Attachment' => false
        ]);

        gc_collect_cycles();
        gc_enable();

    }

    /**
     *
     * @Route("/fin/aPagarReceber/rel2", name="aPagarReceber_rel2")
     *
     * @param Request $request
     * @return void
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function rel2PDF(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $tableData = json_decode($content['tableData'], true);
        $filters = json_decode($content['filters'], true);
        $somatorios = json_decode($content['somatorios'], true);
        $params['totalGeral'] = $content['totalGeral'];
        
        $params['hoje'] = (new \DateTime())->format('d/m/Y H:i:s');
        $params['dts'] = DateTimeUtils::parseDateStr($filters['dtVenctoEfetiva[after]'])->format('d/m/Y') . ' - ' .
            DateTimeUtils::parseDateStr($filters['dtVenctoEfetiva[before]'])->format('d/m/Y');
        $params['tableData'] = $tableData;

        $dia = null;
        $dias = [];
        $i = -1;
        
        foreach ($tableData as $r) {
            if ($r['dtVenctoEfetiva'] !== $dia) {
                $i++;
                $dia = $r['dtVenctoEfetiva'];
                $dias[$i]['total'] = $somatorios[$r['dtVenctoEfetiva']];
                $dias[$i]['dtVenctoEfetiva'] = $r['dtVenctoEfetiva'];
            }
            $dias[$i]['movs'][] = $r;
        }
                
        $params['dias'] = $dias;

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('Financeiro/movimentacaoAPagarRel2.html.twig', $params);


        $this->knpSnappyPdf->setOption('page-width', '12cm');
        $this->knpSnappyPdf->setOption('page-height', '29cm');

        return new Response(
            base64_encode($this->knpSnappyPdf->getOutputFromHtml($html))
        );

    }
    
    /**
     *
     * @Route("/fin/movimentacao/aPagarReceber/rel", name="fin_movimentacoa_aPagarReceber_rel")
     *
     * @param Request $request
     * @return void
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function rel(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $tableData = json_decode($content['tableData'], true);
        $filters = json_decode($content['filters'], true);
        $somatorios = json_decode($content['somatorios'], true);
        $params['totalGeral'] = $content['totalGeral'];
        
        $params['hoje'] = (new \DateTime())->format('d/m/Y H:i:s');

        $filters['dtVenctoEfetiva[after]'] = $filters['dtVenctoEfetiva[after]'] ?? '0001-01-01';
        $filters['dtVenctoEfetiva[before]'] = $filters['dtVenctoEfetiva[before]'] ?? '9999-01-01';
        
        $params['dts'] = DateTimeUtils::parseDateStr($filters['dtVenctoEfetiva[after]'])->format('d/m/Y') . ' - ' .
            DateTimeUtils::parseDateStr($filters['dtVenctoEfetiva[before]'])->format('d/m/Y');
        $params['tableData'] = $tableData;

        $dia = null;
        $dias = [];
        $i = -1;
        
        foreach ($tableData as $r) {
            if ($r['dtVenctoEfetiva'] !== $dia) {
                $i++;
                $dia = $r['dtVenctoEfetiva'];
                $dias[$i]['total'] = $somatorios[$r['dtVenctoEfetiva']];
                $dias[$i]['dtVenctoEfetiva'] = $r['dtVenctoEfetiva'];
            }
            $dias[$i]['movs'][] = $r;
        }
                
        $params['dias'] = $dias;

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('Financeiro/movimentacaoAPagarRel2.html.twig', $params);


        gc_collect_cycles();
        gc_disable();

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('enable_remote', true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);


        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        return new Response(
            base64_encode($dompdf->output())
        );
//
//        // Output the generated PDF to Browser (inline view)
//        $dompdf->stream('aPagarReceber_rel.pdf', [
//            'Attachment' => false
//        ]);

        gc_collect_cycles();
        gc_enable();
        

    }

    /**
     *
     * @Route("/fin/aPagarReceber/relHTML/", name="aPagarReceber_relHTML")
     *
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function relHTML(Request $request): Response
    {
        $params = $this->buildListData($request);
        $params['hoje'] = (new \DateTime())->format('d/m/Y H:i');
        return $this->render('Financeiro/movimentacaoAPagarRel.html.twig', $params);
    }


}