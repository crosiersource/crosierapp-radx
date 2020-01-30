<?php

namespace App\Controller\Financeiro;

use App\Business\Financeiro\MovimentacaoBusiness;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\TipoLancto;
use App\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use App\Form\Financeiro\MovimentacaoType;
use App\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MovimentacaoRecorrentesController
 *
 * Listagem e geração de movimentações recorrentes.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoRecorrentesController extends FormListController
{

    /** @var MovimentacaoEntityHandler */
    protected $entityHandler;

    /** @var MovimentacaoBusiness */
    private $business;

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
     * @param MovimentacaoBusiness $business
     */
    public function setBusiness(MovimentacaoBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     *
     * @Route("/fin/movimentacaoRecorrente/list", name="movimentacaoRecorrente_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function list(Request $request): Response
    {

        $parameters = $request->query->all();

        if (!array_key_exists('filter', $parameters)) {

            if ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacaoRecorrente_list')) {
                $parameters['filter']['dts'] = $sviParams['dts'];
            } else {
                // inicializa para evitar o erro
                $parameters['filter'] = array();
                $parameters['filter']['dts'] = DateTimeUtils::getPrimeiroDiaMes()->format('d/m/Y') . ' - ' . DateTimeUtils::getUltimoDiaMes()->format('d/m/Y');
            }
        }

        $dtIni = DateTimeUtils::parseDateStr(substr($parameters['filter']['dts'], 0, 10));
        $dtFim = DateTimeUtils::parseDateStr(substr($parameters['filter']['dts'], 13, 10));

        $parameters['filter']['dtVencto']['i'] = $dtIni->format('Y-m-d');
        $parameters['filter']['dtVencto']['f'] = $dtFim->format('Y-m-d');

        $parameters['filter']['recorrente'] = true;

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);

        $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true, true);
        $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false, true);
        $parameters['antePeriodoI'] = $ante['dtIni'];
        $parameters['antePeriodoF'] = $ante['dtFim'];
        $parameters['proxPeriodoI'] = $prox['dtIni'];
        $parameters['proxPeriodoF'] = $prox['dtFim'];

        $sviParams = [
            'dts' => $parameters['filter']['dts']
        ];
        $this->storedViewInfoBusiness->store('movimentacaoRecorrente_list', $sviParams);


        $filterDatas = $this->getFilterDatas($parameters);

        /** @var MovimentacaoRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Movimentacao::class);
        $orders = [
            'e.recorrDia' => 'asc',
            'e.descricao' => 'asc',
        ];
        $parameters['dados'] = $repo->findByFilters($filterDatas, $orders, 0, null);

        return $this->doRender('Financeiro/movimentacaoRecorrentesList.html.twig', $parameters);

    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(array('id', 'descricao'), 'LIKE', 'descricao', $params),
            new FilterData('dtVencto', 'BETWEEN_DATE', 'dtVencto', $params),
            new FilterData('recorrente', 'EQ', 'recorrente', $params),
        ];
    }

    /**
     *
     * @Route("/fin/movimentacaoRecorrente/processar", name="movimentacaoRecorrente_processar")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function processar(Request $request)
    {
        try {
            $movsSelecionadas = $request->get('movsSelecionadas');
            $rMovs = [];
            if (!$movsSelecionadas) {
                $this->addFlash('warn', 'Nenhuma movimentação selecionada');
            } else {
                foreach ($movsSelecionadas as $movId => $on) {
                    $rMovs[] = $this->getDoctrine()->getRepository(Movimentacao::class)->find($movId);
                }
                $msgs = $this->business->processarRecorrentes($rMovs);
                $sMsgs = explode("\r\n", $msgs);
                foreach ($sMsgs as $sMsg) {
                    $this->addFlash('msgsProcessarRecorrentes', $sMsg);
                }
            }
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->addFlash('error', $msg);
            $this->addFlash('error', 'Erro ao processar recorrentes');
        }
        return $this->redirectToRoute('movimentacaoRecorrente_list');
    }


    /**
     *
     * @Route("/fin/movimentacao/form/recorrente/{id}", name="movimentacao_form_recorrente", defaults={"id"=null}, requirements={"movimentacao"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function form(Request $request, Movimentacao $movimentacao = null)
    {
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 90]));
            $movimentacao->setRecorrente(true);
        }

        $params = [
            'typeClass' => MovimentacaoType::class,
            'formView' => 'Financeiro/movimentacaoForm_recorrente.html.twig',
            'formRoute' => 'movimentacaoForm_ini',
            'formRouteEdit' => 'movimentacao_edit',
            'formPageTitle' => 'Movimentação Recorrente'
        ];

        return $this->doForm($request, $movimentacao, $params);
    }


}
