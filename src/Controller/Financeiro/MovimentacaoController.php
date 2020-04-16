<?php

namespace App\Controller\Financeiro;

use App\Business\Financeiro\MovimentacaoBusiness;
use App\Entity\Financeiro\Cadeia;
use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\GrupoItem;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\TipoLancto;
use App\EntityHandler\Financeiro\CadeiaEntityHandler;
use App\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use App\Form\Financeiro\MovimentacaoAlterarEmLoteType;
use App\Form\Financeiro\MovimentacaoChequeProprioType;
use App\Form\Financeiro\MovimentacaoPagtoType;
use App\Form\Financeiro\MovimentacaoTransferenciaEntreCarteirasType;
use App\Form\Financeiro\MovimentacaoType;
use App\Repository\Financeiro\CarteiraRepository;
use App\Repository\Financeiro\CategoriaRepository;
use App\Repository\Financeiro\ModoRepository;
use App\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Class MovimentacaoController.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoController extends FormListController
{

    /** @var MovimentacaoBusiness */
    private $business;

    /** @var SessionInterface */
    private $session;

    /** @var CadeiaEntityHandler */
    private $cadeiaEntityHandler;

    /** @var EntityIdUtils */
    private $entityIdUtils;

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
     * @required
     * @param EntityIdUtils $entityIdUtils
     */
    public function setEntityIdUtils(EntityIdUtils $entityIdUtils): void
    {
        $this->entityIdUtils = $entityIdUtils;
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
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData('id', 'EQ', 'id', $params),
            new FilterData('descricao', 'LIKE', 'descricao', $params),
            new FilterData('dtUtil', 'BETWEEN_DATE', 'dtUtil', $params),
            new FilterData('valorTotal', 'BETWEEN', 'valorTotal', $params, 'decimal'),
            new FilterData('chequeNumCheque', 'EQ', 'chequeNumCheque', $params),
            new FilterData('carteira', 'IN', 'carteira', $params),
            new FilterData('status', 'IN', 'status', $params),
            new FilterData('modo', 'IN', 'modo', $params),
            new FilterData('parcelamento', 'EQ', 'parcelamento', $params),
            new FilterData('recorrente', 'EQ', 'recorrente', $params),
            new FilterData('categoria', 'IN', 'categoria', $params)
        ];

    }


    /**
     *
     * @Route("/fin/movimentacao/list/", name="movimentacao_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'listView' => 'Financeiro/movimentacaoList.html.twig',
            'listRoute' => 'movimentacao_list',
            'listRouteAjax' => 'movimentacao_datatablesJsList',
            'listPageTitle' => 'Pesquisar Movimentações',
            'listId' => 'movimentacaoList',
            'formRouteEdit' => 'movimentacao_edit',
            'deleteRoute' => 'movimentacao_delete',
            'filterChoices' => $this->getFilterChoices()
        ];
        return $this->doList($request, $params);
    }

    /**
     * Constrói os valores para os campos de filtros.
     *
     * @return array
     * @throws ViewException
     */
    protected function getFilterChoices(): array
    {
        $filterChoices = array();

        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
        $carteiras = $repoCarteira->findAll(WhereBuilder::buildOrderBy('codigo'));
        $filterChoices['carteiras'] = $carteiras;

        $filterChoices['status'] = [
            'ABERTA',
            'REALIZADA'
        ];

        /** @var ModoRepository $repoModo */
        $repoModo = $this->getDoctrine()->getRepository(Modo::class);
        $modos = $repoModo->findAll();
        $filterChoices['modos'] = $modos;

        /** @var CategoriaRepository $repoCateg */
        $repoCateg = $this->getDoctrine()->getRepository(Categoria::class);
        $categorias = $repoCateg->buildTreeList();
        $filterChoices['categorias'] = $categorias;

        return $filterChoices;
    }

    /**
     *
     * @Route("/fin/movimentacao/datatablesJsList/", name="movimentacao_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/movimentacao/listCadeia/{cadeia}", name="movimentacao_listCadeia", requirements={"cadeia"="\d+"})
     * @param Cadeia $cadeia
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function listCadeia(Cadeia $cadeia): Response
    {
        $total = $this->business->somarMovimentacoes($cadeia->getMovimentacoes());
        return $this->doRender('Financeiro/movimentacaoList_cadeia.html.twig', ['cadeia' => $cadeia, 'total' => $total]);
    }

    /**
     *
     * @Route("/fin/cadeia/delete/{cadeia}", name="cadeia_delete", requirements={"cadeia"="\d+"})
     * @param Cadeia $cadeia
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function cadeiaDelete(Cadeia $cadeia): Response
    {
        try {
            $this->cadeiaEntityHandler->deleteCadeiaETodasAsMovimentacoes($cadeia);
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao deletar cadeia');
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('movimentacao_list');
    }

    /**
     *
     * @Route("/fin/movimentacao/delete/{id}", name="movimentacao_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao $movimentacao
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function delete(Request $request, Movimentacao $movimentacao): Response
    {
        try {
            $this->entityHandler->delete($movimentacao);
            $this->addFlash('success', 'Movimentação deletada com sucesso');
        } catch (ViewException $e) {
            $this->logger->error('Erro ao deletar movimentação');
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $e->getMessage());
            $this->addFlash('error', $e->getMessage());
        }
        if ($request->server->get('HTTP_REFERER')) {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
        return $this->redirectToRoute('movimentacao_list');
    }

    /**
     *
     * @Route("/fin/movimentacao/list/alterarLote/", name="movimentacao_list_alterarLote")
     * @param Request $request
     * @param $UUID
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function alterarLote(Request $request)
    {
        if ($request->get('btnAlterarEmLote')) {
            if (!$request->get('movsSelecionadas')) {
                $this->addFlash('warn', 'Nenhuma movimentação selecionada.');
                return $this->redirectToRoute('movimentacao_import');
            }
            $movsSel = $request->get('movsSelecionadas');
            $this->session->set('movsSelecionadas', $movsSel);
        }

        $form = $this->createForm(MovimentacaoAlterarEmLoteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $lote = [];
                $movsSel = $this->session->get('movsSelecionadas');
                foreach ($movsSel as $id => $on) {
                    $lote[] = $this->getDoctrine()->getRepository(Movimentacao::class)->find($id);
                }

                $movimentacao = $form->getData();
                $this->business->alterarEmLote($lote, $movimentacao);
                /** @var MovimentacaoEntityHandler $movimentacaoEntityHandler */
                $movimentacaoEntityHandler = $this->entityHandler;
                $movimentacaoEntityHandler->saveAll($lote);

                $this->addFlash('success', 'Movimentações alteradas com sucesso.');
                return $this->redirectToRoute('movimentacao_list');
            }
            $form->getErrors(true, false);
        }

        // Pode ou não ter vindo algo no $params. Independentemente disto, só adiciono form e foi-se.
        $params['form'] = $form->createView();

        return $this->doRender('Financeiro/movimentacaoAlterarEmLoteForm.html.twig', $params);
    }


    /**
     *
     * @Route("/fin/movimentacao/form/ini", name="movimentacaoForm_ini")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function ini(Request $request)
    {
        $repoTipoLancto = $this->getDoctrine()->getRepository(TipoLancto::class);
        if ($request->get('tipoLancto')) {
            $tipoLancto = $repoTipoLancto->find($request->get('tipoLancto'));
            return $this->redirect($tipoLancto->getUrl() . (strpos($tipoLancto->getDescricao(), 'PARCELAMENTO') !== FALSE ? '?parcelamento=true' : ''));
        }
        $tiposLanctos = $repoTipoLancto->findAll(['codigo' => 'ASC']);

        return $this->doRender('Financeiro/movimentacaoIniForm.html.twig', ['tiposLanctos' => $tiposLanctos]);
    }


    /**
     *
     * @Route("/fin/movimentacao/edit/{id}", name="movimentacao_edit", requirements={"id"="\d+"})
     * @param UrlMatcherInterface $urlMatcher
     * @param Request $request
     * @param Movimentacao $movimentacao
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function edit(UrlMatcherInterface $urlMatcher, Request $request, Movimentacao $movimentacao): RedirectResponse
    {
        $url = $movimentacao->getTipoLancto()->url;
        $matcher = $urlMatcher->match($url);
        $params = ['id' => $movimentacao->getId(), 'request' => $request];
//        if (strpos($movimentacao->getTipoLancto()->getDescricao(), 'PARCELAMENTO') !== FALSE) {
//            $params[] = ['parcelamento' => true];
//        }
        return $this->redirectToRoute($matcher['_route'], $params, 307);
    }

    /**
     * Edição geral de uma movimentação (com exibição de todos os campos).
     *
     * @Route("/fin/movimentacao/form/geral/{id}", name="movimentacao_form_geral", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formGeral(Request $request, Movimentacao $movimentacao = null)
    {
        $params = [
            'typeClass' => MovimentacaoType::class,
            'formView' => 'Financeiro/movimentacaoForm_geral.html.twig',
            'formRoute' => 'movimentacaoForm_ini',
            'formRouteEdit' => 'movimentacao_edit',
            'formPageTitle' => 'Movimentação'
        ];

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * Form para movimentações do tipoLancto 40.
     *
     * @Route("/fin/movimentacao/form/chequeProprio/{id}", name="movimentacao_form_chequeProprio", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formChequeProprio(Request $request, Movimentacao $movimentacao = null)
    {
        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->getParcelamento();
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 40]));
            $movimentacao->setModo($this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 3]));
        }

        $params = [
            'formRoute' => 'movimentacao_form_chequeProprio',
            'typeClass' => MovimentacaoChequeProprioType::class,
            'formView' => 'Financeiro/movimentacaoForm_chequeProprio.html.twig'
        ];

        if ($parcelamento && !$movimentacao->getId()) {
            $params['formView'] = 'Financeiro/movimentacaoForm_chequeProprio_parcelamento.html.twig';
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
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function handleParcelamento(Request $request, Movimentacao $movimentacao, array $params = []): Response
    {
        $form = $this->createForm($params['typeClass'], $movimentacao);

        $params['qtdeParcelas'] = $request->get('qtdeParcelas');
        $params['dtPrimeiroVencto'] = $request->get('dtPrimeiroVencto');
        $params['valor'] = $request->get('valor');
        $params['tipoValor'] = $request->get('tipoValor');


        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                if ($request->get('btnLimparParcelas')) {
                } else {

                    $qtdeParcelas = $request->get('qtdeParcelas');
                    $dtPrimeiroVencto = DateTimeUtils::parseDateStr($request->get('dtPrimeiroVencto'));
                    $valor = DecimalUtils::parseStr($request->get('valor'));
                    $tipoValor = $request->get('tipoValor');
                    $parcelas = $request->get('parcelas');

                    $movimentacao = $form->getData();
                    $this->business->gerarParcelas($movimentacao, $qtdeParcelas, $valor, $dtPrimeiroVencto, $tipoValor === 'TOTAL', $parcelas);
                    if ($request->get('btnSalvar')) {
                        try {
                            $this->entityHandler->saveAll($movimentacao->getCadeia()->getMovimentacoes(), true);
                            $this->addFlash('success', 'Registro salvo com sucesso!');
                            $this->afterSave($movimentacao);
                            return $this->redirectTo($request, $movimentacao, $params['formRoute']);
                        } catch (ViewException $e) {
                            $this->addFlash('error', $e->getMessage());
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
     * Form para movimentações do tipoLancto 42.
     *
     * @Route("/fin/movimentacao/form/chequeTerceiros/{id}", name="movimentacao_form_chequeTerceiros", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formChequeTerceiros(Request $request, Movimentacao $movimentacao = null)
    {
        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->getParcelamento();
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 50]));
            $movimentacao->setModo($this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 3]));
        }

        $params['formRoute'] = 'movimentacao_form_chequeTerceiros';
        $params['typeClass'] = MovimentacaoChequeProprioType::class;
        $params['formView'] = 'Financeiro/movimentacaoForm_chequeTerceiros.html.twig';

        if ($parcelamento && !$movimentacao->getId()) {
            $params['formView'] = 'Financeiro/movimentacaoForm_chequeTerceiros_parcelamento.html.twig';
            return $this->handleParcelamento($request, $movimentacao, $params);
        }
        // else

        return $this->doForm($request, $movimentacao, $params);

    }

    /**
     * Form para movimentações do tipoLancto 60.
     *
     * @Route("/fin/movimentacao/form/transferenciaEntreCarteiras/{id}", name="movimentacao_form_transferenciaEntreCarteiras", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formTransferenciaEntreCarteiras(Request $request, Movimentacao $movimentacao = null)
    {
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 60]));
            $movimentacao->setCategoria($this->getDoctrine()->getRepository(Categoria::class)->findOneBy(['codigo' => 299]));
        }

        $params['typeClass'] = MovimentacaoTransferenciaEntreCarteirasType::class;
        $params['formView'] = 'Financeiro/movimentacaoForm_transferenciaEntreCarteiras.html.twig';
        $params['formPageTitle'] = 'Transferência Entre Carteiras';
        $params['formRoute'] = 'movimentacao_form_transferenciaEntreCarteiras';

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * Form para movimentações do tipoLancto 61.
     *
     * @Route("/fin/movimentacao/form/transferenciaEntradaCaixa/{id}", name="movimentacao_form_transferenciaEntradaCaixa", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formTransferenciaEntradaCaixa(Request $request, Movimentacao $movimentacao = null)
    {
        $params['formView'] = 'Financeiro/movimentacaoForm_transferenciaEntradaCaixa.html.twig';
        $params['typeClass'] = MovimentacaoType::class;
        $params['formRoute'] = 'movimentacao_form_transferenciaEntradaCaixa';
        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * Form para movimentações do tipoLancto 70.
     *
     * @Route("/fin/movimentacao/form/grupo/{grupoItem}/{id}", name="movimentacao_form_grupo", defaults={"id"=null}, requirements={"grupoItem"="\d+", "id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formGrupo(Request $request, GrupoItem $grupoItem, Movimentacao $movimentacao = null)
    {
        if (!$grupoItem) {
            $this->addFlash('error', 'Nenhum grupo informado');
            return $this->redirectToRoute('grupoItem_listMovs');
        }

        if (!$movimentacao && ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_form_grupo'))) {
            if (isset($sviParams['ultimaMovimentacaoSalva'])) {
                /** @var Movimentacao $movimentacao */
                $movimentacao = EntityIdUtils::unserialize($sviParams['ultimaMovimentacaoSalva'], Movimentacao::class);

                if ($movimentacao) {
                    $this->business->refindAll($movimentacao);
                    $movimentacao->setId(null);
                    $movimentacao->setCadeia(null);
                    $movimentacao->setValor(null);
                    $movimentacao->setDescontos(null);
                    $movimentacao->setAcrescimos(null);
                    $movimentacao->setValorTotal(null);
                    $movimentacao->setDtUtil(null);
                    $movimentacao->setDtMoviment(null);
                    $movimentacao->setDtPagto(null);
                    $movimentacao->setDtVencto(null);
                    $movimentacao->setDtVenctoEfetiva(null);
                    $movimentacao->setCadeiaOrdem(null);
                    $movimentacao->setCadeiaQtde(null);
                    $movimentacao->setUserInsertedId(null);
                    $movimentacao->setUserUpdatedId(null);
                    $movimentacao->setInserted(null);
                    $movimentacao->setUpdated(null);
                }

            }
        }

        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->getParcelamento();
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
        }

        $tipoLancto = $parcelamento ? 71 : 70;
        $movimentacao->setTipoLancto($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => $tipoLancto]));
        $movimentacao->setCarteira($this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['codigo' => 7]));
        $movimentacao->setModo($this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 50]));
        $movimentacao->setGrupoItem($grupoItem);

        $params['typeClass'] = MovimentacaoType::class; // ???
        $params['formView'] = 'Financeiro/movimentacaoForm_grupo.html.twig';
        $params['formPageTitle'] = 'Movimentação de Grupo';

        if ($parcelamento) {
            return $this->handleParcelamento($request, $movimentacao, $params);
        }

        $params['formView'] = $parcelamento ? 'Financeiro/movimentacaoForm_grupo_parcelamento.html.twig' : 'Financeiro/movimentacaoForm_grupo.html.twig';
        $params['typeClass'] = MovimentacaoType::class;
        $params['formRoute'] = 'movimentacao_form_grupo';


        $r = $this->doForm($request, $movimentacao, $params);
        if ($movimentacao->getId()) {
            $sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_form_grupo') ?? [];
            $sviParams['ultimaMovimentacaoSalva'] = EntityIdUtils::serialize($movimentacao);
            $this->storedViewInfoBusiness->set('movimentacao_form_grupo', $sviParams);
        }
        return $r;
    }

    /**
     * Form para movimentações do tipoLancto 80.
     *
     * @Route("/fin/movimentacao/form/estorno/{id}", name="movimentacao_form_estorno", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formEstorno(Request $request, Movimentacao $movimentacao = null)
    {
        $params['formView'] = 'Financeiro/movimentacaoForm_estorno.html.twig';
        $params['typeClass'] = MovimentacaoType::class;
        $params['formRoute'] = 'movimentacao_form_estorno';
        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * Form para movimentações do tipoLancto 80.
     *
     * @Route("/fin/movimentacao/form/pagto/{id}", name="movimentacao_form_pagto", requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formPagto(Request $request, Movimentacao $movimentacao)
    {
        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);

        if ($request->get('filter')) {
            $params['movsPesquisarLanctos'] = $this->handleFormPagtoPesquisarLanctos($request);

            $params['filter'] = $request->get('filter');

        } else {
            $carteiraIndefinida = $repoCarteira->findOneBy(['codigo' => 99]);
            $params['filter']['carteiras'] = [$carteiraIndefinida->getId()];
            if ($movimentacao && $movimentacao->getCarteira()->getCodigo() !== 99) {
                $params['filter']['carteiras'][] = $movimentacao->getCarteira()->getId();
            }
            $dtIni = (clone $movimentacao->getDtVencto())->sub(new \DateInterval('P5D'))->format('d/m/Y');
            $dtFim = (clone $movimentacao->getDtVencto())->add(new \DateInterval('P5D'))->format('d/m/Y');
            $params['filter']['dts'] = $dtIni . ' - ' . $dtFim;

            $params['filter']['valor']['i'] = number_format($movimentacao->getValorTotal(), 2, ',', '.');
            $params['filter']['valor']['f'] = number_format((float)bcmul($movimentacao->getValorTotal(), 1.3, 2), 2, ',', '.');
        }
        $carteirasOptions = $repoCarteira->findAll(['codigo' => 'ASC']);

        $params['carteirasOptions'] = json_encode(
            Select2JsUtils::toSelect2DataFn($carteirasOptions, function ($e) {
                /** @var TipoLancto $e */
                return $e->getDescricaoMontada();
            }, $params['filter']['carteiras'] ?? null));

        $params['formRoute'] = 'movimentacao_form_pagto';
        $params['formView'] = 'Financeiro/movimentacaoForm_pagto.html.twig';
        $params['typeClass'] = MovimentacaoPagtoType::class;

        // se a ação for do btnPesquisarLanctos, não submete o form
        $preventSubmit = $request->get('btnPesquisarLanctos') ? true : false;
        if ($preventSubmit) {
            $params['_fragment'] = 'pesquisarLanctos';
        }

        return $this->doForm($request, $movimentacao, $params, $preventSubmit);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws ViewException
     */
    private function handleFormPagtoPesquisarLanctos(Request $request)
    {
        $params = $request->request->all();

        $dtIni = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 0, 10));
        $dtIni->setTime(0, 0, 0, 0);
        $dtFim = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 13, 10));
        $dtFim->setTime(23, 59, 59, 99999);

        $carteiras = $params['filter']['carteiras'] ?? null;

        /** @var MovimentacaoRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Movimentacao::class);

        $valorI = DecimalUtils::parseStr($params['filter']['valor']['i']);
        $valorF = DecimalUtils::parseStr($params['filter']['valor']['f']);

        $filtersSimpl = [
            ['valorTotal', 'BETWEEN', [$valorI, $valorF]],
            ['dtUtil', 'BETWEEN_DATE', [$dtIni, $dtFim]],
            ['carteira', 'IN', $carteiras],
            ['status', 'EQ', 'REALIZADA'],
            ['categ.codigo', 'NOT_IN', [199, 299]]
        ];

        $orders = [
            'e.valorTotal' => 'asc',
            'e.dtUtil' => 'asc',
            'categ.codigoSuper' => 'asc',
        ];

        return $repo->findByFiltersSimpl($filtersSimpl, $orders, 0, -1);
    }


    /**
     * Form para movimentações do tipoLancto 80.
     *
     * @Route("/fin/movimentacao/form/pagto/pagarAbertaComRealizada/{aberta}/{realizada}", name="movimentacao_form_pagto_pagarAbertaComRealizada", requirements={"aberta"="\d+","realizada"="\d+"})
     * @param Movimentacao $aberta
     * @param Movimentacao $realizada
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function pagarAbertaComRealizada(Movimentacao $aberta, Movimentacao $realizada): ?RedirectResponse
    {
        try {
            $this->business->pagarAbertaComRealizada($aberta, $realizada);
            $this->addFlash('success', 'Sucesso ao pagar aberta com realizada');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao pagar aberta com realizada.');
        }
        return $this->redirectToRoute('aPagarReceber_list');
    }

}