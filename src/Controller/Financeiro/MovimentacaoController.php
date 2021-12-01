<?php

namespace App\Controller\Financeiro;

use App\Form\Financeiro\MovimentacaoAlterarEmLoteType;
use App\Form\Financeiro\MovimentacaoAPagarReceberType;
use App\Form\Financeiro\MovimentacaoChequeProprioType;
use App\Form\Financeiro\MovimentacaoGeralType;
use App\Form\Financeiro\MovimentacaoPagtoType;
use App\Form\Financeiro\MovimentacaoTransferenciaEntreCarteirasType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Cadeia;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\BandeiraCartaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CarteiraRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CentroCustoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\ModoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\OperadoraCartaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\TipoLanctoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoController extends FormListController
{

    private MovimentacaoBusiness $business;

    private SessionInterface $session;

    private EntityIdUtils $entityIdUtils;

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
        return $this->doDatatablesJsList($request, null, null, null, ['outrosGruposSerializ' => ['carteira', 'modo', 'categoria']]);
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
        $total = $this->business->somarMovimentacoes($cadeia->movimentacoes);
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
            $this->getEntityHandler()->deleteCadeiaETodasAsMovimentacoes($cadeia);
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
        /** @var TipoLanctoRepository $repoTipoLancto */
        $repoTipoLancto = $this->getDoctrine()->getRepository(TipoLancto::class);
        $tiposLanctos = $repoTipoLancto->findAll(['codigo' => 'ASC']);
        return $this->doRender('Financeiro/movimentacaoIniForm.html.twig', ['tiposLanctos' => $tiposLanctos]);
    }


    /**
     *
     * @Route("/fin/movimentacao/edit/{id}", name="movimentacao_edit", requirements={"id"="\d+"})
     * @param Movimentacao $movimentacao
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function edit(Movimentacao $movimentacao): RedirectResponse
    {
        $url = $this->business->getEditingURL($movimentacao) . $movimentacao->getId();
        return $this->redirect($url);
    }


    /**
     * @Route("/fin/movimentacao/form/realizada/{id}", name="fin_movimentacao_form_realizada", defaults={"id"=null}, requirements={"movimentacao"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formRealizada(Request $request, Movimentacao $movimentacao = null)
    {
        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->parcelamento;
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->carteira = ($this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['codigo' => 99]));

            $tipoLanctoCodigo = $parcelamento ? 21 : 20;
            $movimentacao->tipoLancto = ($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => $tipoLanctoCodigo]));
            $movimentacao->status = 'REALIZADA';

        }

        $params = [
            'typeClass' => MovimentacaoAPagarReceberType::class,
            'formRoute' => 'fin_movimentacao_form_realizada',
            'formPageTitle' => 'Movimentação Realizada',
        ];

        $params['formView'] = 'Financeiro/movimentacaoForm_realizada.html.twig';

        if (!$movimentacao->getId() && $parcelamento) {
            $params['formView'] = 'Financeiro/movimentacaoForm_realizada_parcelamento.html.twig';
            return $this->handleParcelamento($request, $movimentacao, $params);
        }
        // else

        return $this->doForm($request, $movimentacao, $params);
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
            'typeClass' => MovimentacaoGeralType::class,
            'formView' => 'Financeiro/movimentacaoForm_geral.html.twig',
            'formRoute' => 'movimentacaoForm_ini',
            'formRouteEdit' => 'movimentacao_edit',
            'formPageTitle' => 'Movimentação'
        ];

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * @Route("/fin/movimentacao/form/aPagarReceber/{id}", name="fin_movimentacao_form_aPagarReceber", defaults={"id"=null}, requirements={"movimentacao"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formPagarReceber(Request $request, Movimentacao $movimentacao = null)
    {
        $parcelamento = false;
        if ($movimentacao) {
            if ($movimentacao->dtPagto) {
                return $this->edit($movimentacao);
            }
            $parcelamento = $movimentacao->parcelamento;
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->carteira = ($this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['codigo' => 99]));
            $tipoLanctoCodigo = 20;
            $movimentacao->tipoLancto = ($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => $tipoLanctoCodigo]));
            $movimentacao->status = 'ABERTA';
        }

        $params = [
            'typeClass' => MovimentacaoAPagarReceberType::class,
            'formRoute' => 'fin_movimentacao_form_aPagarReceber',
            'formPageTitle' => 'Movimentação a Pagar/Receber',
            'formView' => 'Financeiro/movimentacaoForm_aPagarReceber.html.twig'
        ];

        if (!$movimentacao->getId() && $parcelamento) {
            $params['formView'] = 'Financeiro/movimentacaoForm_aPagarReceber_parcelamento.html.twig';
            return $this->handleParcelamento($request, $movimentacao, $params);
        }
        // else

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
            $parcelamento = $movimentacao->parcelamento;
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->tipoLancto = $this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 40]);
            $movimentacao->modo = $this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 3]);
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

                if (!$request->get('btnLimparParcelas')) {
                    $qtdeParcelas = $request->get('qtdeParcelas');
                    $dtPrimeiroVencto = DateTimeUtils::parseDateStr($request->get('dtPrimeiroVencto'));
                    $valor = DecimalUtils::parseStr($request->get('valor'));
                    $tipoValor = $request->get('tipoValor');
                    $parcelas = $request->get('parcelas');

                    /** @var Movimentacao $movimentacao */
                    $movimentacao = $form->getData();
                    $this->business->gerarParcelas($movimentacao, $qtdeParcelas, $valor, $dtPrimeiroVencto, $tipoValor === 'TOTAL', $parcelas);
                    if ($request->get('btnSalvar')) {
                        try {
                            $this->entityHandler->saveAll($movimentacao->cadeia->movimentacoes, true);
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
     * @param Request $request
     * @param Movimentacao $movimentacao
     * @param array $params
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function handleParcelamento_(Request $request, Movimentacao $movimentacao, array $params = []): Response
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
                            $this->entityHandler->saveAll($movimentacao->cadeia->movimentacoes, true);
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
            $parcelamento = $movimentacao->parcelamento;
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
            $movimentacao->tipoLancto = $this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 50]);
            $movimentacao->modo = $this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 3]);
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
            $movimentacao->tipoLancto = $this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 60]);
            $movimentacao->categoria = $this->getDoctrine()->getRepository(Categoria::class)->findOneBy(['codigo' => 299]);
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
        $params['typeClass'] = MovimentacaoGeralType::class;
        $params['formRoute'] = 'movimentacao_form_transferenciaEntradaCaixa';
        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * Form para movimentações do tipoLancto 70.
     *
     * @Route("/fin/movimentacao/form/grupo/{id}", name="movimentacao_form_grupo", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formGrupo(Request $request, Movimentacao $movimentacao = null)
    {
        if (!$movimentacao && ($sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_form_grupo'))) {
            if (isset($sviParams['ultimaMovimentacaoSalva'])) {
                /** @var Movimentacao $movimentacao */
                $movimentacao = $this->entityIdUtils->unserialize($sviParams['ultimaMovimentacaoSalva'], Movimentacao::class);

                if ($movimentacao) {
                    $this->entityHandler->refindAll($movimentacao);
                    $movimentacao->setId(null);
                    $movimentacao->cadeia = null;
                    $movimentacao->valor = null;
                    $movimentacao->descontos = null;
                    $movimentacao->acrescimos = null;
                    $movimentacao->valorTotal = null;
                    $movimentacao->dtUtil = null;
                    $movimentacao->dtMoviment = null;
                    $movimentacao->dtPagto = null;
                    $movimentacao->dtVencto = null;
                    $movimentacao->dtVenctoEfetiva = null;
                    $movimentacao->cadeiaOrdem = null;
                    $movimentacao->cadeiaQtde = null;
                    $movimentacao->setUserInsertedId(null);
                    $movimentacao->setUserUpdatedId(null);
                    $movimentacao->setInserted(null);
                    $movimentacao->setUpdated(null);
                }

            }
        }

        $parcelamento = false;
        if ($movimentacao) {
            $parcelamento = $movimentacao->parcelamento;
        } else if ($request->get('parcelamento')) {
            $parcelamento = true;
        }

        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
        }

        $tipoLancto = $parcelamento ? 71 : 70;
        $movimentacao->tipoLancto = ($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => $tipoLancto]));
        $movimentacao->carteira = ($this->getDoctrine()->getRepository(Carteira::class)->findOneBy(['codigo' => 7]));
        $movimentacao->modo = ($this->getDoctrine()->getRepository(Modo::class)->findOneBy(['codigo' => 50]));

        $params['typeClass'] = MovimentacaoGeralType::class; // ???
        $params['formView'] = 'Financeiro/movimentacaoForm_grupo.html.twig';
        $params['formPageTitle'] = 'Movimentação de Grupo';

        if ($parcelamento) {
            return $this->handleParcelamento($request, $movimentacao, $params);
        }

        $params['formView'] = $parcelamento ? 'Financeiro/movimentacaoForm_grupo_parcelamento.html.twig' : 'Financeiro/movimentacaoForm_grupo.html.twig';
        $params['typeClass'] = MovimentacaoGeralType::class;
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
        $params['typeClass'] = MovimentacaoGeralType::class;
        $params['formRoute'] = 'movimentacao_form_estorno';
        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * @Route("/fin/movimentacao/form/pagto/{id}", name="movimentacao_form_pagto", defaults={"id"=null})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formPagto(Request $request, ?Movimentacao $movimentacao = null)
    {
        $params = [
            'typeClass' => MovimentacaoPagtoType::class,
            'formRoute' => 'movimentacao_form_pagto',
            'formView' => 'Financeiro/movimentacaoForm_pagto.html.twig'
        ];

        return $this->doForm($request, $movimentacao, $params);
    }

    /**
     * @Route("/fin/movimentacao/form/rapida/{id}", name="movimentacao_form_rapida", defaults={"id"=null})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function formMovimentacaoRapida(Request $request, ?Movimentacao $movimentacao = null)
    {
        $params = [
            'typeClass' => MovimentacaoPagtoType::class,
            'formRoute' => 'movimentacao_form_rapida',
            'formView' => 'Financeiro/movimentacaoForm_rapida.html.twig'
        ];

        if (!$movimentacao || !$movimentacao->getId()) {
            $movimentacao = new Movimentacao();
            $movimentacao->tipoLancto = ($this->getDoctrine()->getRepository(TipoLancto::class)->findOneBy(['codigo' => 20]));
        }
        $movimentacao->status = 'REALIZADA';

        $fnHandleRequestOnValid = function (Request $request, Movimentacao $movimentacao) {
            $movimentacao->dtPagto = $movimentacao->dtMoviment;
            $movimentacao->valorTotal = $movimentacao->valor;
        };

        // Verifique o método handleRequestOnValid abaixo
        return $this->doForm($request, $movimentacao, $params, false, $fnHandleRequestOnValid);
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
        $dtIni->setTime(0, 0);
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


    /**
     *
     * @Route("/fin/movimentacao/pesquisaList/", name="fin_movimentacao_pesquisaList")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function pesquisaList(Request $request): Response
    {
        $params = [
            'listView' => 'Financeiro/movimentacao_pesquisa_list.html.twig',
            'listRoute' => 'fin_movimentacao_pesquisaList'
        ];


        $fnGetFilterDatas = function (array $params): array {
            return [
                new FilterData(['id'], 'LIKE', 'id', $params),
                new FilterData(['notafiscal_id'], 'EQ', 'notafiscal_id', $params, null, true),
                new FilterData(['descricao'], 'LIKE', 'descricao', $params),
                new FilterData(['carteira'], 'IN', 'carteira', $params),
                new FilterData(['categoria'], 'IN', 'categoria', $params),
                new FilterData(['centroCusto'], 'IN', 'centroCusto', $params),
                new FilterData(['status'], 'EQ', 'status', $params),
                new FilterData(['dtUtil'], 'BETWEEN_DATE_CONCAT', 'dts', $params),
                new FilterData(['chequeNumCheque'], 'LIKE_END', 'chequeNumCheque', $params),
                new FilterData(['operadoraCartao'], 'IN', 'operadoraCartao', $params),
                new FilterData(['bandeiraCartao'], 'IN', 'bandeiraCartao', $params),
                new FilterData(['recorrente'], 'EQ_BOOL', 'recorrente', $params),
                new FilterData(['valor', 'valorTotal'], 'BETWEEN', 'valor', $params, 'decimal'),
            ];
        };


        $params['limit'] = 200;

        $filters = $request->get('filter');

        /** @var ModoRepository $repoModo */
        $repoModo = $this->getDoctrine()->getRepository(Modo::class);
        $params['modos'] = $repoModo->getSelect2js($filters['modo'] ?? null);

        /** @var CategoriaRepository $repoModo */
        $repoCategoria = $this->getDoctrine()->getRepository(Categoria::class);
        $params['categorias'] = $repoCategoria->getSelect2js($filters['categoria'] ?? null);

        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
        $params['carteiras'] = $repoCarteira->getSelect2js($filters['carteiras'] ?? null);

        /** @var CentroCustoRepository $repoCentroCusto */
        $repoCentroCusto = $this->getDoctrine()->getRepository(CentroCusto::class);
        $params['centrosCusto'] = $repoCentroCusto->getSelect2js($filters['centroCusto'] ?? null);

        $params['status'] = json_encode([
            ['id' => '', 'text' => '...', 'selected' => ($filters['status'] ?? '') === ''],
            ['id' => 'ABERTA', 'text' => 'ABERTA', 'selected' => ($filters['status'] ?? '') === 'ABERTA'],
            ['id' => 'REALIZADA', 'text' => 'REALIZADA', 'selected' => ($filters['status'] ?? '') === 'REALIZADA'],
        ]);

        if ($filters['dts'] ?? false) {
            $dtIni = DateTimeUtils::parseDateStr(substr($filters['dts'], 0, 10));
            $dtFim = DateTimeUtils::parseDateStr(substr($filters['dts'], 13, 10));
            /** @var DiaUtilRepository $repoDiaUtil */
            $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);
            $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true);
            $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false);
            $params['antePeriodoI'] = $ante['dtIni'];
            $params['antePeriodoF'] = $ante['dtFim'];
            $params['proxPeriodoI'] = $prox['dtIni'];
            $params['proxPeriodoF'] = $prox['dtFim'];
        }

        /** @var OperadoraCartaoRepository $repoOperadoraCartao */
        $repoOperadoraCartao = $this->getDoctrine()->getRepository(OperadoraCartao::class);
        $params['operadorasCartao'] = $repoOperadoraCartao->getSelect2js($filters['operadoraCartao'] ?? null);

        /** @var BandeiraCartaoRepository $repoBandeiraCartao */
        $repoBandeiraCartao = $this->getDoctrine()->getRepository(BandeiraCartao::class);
        $params['bandeirasCartao'] = $repoBandeiraCartao->getSelect2js($filters['bandeirasCartao'] ?? null);

        $fnHandleDadosList = function (array &$dados, int $totalRegistros) use ($params) {
            if (count($dados) >= $params['limit'] && $totalRegistros > $params['limit']) {
                $this->addFlash('warn', 'Retornando apenas ' . $params['limit'] . ' registros de um total de ' . $totalRegistros . '. Utilize os filtros!');
            }
        };

        return $this->doListSimpl($request, $params, $fnGetFilterDatas, $fnHandleDadosList);
    }


    /**
     *
     * @Route("/fin/movimentacao/filiais/", name="fin_movimentacao_filiais")
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function filiais(): Response
    {
        return new JsonResponse($this->business->getSelect2jsFiliais());
    }

    /**
     *
     * @Route("/fin/movimentacao/findSacadoOuCedente/", name="fin_movimentacao_findSacadoOuCedente")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function findSacadoOuCedente(Request $request): Response
    {
        $term = $request->get('term');
        /** @var MovimentacaoRepository $repoMovimentacao */
        $repoMovimentacao = $this->getDoctrine()->getRepository(Movimentacao::class);

        $rs = $repoMovimentacao->findSacadoOuCedente($term);
        $fn = function ($e) {
            return StringUtils::mascararCnpjCpf($e['documento']) . ' - ' . $e['nome'];
        };
        $s2js = Select2JsUtils::toSelect2DataFn($rs, $fn, [], $fn);
        return new JsonResponse(['results' => $s2js]);
    }


    /**
     * @Route("/fin/movimentacao/clonar/{id}", name="fin_movimentacao_clonar", requirements={"movimentacao"="\d+"})
     * @param Request $request
     * @param Movimentacao|null $movimentacao
     * @return RedirectResponse
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function clonar(Request $request, Movimentacao $movimentacao = null): RedirectResponse
    {
        try {
            /** @var Movimentacao $nova */
            $nova = $this->getEntityHandler()->doClone($movimentacao);
            $this->addFlash('success', 'Movimentação clonada com sucesso');
            return $this->edit($nova);
        } catch (ViewException $e) {
            $this->addFlash('error', 'Ocorreu um erro ao clonar a movimentação');
            return $this->edit($movimentacao);
        }
    }


    /**
     *
     * @Route("/fin/movimentacao/getTiposLanctos", name="movimentacao_getTiposLanctos")
     * @return JsonResponse
     *
     * @throws \Exception
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function getTiposLanctos(): JsonResponse
    {
        $tiposLanctos = $this->getDoctrine()->getRepository(TipoLancto::class)->findAll(['descricao' => 'ASC']);

        $select2js = Select2JsUtils::toSelect2DataFn($tiposLanctos, function ($e) {
            /** @var TipoLancto $e */
            return $e->getDescricaoMontada();
        });
        return new JsonResponse(
            ['results' => $select2js]
        );
    }



    // ------------------------------------ vue


    /**
     * @Route("/fin/movimentacao/aPagarReceber/form", name="fin_banco_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Movimentacao/form_aPagarReceber'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    
    /**
     *
     * @Route("/api/fin/movimentacao/filiais/", name="api_fin_movimentacao_filiais")
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function getFiliais(): Response
    {
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
            $filiaisR = json_decode($repoAppConfig->findConfigByChaveAndAppNome('financeiro.filiais_prop.json', 'crosierapp-radx')->getValor(), true);
            if (!$filiaisR) {
                throw new \RuntimeException();
            }
            $filiais = [];
            foreach ($filiaisR as $documento => $nome) {
                $str = StringUtils::mascararCnpjCpf($documento) . ' - ' . $nome;
                $filiais[] = [
                    'name' => $str,
                    'value' => $str
                ];
            }
            

            return new JsonResponse(
                [
                    'RESULT' => 'OK',
                    'MSG' => 'Executado com sucesso',
                    'DATA' => $filiais
                ]
            );
        } catch (ViewException $e) {
            return new JsonResponse(
                [
                    'RESULT' => 'ERR',
                    'MSG' => 'Erro - getFiliais',
                ]
            );
        }
    }

    
    /**
     *
     * @Route("/api/fin/movimentacao/findSacadoOuCedente/", name="api_fin_movimentacao_findSacadoOuCedente")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function getSacadoOuCedente(Request $request): Response
    {
        try {
            $term = $request->get('term');
            /** @var MovimentacaoRepository $repoMovimentacao */
            $repoMovimentacao = $this->getDoctrine()->getRepository(Movimentacao::class);

            $rs = $repoMovimentacao->findSacadoOuCedente($term);
            $fn = function ($e) {
                return StringUtils::mascararCnpjCpf($e['documento'] ?: 99999999999999) . ' - ' . $e['nome'];
            };
            $s2js = Select2JsUtils::toSelect2DataFn($rs, $fn, [], $fn);

//            foreach ($rs as $r) {
//                $s2js[] = StringUtils::mascararCnpjCpf($r['documento'] ?: 99999999999999) . ' - ' . $r['nome'];
//            }
            return new JsonResponse(
                [
                    'RESULT' => 'OK',
                    'MSG' => 'Executado com sucesso',
                    'DATA' => $s2js
                ]
            );
        } catch (ViewException $e) {
            return new JsonResponse(
                [
                    'RESULT' => 'ERR',
                    'MSG' => 'Erro - getFiliais',
                ]
            );
        }

    }


}
