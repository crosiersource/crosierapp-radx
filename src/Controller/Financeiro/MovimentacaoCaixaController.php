<?php

namespace App\Controller\Financeiro;

use App\Business\Financeiro\MovimentacaoBusiness;
use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use App\Form\Financeiro\MovimentacaoCaixaType;
use App\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MovimentacaoCaixaController.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoCaixaController extends FormListController
{

    /** @var MovimentacaoEntityHandler */
    protected $entityHandler;

    /** @var MovimentacaoBusiness */
    private $business;

    /** @var EntityIdUtils */
    private $entityIdUtils;

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
     * @param EntityIdUtils $entityIdUtils
     */
    public function setEntityIdUtils(EntityIdUtils $entityIdUtils): void
    {
        $this->entityIdUtils = $entityIdUtils;
    }

    /**
     *
     * @Route("/movimentacao/caixa", name="movimentacao_caixa")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function movimentacoesCaixa(Request $request)
    {
        $parameters = $request->query->all();
        if (!array_key_exists('filter', $parameters)) {

            if ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_caixa')) {
                $parameters['filter']['dtMoviment'] = $sviParams['dtMoviment'];
                $parameters['filter']['carteira'] = $sviParams['carteira'];
            } else {
                $parameters['filter'] = [];
            }
        }

        if (!isset($parameters['filter']['dtMoviment']) || !$parameters['filter']['dtMoviment']) {
            $parameters['filter']['dtMoviment'] = date('Y-m-d');
        }

        if (!isset($parameters['filter']['carteira']) || !$parameters['filter']['carteira']) {
            $parameters['filter']['carteira'] = $this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['caixa' => true])->getId();
        }

        $dtMoviment = DateTimeUtils::parseDateStr($parameters['filter']['dtMoviment']);

        // Já calculo pois vou utilizar no saldo anterior

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);

        // Como é um caixa, o diaUtil deve ser 'comercial' e não necessariamente 'financeiro'
        $dtAnterior = $repoDiaUtil->findDiaUtil($dtMoviment, false, null, true);
        $dtProximo = $repoDiaUtil->findDiaUtil($dtMoviment, true, null, true);

        if (isset($parameters['btnAnterior'])) {
            $dtMoviment = $dtAnterior;
        } else if (isset($parameters['btnPosterior'])) {
            $dtMoviment->setTime(12, 0, 0, 0)->modify('next day');
        }

        $dtMoviment->setTime(0, 0, 0, 0);

        $parameters['filter']['dtMoviment'] = $dtMoviment;


        $carteira = $this->getDoctrine()->getRepository(Carteira::class)->find($parameters['filter']['carteira']);

        $listMovs = $this->getDoctrine()->getRepository(Movimentacao::class)->findBy(['dtMoviment' => $dtMoviment, 'carteira' => $carteira], ['valor' => 'ASC']);

        $listCartoesDebito = [];
        $listEntradasEmDinheiro = [];
        $listDespesas = [];
        $listRetiradas = [];
        $listOutrasEntradasEmDinheiro = [];
        $listOutras = [];

        // array para marcar movimentações que são de mesma cadeia de outras que estarão em um dos grupos,
        // para que não sejam indevidamente adicionadas no $listOutras[]
        $movsDeMesmaCadeia = [];
        /** @var Movimentacao $mov */
        foreach ($listMovs as $mov) {
            if ($mov->getModo()->getCodigo() === 10 && $mov->getCategoria()->getCodigo() === 101) {
                // CARTÕES DE DÉBITO
                $listCartoesDebito[] = $mov;
                $movsDeMesmaCadeia = array_merge($movsDeMesmaCadeia, $mov->getOutrasMovimentacoesDaCadeia());

            } else if ($mov->getModo()->getCodigo() === 1 && $mov->getCategoria()->getCodigo() === 101 && !$mov->getCadeia()) {
                // ENTRADAS EM DINHEIRO
                $listEntradasEmDinheiro[] = $mov;

            } else if ($mov->getCategoria()->getCodigoSuper() === 2 && $mov->getCategoria()->getCodigo() !== 299 && $mov->getCategoria()->getCodigo() !== 251) {
                // DESPESAS
                // tudo o que for categoria 2XXXX mas não 2.99
                $listDespesas[] = $mov;

            } else if ($mov->getCategoria()->getCodigo() === 299 && $mov->getModo()->getCodigo() === 01 && $mov->getCadeia() && $mov->getCadeia()->getMovimentacoes()->count() === 2) {
                // RETIRADAS
                // tudo o que for categoria 2.99
                $listRetiradas[] = $mov;
                $movsDeMesmaCadeia = array_merge($movsDeMesmaCadeia, $mov->getOutrasMovimentacoesDaCadeia());
            } else if ($mov->getCategoria()->getCodigo() === 101 && $mov->getModo()->getCodigo() === 01 && $mov->getCadeia() && $mov->getCadeia()->getMovimentacoes()->count() === 3) {
                // RETIRADAS
                // tudo o que for categoria 2.99
                $listOutrasEntradasEmDinheiro[] = $mov;
                $movsDeMesmaCadeia = array_merge($movsDeMesmaCadeia, $mov->getOutrasMovimentacoesDaCadeia());
            }
        }

        foreach ($listMovs as $mov) {
            if (!in_array($mov, array_merge($movsDeMesmaCadeia, $listEntradasEmDinheiro, $listCartoesDebito, $listDespesas, $listRetiradas, $listOutrasEntradasEmDinheiro), true)) {
                $listOutras[] = $mov;
                $movsDeMesmaCadeia = array_merge($movsDeMesmaCadeia, $mov->getOutrasMovimentacoesDaCadeia());
            }
        }


        $parameters['lists']['listCartoesDebito']['titulo'] = 'Cartões de Débito';
        $parameters['lists']['listCartoesDebito']['ents'] = $listCartoesDebito;
        $totalCartoesDebito = $this->business->somarMovimentacoes($listCartoesDebito);
        $parameters['lists']['listCartoesDebito']['total'] = $totalCartoesDebito;

        $parameters['lists']['listEntradasEmDinheiro']['titulo'] = 'Entradas em Dinheiro';
        $parameters['lists']['listEntradasEmDinheiro']['ents'] = $listEntradasEmDinheiro;
        $totalEntradasEmDinheiro = $this->business->somarMovimentacoes($listEntradasEmDinheiro);
        $parameters['lists']['listEntradasEmDinheiro']['total'] = $totalEntradasEmDinheiro;

        $parameters['lists']['listDespesas']['titulo'] = 'Despesas';
        $parameters['lists']['listDespesas']['ents'] = $listDespesas;
        $totalDespesas = $this->business->somarMovimentacoes($listDespesas);
        $parameters['lists']['listDespesas']['total'] = $totalDespesas;

        $parameters['lists']['listRetiradas']['titulo'] = 'Retiradas';
        $parameters['lists']['listRetiradas']['ents'] = $listRetiradas;
        $totalRetiradas = $this->business->somarMovimentacoes($listRetiradas);
        $parameters['lists']['listRetiradas']['total'] = $totalRetiradas;

        $parameters['lists']['listOutrasEntradasEmDinheiro']['titulo'] = 'Outras Entradas';
        $parameters['lists']['listOutrasEntradasEmDinheiro']['ents'] = $listOutrasEntradasEmDinheiro;
        $totalOutrasEntradasEmDinheiro = $this->business->somarMovimentacoes($listOutrasEntradasEmDinheiro);
        $parameters['lists']['listOutrasEntradasEmDinheiro']['total'] = $totalOutrasEntradasEmDinheiro;

        $parameters['lists']['listOutras']['titulo'] = 'Outras Movimentações';
        $parameters['lists']['listOutras']['ents'] = $listOutras;
        $totalOutras = $this->business->somarMovimentacoes($listOutras);
        $parameters['lists']['listOutras']['total'] = $totalOutras;


        $parameters['totalDia'] = abs($totalCartoesDebito) + abs($totalEntradasEmDinheiro);


        /** @var MovimentacaoRepository $movRepo */
        $movRepo = $this->getDoctrine()->getRepository(Movimentacao::class);
        $parameters['saldoAnterior'] = $movRepo->findSaldo($dtAnterior, $carteira, 'SALDO_POSTERIOR_REALIZADAS');
        $parameters['saldoAnteriorSemDebitos'] = $movRepo->findSaldo($dtAnterior, $carteira, 'SALDO_POSTERIOR_REALIZADAS_SEM_DEBITOS');
        $parameters['saldoPosterior'] = $movRepo->findSaldo($dtMoviment, $carteira, 'SALDO_POSTERIOR_REALIZADAS');
        $parameters['saldoPosteriorSemDebitos'] = $movRepo->findSaldo($dtMoviment, $carteira, 'SALDO_POSTERIOR_REALIZADAS_SEM_DEBITOS');
        $parameters['page_title'] = 'Movimentações de Caixa';

        $parameters['dtAnterior'] = $dtAnterior->format('d/m/Y');
        $parameters['dtProximo'] = $dtProximo->format('d/m/Y');


        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
        $rCarteiras = $repoCarteira->findBy(['caixa' => true, 'atual' => true], ['codigo' => 'ASC']);
        $carteiras = Select2JsUtils::toSelect2DataFn($rCarteiras, function ($e) {
            /** @var Carteira $e */
            return $e->getDescricaoMontada();
        });

        $parameters['carteirasOptions'] = json_encode($carteiras);

        $sviParams = [
            'carteira' => $carteira->getId(),
            'dtMoviment' => $dtMoviment->format('Y-m-d')
        ];
        $this->storedViewInfoBusiness->store('movimentacao_caixa', $sviParams);

        return $this->doRender('movimentacaoCaixaList.html.twig', $parameters);
    }


    /**
     *
     * @Route("/movimentacao/caixa/consolidarDebitos", name="movimentacao_caixa_consolidarDebitos")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function consolidarDebitos(Request $request)
    {
        /** @var Carteira $carteira */
        $carteira = $this->getDoctrine()->getRepository(Carteira::class)->find($request->get('carteira'));

        if ($request->get('mesano')) {
            $dts = DateTimeUtils::getDiasMesAno($request->get('mesano'));
        } else {
            $dts = [DateTimeUtils::parseDateStr($request->get('dtMoviment'))];
        }
        try {
            foreach ($dts as $dtMoviment) {
                $results = $this->business->consolidarMovimentacoesCartoesDebito($dtMoviment, $carteira);
                foreach ($results as $result) {
                    $this->addFlash('info', $result);
                }
            }
            $this->addFlash('success', 'Movimentações consolidadas.');
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
            $this->addFlash('error', 'Erro ao consolidar movimentações de débito.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao consolidar movimentações de débito.');
        }

        return $this->redirectToRoute('movimentacao_caixa', ['filter' => ['carteira' => $carteira->getId(), 'dtMoviment' => $dtMoviment->format('d/m/Y')]]);

    }


    /**
     *
     * @Route("/movimentacao/form/caixa/{id}", name="movimentacao_form_caixa", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function form(Request $request, Movimentacao $movimentacao = null)
    {
        $movimentacaoSalva = null;
        if (!$movimentacao && ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_form_caixa'))) {
            if (isset($sviParams['ultimaMovimentacaoSalva'])) {
                try {
                    /** @var Movimentacao $movimentacaoSalva */
                    $movimentacaoSalva = $this->entityIdUtils->unserialize($sviParams['ultimaMovimentacaoSalva'], Movimentacao::class);
                    $this->business->refindAll($movimentacaoSalva);
                } catch (ViewException $e) {
                    $this->addFlash('error', $e->getMessage());
                    $this->logger->error($e->getMessage());
                }
            }
        }

        if (!$movimentacao && !$request->request->get('movimentacao')) {
            // Form para novo registro
            $movimentacao = new Movimentacao();

            if ($movimentacaoSalva) {
                $movimentacao->setTipoLancto($movimentacaoSalva->getTipoLancto());
                $movimentacao->setCategoria($movimentacaoSalva->getCategoria());
                $movimentacao->setModo($movimentacaoSalva->getModo());
                $movimentacao->setModo($movimentacaoSalva->getModo());
                $movimentacao->setCarteira($movimentacaoSalva->getCarteira());
                $movimentacao->setCarteiraDestino($movimentacaoSalva->getCarteiraDestino());
                $movimentacao->setOperadoraCartao($movimentacaoSalva->getOperadoraCartao());
                $movimentacao->setBandeiraCartao($movimentacaoSalva->getBandeiraCartao());
                $movimentacao->setDtMoviment($movimentacaoSalva->getDtMoviment());
                $movimentacao->setDescricao($movimentacaoSalva->getDescricao());
                $movimentacao->setValor($movimentacaoSalva->getValor());
                $movimentacao->setObs($movimentacaoSalva->getObs());
            }


            // Pode passar a carteira
            if ($request->get('carteira')) {
                /** @var Carteira $carteira */
                $carteira = $this->getDoctrine()->getRepository(Carteira::class)->find($request->get('carteira'));
                if ($carteira) {
                    $movimentacao->setCarteira($carteira);
                }
            }

            /** @var Modo $emEspecie */
            $emEspecie = $this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 1]);
            $movimentacao->setModo($emEspecie);

            /** @var Categoria $vendasInternas */
            $vendasInternas = $this->getDoctrine()->getRepository(Categoria::class)->findOneBy(['codigo' => 101]);
            $movimentacao->setCategoria($vendasInternas);

            if ($dtMoviment = $request->get('dtMoviment')) {
                $movimentacao->setDtMoviment(DateTimeUtils::parseDateStr($dtMoviment));
            }

        }

        $params = [
            'typeClass' => MovimentacaoCaixaType::class,
            'formView' => 'movimentacaoForm_caixa.html.twig',
            'formRoute' => 'movimentacao_form_caixa',
            'formPageTitle' => 'Movimentação de Caixa'
        ];

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * @param EntityId $entityId
     * @return null|RedirectResponse|void
     */
    public function afterSave(EntityId $entityId)
    {
        /** @var Movimentacao $movimentacao */
        $movimentacao = clone $entityId;
        $movimentacao->setId(null);
        $movimentacao->setUUID(null);
        $movimentacao->setValor(null);
        $movimentacao->setDescontos(null);
        $movimentacao->setAcrescimos(null);
        $movimentacao->setValorTotal(null);
        $sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_form_caixa') ?? [];

        $sviParams['ultimaMovimentacaoSalva'] = $this->entityIdUtils->serialize($movimentacao);
        $this->storedViewInfoBusiness->set('movimentacao_form_caixa', $sviParams);
    }


    /**
     *
     * @Route("/movimentacao/caixa/delete/{movimentacao}", name="movimentacao_caixa_delete", requirements={"movimentacao"="\d+"})
     * @param Movimentacao $movimentacao
     * @return RedirectResponse
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function delete(Movimentacao $movimentacao): RedirectResponse
    {
        $dtMoviment = clone $movimentacao->getDtMoviment();
        $this->entityHandler->delete($movimentacao);
        return $this->redirectToRoute('movimentacao_caixa', ['dtMoviment' => $dtMoviment->format('Y-m-d')]);
    }


}