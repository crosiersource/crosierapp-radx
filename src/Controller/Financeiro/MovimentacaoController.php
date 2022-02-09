<?php

namespace App\Controller\Financeiro;

use App\Business\Financeiro\MovimentacaoImporter;
use App\Form\Financeiro\MovimentacaoAlterarEmLoteType;
use App\Form\Financeiro\MovimentacaoAPagarReceberType;
use App\Form\Financeiro\MovimentacaoChequeProprioType;
use App\Form\Financeiro\MovimentacaoGeralType;
use App\Form\Financeiro\MovimentacaoPagtoType;
use App\Form\Financeiro\MovimentacaoTransferenciaEntreCarteirasType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Cadeia;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
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
            $filiaisR = json_decode($repoAppConfig->findConfigByChaveAndAppNome('financeiro.filiais_prop.json', 'crosierapp-radx')->valor, true);
            if (!$filiaisR) {
                throw new \RuntimeException();
            }
            $filiais = [];
            foreach ($filiaisR as $documento => $nome) {
                $str = StringUtils::mascararCnpjCpf($documento) . ' - ' . $nome;
                $filiais[] = [
                    'label' => $str,
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


    /**
     *
     * @Route("/fin/movimentacao/recorrente/processar", name="fin_movimentacao_recorrente_processar")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function processarRecorrentes(Request $request): JsonResponse
    {
        try {
            $movsSelecionadas = json_decode($request->getContent(), true);
            $rMovs = [];
            foreach ($movsSelecionadas as $mov) {
                $rMovs[] = $this->getDoctrine()->getRepository(Movimentacao::class)->find($mov['id']);
            }
            $msgs = $this->business->processarRecorrentes($rMovs);
            $sMsgs = explode("\r\n", $msgs);
            return CrosierApiResponse::success(['msgs' => $sMsgs]);
        } catch (\Exception $e) {
            return CrosierApiResponse::error($e, true);
        }
    }


    /**
     *
     * @Route("/api/fin/movimentacao/extrato/saldos/{carteira}/{dt}", name="api_fin_movimentacao_extrato_saldos")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function saldos(Carteira $carteira, \DateTime $dt): JsonResponse
    {
        try {
            $saldos = $this->business->calcularSaldos($dt, $carteira);
            return CrosierApiResponse::success($saldos);
        } catch (\Exception $e) {
            return CrosierApiResponse::error($e);
        }
    }


    /**
     *
     * @Route("/fin/movimentacao/aPagarReceber/fichaMovimentacao", name="fin_movimentacao_aPagarReceber_fichaMovimentacao")
     *
     * @param Request $request
     * @return void
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function fichaMovimentacaoPDF(Request $request): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $movsSel = json_decode(json_decode($request->getContent(), true)['movsSelecionadas'], true);

        $movs = [];
        foreach ($movsSel as $mov) {
            $movs[] = $this->getDoctrine()->getRepository(Movimentacao::class)->find($mov['id']);
        }

        gc_collect_cycles();
        gc_disable();

        $html = $this->renderView('Financeiro/fichaMovimentacao.html.twig', ['movs' => $movs]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        gc_collect_cycles();
        gc_enable();

        return new Response(
            base64_encode($dompdf->output())
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

        gc_collect_cycles();
        gc_enable();


    }



    /**
     *
     * @Route("/api/fin/movimentacao/importar", name="api_fin_movimentacao_importar")
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function importar(Request $request, MovimentacaoImporter $importer): JsonResponse
    {
        try {
            $content = json_decode($request->getContent(), true);

            $tipoImportacao = $content['tipoImportacao'];
            
            if ($tipoImportacao === 'EXTRATO_SIMPLES') {
                $repoCarteira = $this->getDoctrine()->getRepository(Carteira::class);
                $carteira = $repoCarteira->find($content['carteira']);
            }
            $r = $importer->importar($tipoImportacao, $content['linhasImportacao'], $carteira);
            // $r = json_decode('{"LINHAS_RESULT":"<<< LINHAS IMPORTADAS >>>\n01\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t908,58\n01\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t132,35\n01\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-175,01\n01\/12\/21\tPIX ENVIADO - Cp :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI\t-865,92\n02\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t850,26\n02\/12\/21\tPIX ENVIADO - Cp :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI\t-675,24\n02\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-175,02\n02\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-ANTECIPACAO\t54,59\n02\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-54,59\n03\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t42,25\n03\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t26,47","movs":[{"UUID":"72a5aa4d-9ffe-4403-9936-23b5a7ad117d","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-CARTAO DE DEBITO","dtMoviment":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"908.58","descontos":"-0","acrescimos":"0","valorTotal":"908.58","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"01\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t908,58"}},{"UUID":"ec261e47-9c39-4cbe-9ccb-90ac456cb4e8","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-CARTAO DE DEBITO","dtMoviment":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"132.35","descontos":"-0","acrescimos":"0","valorTotal":"132.35","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"01\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t132,35"}},{"UUID":"ba634b1b-d704-40cd-b921-890d1cdb6abd","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":6,"descricao":"BOLETO\/GUIA\/DDA","modoDeTransfPropria":false,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":false,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":20,"descricao":"Conta a Pagar\/Receber","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":null,"categoria":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"PIX ENVIADO - CP :18236120-CARLOS EDUARDO PAULUK","dtMoviment":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-01 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-01 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"175.01","descontos":"-0","acrescimos":"0","valorTotal":"175.01","cadeia":null,"parcelamento":false,"cadeiaOrdem":null,"cadeiaQtde":null,"obs":"","jsonData":{"importacao_linha":"01\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-175,01"}},{"UUID":"e2f318a9-b57e-43b5-aea3-5676f10f595f","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":6,"descricao":"BANCO DO BRASIL 3233-6 3303-0","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":true,"banco":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"agencia":"3233-6","conta":"3303-0","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"categoria":{"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA PARA CONTA","descricaoPadraoMoviment":"","codigo":299,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":2,"codigoOrd":299000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"PIX ENVIADO - CP :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI","dtMoviment":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"865.92","descontos":"-0","acrescimos":"0","valorTotal":"865.92","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":1,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"01\/12\/21\tPIX ENVIADO - Cp :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI\t-865,92"}},{"UUID":"1cada28a-c681-41d6-9663-0f43766ad811","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-CARTAO DE DEBITO","dtMoviment":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"850.26","descontos":"-0","acrescimos":"0","valorTotal":"850.26","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"02\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t850,26"}},{"UUID":"19264811-e2c3-49ad-b706-0c9f0ad1b136","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":6,"descricao":"BANCO DO BRASIL 3233-6 3303-0","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":true,"banco":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"agencia":"3233-6","conta":"3303-0","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"categoria":{"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA PARA CONTA","descricaoPadraoMoviment":"","codigo":299,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":2,"codigoOrd":299000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"PIX ENVIADO - CP :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI","dtMoviment":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"675.24","descontos":"-0","acrescimos":"0","valorTotal":"675.24","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":1,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"02\/12\/21\tPIX ENVIADO - Cp :00000000-COMERCIAL DE TECIDOS PAULUK - EIRELI\t-675,24"}},{"UUID":"8466025b-03e7-4fdd-b4db-253065239bb9","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":6,"descricao":"BOLETO\/GUIA\/DDA","modoDeTransfPropria":false,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":false,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":20,"descricao":"Conta a Pagar\/Receber","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":null,"categoria":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"PIX ENVIADO - CP :18236120-CARLOS EDUARDO PAULUK","dtMoviment":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-02 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-02 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"175.02","descontos":"-0","acrescimos":"0","valorTotal":"175.02","cadeia":null,"parcelamento":false,"cadeiaOrdem":null,"cadeiaQtde":null,"obs":"","jsonData":{"importacao_linha":"02\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-175,02"}},{"UUID":"6e657b78-439a-43d9-a7a2-f37e56e777f8","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-ANTECIPACAO","dtMoviment":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"54.59","descontos":"-0","acrescimos":"0","valorTotal":"54.59","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"02\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-ANTECIPACAO\t54,59"}},{"UUID":"1ca9e248-85cb-4fac-80bf-6c3799f12c11","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":6,"descricao":"BOLETO\/GUIA\/DDA","modoDeTransfPropria":false,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":false,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":20,"descricao":"Conta a Pagar\/Receber","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":null,"categoria":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"PIX ENVIADO - CP :18236120-CARLOS EDUARDO PAULUK","dtMoviment":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-02 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-02 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-02 12:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"54.59","descontos":"-0","acrescimos":"0","valorTotal":"54.59","cadeia":null,"parcelamento":false,"cadeiaOrdem":null,"cadeiaQtde":null,"obs":"","jsonData":{"importacao_linha":"02\/12\/21\tPIX ENVIADO - Cp :18236120-Carlos Eduardo Pauluk\t-54,59"}},{"UUID":"76520866-08ca-4c5f-ab33-3b52d39f5422","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-CARTAO DE DEBITO","dtMoviment":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"42.25","descontos":"-0","acrescimos":"0","valorTotal":"42.25","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"03\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t42,25"}},{"UUID":"3f000214-a9be-4772-8d92-e16544d96f59","fatura":null,"faturaOrdem":null,"modo":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":7,"descricao":"PIX\/TRANSF. BANC\u00c1RIA","modoDeTransfPropria":true,"modoDeMovimentAgrup":false,"modoDeCartao":false,"modoDeCheque":false,"modoDeTransfCaixa":true,"modoComBancoOrigem":true},"documentoBanco":null,"documentoNum":"","sacado":"","cedente":"","quitado":true,"tipoLancto":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":60,"descricao":"Transfer\u00eancia entre Carteiras","descricaoMontada":null},"carteira":{"codigo":11,"descricao":"BANCO INTER (IP\u00ca UNIFORMES)","dtConsolidado":{"date":"2020-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"0001","conta":"74308475","limite":0,"operadoraCartao":null,"atual":true,"jsonData":null},"carteiraDestino":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"codigo":37,"descricao":"STONE IP\u00ca","dtConsolidado":{"date":"1900-01-01 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"concreta":true,"abertas":true,"caixa":false,"caixaStatus":null,"caixaResponsavel":null,"cheque":false,"banco":null,"agencia":"","conta":"","limite":null,"operadoraCartao":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"atual":true,"jsonData":null},"categoria":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"pai":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"subCategs":{},"descricao":"TRANSFER\u00caNCIA DE CONTA","descricaoPadraoMoviment":"","codigo":199,"totalizavel":false,"centroCustoDif":false,"rolesAcess":"","descricaoAlternativa":"","codigoSuper":1,"codigoOrd":199000000000},"centroCusto":{"__initializer__":{},"__cloner__":{},"__isInitialized__":false},"grupoItem":null,"status":"REALIZADA","descricao":"CREDITO DOMICILIO CARTAO - STONE-CARTAO DE DEBITO","dtMoviment":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVencto":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtVenctoEfetiva":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtPagto":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"dtUtil":{"date":"2021-12-03 00:00:00.000000","timezone_type":3,"timezone":"America\/Fortaleza"},"chequeBanco":null,"chequeAgencia":null,"chequeConta":null,"chequeNumCheque":null,"operadoraCartao":null,"bandeiraCartao":null,"qtdeParcelasCartao":null,"idTransacaoCartao":"","numCartao":"","recorrente":false,"recorrFrequencia":"","recorrTipoRepet":"","recorrDia":null,"recorrVariacao":null,"valor":"26.47","descontos":"-0","acrescimos":"0","valorTotal":"26.47","cadeia":{"__initializer__":null,"__cloner__":null,"__isInitialized__":true,"vinculante":true,"fechada":true,"movimentacoes":{}},"parcelamento":false,"cadeiaOrdem":2,"cadeiaQtde":2,"obs":"","jsonData":{"importacao_linha":"03\/12\/21\tCREDITO DOMICILIO CARTAO - Stone-CARTAO DE DEBITO\t26,47"}}],"err":null,"menorData":"2021-12-01","maiorData":"2021-12-03","RESULT":"OK"}', true);
            return CrosierApiResponse::success($r);
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


}
