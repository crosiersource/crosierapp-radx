<?php

namespace App\Controller\Financeiro;

use App\Form\Financeiro\MovimentacaoAPagarType;
use App\Form\Financeiro\MovimentacaoAReceberType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CadeiaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MovimentacaoAPagarReceberController.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoAPagarReceberController extends FormListController
{

    /** @var MovimentacaoBusiness */
    private $business;

    /** @var SessionInterface */
    private $session;

    /** @var CadeiaEntityHandler */
    private $cadeiaEntityHandler;

    /**
     * @required
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
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
     * @required
     * @param MovimentacaoEntityHandler $entityHandler
     */
    public function setEntityHandler(MovimentacaoEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param CadeiaEntityHandler $cadeiaEntityHandler
     */
    public function setCadeiaEntityHandler(CadeiaEntityHandler $cadeiaEntityHandler): void
    {
        $this->cadeiaEntityHandler = $cadeiaEntityHandler;
    }


    /**
     * Tela para lançamento completo de contas a pagar.
     *
     * @Route("/fin/movimentacao/form/aPagarReceber/{id}", name="movimentacao_form_aPagarReceber", defaults={"id"=null}, requirements={"movimentacao"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function aPagarReceberForm(Request $request, Movimentacao $movimentacao = null)
    {
        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->getParcelamento();
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->setCarteira($this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['codigo' => 99]));

            $tipoLanctoCodigo = $parcelamento ? 21 : 20;
            $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => $tipoLanctoCodigo]));
            $movimentacao->setStatus('ABERTA');
        }

        $params = [
            'typeClass' => MovimentacaoAPagarType::class,
            'formRoute' => 'movimentacao_form_aPagarReceber',
            'formPageTitle' => 'Movimentação a Pagar/Receber',
        ];

        $params['formView'] = 'Financeiro/movimentacaoForm_aPagarReceber.html.twig';

        if (!$movimentacao->getId() && $parcelamento) {
            $params['formView'] = 'Financeiro/movimentacaoForm_aPagarReceber_parcelamento.html.twig';
            return $this->handleParcelamento($request, $movimentacao, $params);
        }
        // else

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * @param Request $request
     * @param Movimentacao $movimentacao
     * @param array $params
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function handleParcelamento(Request $request, Movimentacao $movimentacao, array $params = []): Response
    {
        $form = $this->createForm($params['typeClass'], $movimentacao);

        $form->handleRequest($request);

        $params['qtdeParcelas'] = $request->get('qtdeParcelas');
        $params['dtPrimeiroVencto'] = $request->get('dtPrimeiroVencto');
        $params['valor'] = $request->get('valor');
        $params['tipoValor'] = $request->get('tipoValor');


        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $qtdeParcelas = $request->get('qtdeParcelas');
                $dtPrimeiroVencto = DateTimeUtils::parseDateStr($request->get('dtPrimeiroVencto'));
                $valor = DecimalUtils::parseStr($request->get('valor'));
                $tipoValor = $request->get('tipoValor');
                $parcelas = $request->get('parcelas');

                if ($qtdeParcelas === null || !$dtPrimeiroVencto || !$valor === null || !$tipoValor) {
                    $this->addFlash('error', 'Informe os dados do parcelamento');
                } else {

                    $movimentacao = $form->getData();
                    $this->business->gerarParcelas($movimentacao, $qtdeParcelas, $valor, $dtPrimeiroVencto, $tipoValor === 'TOTAL', $parcelas);
                    if ($request->get('btnSalvar')) {
                        try {
                            $this->entityHandler->saveAll($movimentacao->getCadeia()->getMovimentacoes(), true);
                            $this->addFlash('success', 'Registro salvo com sucesso!');
                            $this->afterSave($movimentacao);
                            return $this->redirectTo($request, $movimentacao, $params['formRoute']);
                        } catch (\Exception $e) {
                            $msg = ExceptionUtils::treatException($e);
                            $this->addFlash('error', $msg);
                            $this->addFlash('error', 'Erro ao salvar!');
                        }
                    }
                }
            } else {
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }


        $this->handleReferer($request, $params);

        // Pode ou não ter vindo algo no $params. Independentemente disto, só adiciono form e foi-se.
        $params['form'] = $form->createView();
        $params['e'] = $movimentacao;
        return $this->doRender($params['formView'], $params);
    }

    /**
     * Tela para lançamento simplificado de contas a pagar.
     *
     * @Route("/fin/aPagar/formSimpl/{id}", name="movimentacao_form_aPagarReceberSimpl", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function aPagarFormSimpl(Request $request, Movimentacao $movimentacao = null)
    {
        if (null !== $r = $this->checkRedir($request, $movimentacao)) {
            return $r;
        }

        $vParams['exibirRecorrente'] = $this->business->exibirRecorrente($movimentacao);

        $params['typeClass'] = MovimentacaoAPagarType::class;
        $params['formView'] = 'Financeiro/movimentacaoForm_aPagarReceber_simpl.html.twig';
        $params['formRoute'] = 'movimentacao_form_aPagarReceberSimpl';
        $params['formPageTitle'] = 'Conta a Pagar';
        return $this->doForm($request, $movimentacao, $vParams);
    }


    /**
     * Tela para lançamento completo de contas a receber.
     *
     * @Route("/fin/aReceber/form/{id}", name="aReceber_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function aReceberForm(Request $request, Movimentacao $movimentacao = null)
    {
        if (null !== $r = $this->checkRedir($request, $movimentacao, false)) {
            return $r;
        }

        $vParams['exibirRecorrente'] = $this->business->exibirRecorrente($movimentacao);

        $params['typeClass'] = MovimentacaoAReceberType::class;
        $params['formView'] = 'Financeiro/movimentacaoAPagarReceberForm.html.twig';
        $params['formRoute'] = 'aReceber_form';
        $params['formPageTitle'] = 'Conta a Receber';
        return $this->doForm($request, $movimentacao, $vParams);
    }

    /**
     * Tela para lançamento simplificado de contas a pagar.
     *
     * @Route("/fin/aReceber/formSimpl/{id}", name="aReceber_formSimpl", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function aReceberFormSimpl(Request $request, Movimentacao $movimentacao = null)
    {
        if (null !== $r = $this->checkRedir($request, $movimentacao, false)) {
            return $r;
        }

        $vParams['exibirRecorrente'] = $this->business->exibirRecorrente($movimentacao);

        $params['typeClass'] = MovimentacaoAReceberType::class;
        $params['formView'] = 'Financeiro/movimentacaoAPagarReceberForm_simpl.html.twig';
        $params['formRoute'] = 'aReceber_formSimpl';
        $params['formPageTitle'] = 'Conta a Receber';
        return $this->doForm($request, $movimentacao, $vParams);
    }

    /**
     *
     * @Route("/fin/aPagarReceber/list", name="aPagarReceber_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
            if ($movimentacao->getDtVenctoEfetiva() && $movimentacao->getDtVenctoEfetiva()->format('d/m/Y') !== $dia) {
                $i++;
                $dia = $movimentacao->getDtVenctoEfetiva()->format('d/m/Y');
                $dias[$i]['dtVenctoEfetiva'] = $movimentacao->getDtVenctoEfetiva();
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
     * @Route("/fin/aPagarReceber/relHTML/", name="aPagarReceber_relHTML")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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