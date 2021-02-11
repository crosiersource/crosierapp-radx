<?php

namespace App\Controller\Financeiro;

use App\Form\Financeiro\MovimentacaoAlterarEmLoteType;
use App\Form\Financeiro\MovimentacaoGeralType;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoImporter;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * MovimentacaoImportController.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoImportController extends BaseController
{

    private MovimentacaoBusiness $business;

    private MovimentacaoImporter $movimentacaoImporter;

    private MovimentacaoEntityHandler $entityHandler;

    private SessionInterface $session;

    private EntityIdUtils $entityIdUtils;

    private array $vParams = array();

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
     * @param EntityIdUtils $entityIdUtils
     */
    public function setEntityIdUtils(EntityIdUtils $entityIdUtils): void
    {
        $this->entityIdUtils = $entityIdUtils;
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
     * @param MovimentacaoImporter $movimentacaoImporter
     */
    public function setMovimentacaoImporter(MovimentacaoImporter $movimentacaoImporter): void
    {
        $this->movimentacaoImporter = $movimentacaoImporter;
    }

    /**
     * @required
     * @param MovimentacaoEntityHandler $entityHandler
     */
    public function setEntityHandler(MovimentacaoEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }


    public function setDefaults(): void
    {
        $this->vParams['tipoExtrato'] = 'EXTRATO_SIMPLES';
        $this->vParams['carteiraExtrato'] = null;
        $this->vParams['carteiraExtratoEntity'] = null;
        $this->vParams['carteiraDestino'] = null;
        $this->vParams['carteiraDestinoEntity'] = null;
        $this->vParams['grupo'] = null;
        $this->vParams['grupoEntity'] = null;
        $this->vParams['grupoItem'] = null;
        $this->vParams['grupoItemEntity'] = null;
        $this->vParams['gerarSemRegras'] = true;
        $this->vParams['usarCabecalho'] = null;
        $this->vParams['gerarAConferir'] = true;
    }

    /**
     * Lida com os vParams, sobrepondo na seguinte ordem: defaults > session > request.
     * @param Request $request
     * @throws ViewException
     */
    public function handleVParams(Request $request): void
    {
        $this->setDefaults();

        $sviParams = $this->storedViewInfoBusiness->retrieve('movimentacao_import');

        if ($sviParams) {
            $this->vParams = array_merge($this->vParams, $sviParams);
        }
        if (is_array($request->request->all())) {
            $this->vParams = array_merge($this->vParams, $request->request->all());

            if ($this->vParams['carteiraExtrato'] ?? false) {
                // já pesquisa e armazena a entidade para ser usada tanto no importar quanto no verificar
                $this->vParams['carteiraExtratoEntity'] = $this->getDoctrine()->getRepository(Carteira::class)->find($this->vParams['carteiraExtrato']);
            }

            if ($this->vParams['carteiraDestino'] ?? false) {
                $this->vParams['carteiraDestinoEntity'] = $this->getDoctrine()->getRepository(Carteira::class)->find($this->vParams['carteiraDestino']);
            }

            if ($this->vParams['grupoItem'] ?? false) {
                $grupoItem = $this->getDoctrine()->getRepository(GrupoItem::class)->find($this->vParams['grupoItem']);
                $this->vParams['grupoItemEntity'] = $grupoItem;
                $this->vParams['grupo'] = $grupoItem->getPai()->getId();
                $this->vParams['grupoEntity'] = $grupoItem->getPai();
            }

            if (strpos($this->vParams['tipoExtrato'], 'DEBITO') !== FALSE) {
                if (!$this->vParams['carteiraExtratoEntity'] || !$this->vParams['carteiraDestinoEntity']) {
                    throw new ViewException("Para extratos do tipo 'DÉBITO' é necessário informar as carteiras de origem e destino");
                }
            } else if ((strpos($this->vParams['tipoExtrato'], 'GRUPO') !== FALSE) && !$this->vParams['grupoItemEntity']) {
                throw new ViewException("Para extratos do tipo 'GRUPO' é necessário informar o grupo");
            }
        }
//        if (strpos($this->vParams['tipoExtrato'], 'DÉBITO') === FALSE) {
//            $this->vParams['carteiraDestino'] = null;
//            $this->vParams['carteiraDestinoEntity'] = null;
//        }
        if (isset($this->vParams['carteiraDestinoEntity']) && !($this->vParams['carteiraDestinoEntity'] instanceof Carteira)) {
            $this->vParams['carteiraDestinoEntity'] = null;
        }
        if (isset($this->vParams['carteiraExtratoEntity']) && !($this->vParams['carteiraExtratoEntity'] instanceof Carteira)) {
            $this->vParams['carteiraExtratoEntity'] = null;
        }

        $storedVParams = $this->vParams;
        $storedVParams['linhasExtrato'] = null;

        $this->storedViewInfoBusiness->store('movimentacao_import', $storedVParams);
    }

    private function getMovimentacoesDaSessao(string $tipo): array
    {
        $arq = $_SERVER['CROSIER_SESSIONS_FOLDER'] . '/MovimentacaoImporter_'. $this->getUser()->getUsername() . '.cache';
        $movsImportadas = json_decode(file_get_contents($arq), true);
        // $movsImportadas = $this->session->get($tipo);
        $unsMovsImportadas = [];
        if ($movsImportadas) {
            foreach ($movsImportadas as $mov) {
                $unsMovsImportadas[$mov['UUID']] = $this->entityIdUtils->unserialize($mov, Movimentacao::class);
            }
        }
        return $unsMovsImportadas;
    }

    private function setMovimentacoesNaSessao(string $tipo, $movimentacoes)
    {
        $serMovimentacoes = null;
        if ($movimentacoes) {
            $serMovimentacoes = [];
            foreach ($movimentacoes as $mov) {
                if ($mov instanceof Movimentacao) {
                    $mov = $this->entityIdUtils->serialize($mov);
                }
                $serMovimentacoes[] = $mov;
            }
        }
        $arq = $_SERVER['CROSIER_SESSIONS_FOLDER'] . 'MovimentacaoImporter_'. $this->getUser()->getUsername() . '.cache';
        file_put_contents($arq, json_encode($serMovimentacoes));
        // $this->session->set($tipo, $serMovimentacoes);
    }

    /**
     *
     * @Route("/fin/movimentacao/import", name="movimentacao_import")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function import(Request $request)
    {
        $this->handleVParams($request);

        try {
            if ($request->request->get('btnImportar')) {
                $this->importar();
            }

            if ($request->request->get('btnVerificar')) {
                $this->movimentacaoImporter->verificarImportadasAMais($this->getMovimentacoesDaSessao('importadas'),
                    $this->vParams['tipoExtrato'],
                    $this->vParams['carteiraExtratoEntity'],
                    $this->vParams['carteiraDestinoEntity'],
                    $this->vParams['grupoItemEntity']);
            }

            if ($request->request->get('btnSalvarTodas')) {
                if (!$this->salvarTodas()) {
                    $this->importar();
                } else {
                    $this->session->set('linhasExtrato', 'null');
                }

            }

            if ($request->request->get('btnLimpar')) {
                $this->limpar();
            }
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao processar requisição.');
        }


        $this->vParams['page_title'] = 'Importação de Movimentações';

        $this->vParams['movsImportadas'] = $this->getMovimentacoesDaSessao('importadas');
        $this->vParams['total'] = $this->session->get('total');
        $this->vParams['linhasExtrato'] = $this->session->get('linhasExtrato');

        return $this->doRender('Financeiro/movimentacaoImport.html.twig', $this->vParams);
    }

    /**
     * @throws ViewException
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function limpar(): void
    {
        $this->storedViewInfoBusiness->clear('movimentacao_import');
        $this->setDefaults();
    }

    /**
     * Importa as movimentações e seta o resultado na session.
     *
     * @throws ViewException
     */
    private function importar(): void
    {
        $r = $this->movimentacaoImporter->importar(
            $this->vParams['tipoExtrato'],
            $this->vParams['linhasExtrato'],
            $this->vParams['carteiraExtratoEntity'],
            $this->vParams['carteiraDestinoEntity'],
            $this->vParams['grupoItemEntity'],
            $this->vParams['gerarSemRegras'],
            $this->vParams['usarCabecalho'],
            $this->vParams['gerarAConferir']);

        $movsImportadas = $r['movs'];

        $sessionMovs = [];
        $unqs = [];
        /** @var Movimentacao $mov */
        foreach ($movsImportadas as $mov) {
            if (!$mov->UUID) {
                throw new ViewException('Movimentação sem UUID: ' . $mov->descricao);
            }
            if (in_array($mov->UUID, $unqs, true)) {
                throw new ViewException('Movimentação duplicada na sessão: ' . $mov->descricao);
            }
            $unqs[] = $mov->UUID;

            $sessionMovs[$mov->UUID] = $mov;
        }

        if (isset($r['err'])) {
            foreach ($r['err'] as $err) {
                // FIXME: exibir também a linha (mas daí talvez não num flash)
                $this->addFlash('error', $err['errMsg']);
            }
        }

        $this->session->set('linhasExtrato', $r['LINHAS_RESULT']);
        $this->session->set('total', $this->business->somarMovimentacoes($r['movs']));
        $this->setMovimentacoesNaSessao('importadas', $sessionMovs);
    }


    /**
     * Salva todas as movimentações.
     */
    private function salvarTodas(): bool
    {
        try {
            $movsImportadas = $this->getMovimentacoesDaSessao('importadas');
            if ($movsImportadas) {
                $this->entityHandler->saveAll($movsImportadas);
                $this->addFlash('success', 'Movimentações salvas com sucesso!');
                $this->setMovimentacoesNaSessao('importadas', null);
                return true;
            }
            $this->addFlash('warn', 'Nenhuma movimentação a salvar');
        } catch (ViewException | \Throwable $e) {
            $this->getLogger()->error('Erro ao salvarTodas()');
            $this->addFlash('error', 'Erro ao processar requisição');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
            $this->getLogger()->error($e->getMessage());
        }

        return false;
    }

    /**
     *
     * @Route("/fin/movimentacao/import/tiposExtratos", name="movimentacao_import_tiposExtratos")
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function tiposExtratos(): Response
    {
        // FIXME: isto deveria estar em uma tabela.
        $tiposExtratos = [
            ['id' => 'EXTRATO_SIMPLES', 'text' => 'EXTRATO SIMPLES'],
            ['id' => 'EXTRATO_GRUPO_MOVIMENTACOES', 'text' => 'EXTRATO GRUPO DE MOVIMENTAÇÕES'],
            ['id' => 'EXTRATO_COMPRA_BNDES_BB', 'text' => 'EXTRATO COMPRA BNDES BB'],
            ['id' => 'EXTRATO_RDCARD_CREDITO', 'text' => 'EXTRATO RDCARD - CRÉDITO'],
            ['id' => 'EXTRATO_RDCARD_DEBITO', 'text' => 'EXTRATO RDCARD - DÉBITO'],
            ['id' => 'EXTRATO_MODERNINHA_DEBITO', 'text' => 'EXTRATO MODERNINHA - DÉBITO'],
            ['id' => 'EXTRATO_CIELO_CREDITO', 'text' => 'EXTRATO CIELO - CRÉDITO'],
            ['id' => 'EXTRATO_CIELO_DEBITO', 'text' => 'EXTRATO CIELO - DÉBITO'],
            ['id' => 'EXTRATO_CIELO_CREDITO_NOVO', 'text' => 'EXTRATO CIELO - CRÉDITO (NOVO)'],
            ['id' => 'EXTRATO_CIELO_DEBITO_NOVO', 'text' => 'EXTRATO CIELO - DÉBITO (NOVO)'],
            ['id' => 'EXTRATO_STONE_CREDITO', 'text' => 'EXTRATO STONE - CRÉDITO'],
            ['id' => 'EXTRATO_STONE_DEBITO', 'text' => 'EXTRATO STONE - DÉBITO']
        ];

//        $results = array('results' => $tiposExtratos);

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($tiposExtratos, 'json');

        return new Response($json);

    }

    /**
     *
     * @Route("/fin/movimentacao/import/form/{UUID}", name="movimentacao_import_form")
     * @param Request $request
     * @param $UUID
     * @return RedirectResponse|Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function form(Request $request, $UUID)
    {
        if (!$UUID) {
            throw new ViewException('UUID não informado');
        }

        $sessionMovs = $this->getMovimentacoesDaSessao('importadas');

        $movimentacao = $sessionMovs[$UUID];

        // Dá um merge nos atributos manyToOne pra não dar erro no createForm
        if ($movimentacao) {
            $this->entityHandler->refindAll($movimentacao);
        }

        $formData = null;
        $form = $this->createForm(MovimentacaoGeralType::class, $movimentacao);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $movimentacao = $form->getData();
                $sessionMovs[$UUID] = $movimentacao;
                $this->setMovimentacoesNaSessao('importadas', $sessionMovs);
                $this->addFlash('success', 'Registro salvo com sucesso!');
                return $this->redirectToRoute('movimentacao_import');
            }
            $form->getErrors(true, false);
        }

        // Pode ou não ter vindo algo no $parameters. Independentemente disto, só adiciono form e foi-se.
        $parameters['form'] = $form->createView();

        return $this->doRender('Financeiro/movimentacaoImportForm.html.twig', $parameters);
    }

    /**
     *
     * @Route("/fin/movimentacao/import/remove/{UUID}", name="movimentacao_import_remove")
     * @param $UUID
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function remove($UUID)
    {
        if (!$UUID) {
            throw new ViewException('UUID não informado');
        }
        $sessionMovs = $this->getMovimentacoesDaSessao('importadas');
        unset($sessionMovs[$UUID]);
        $this->setMovimentacoesNaSessao('importadas', $sessionMovs);
        return $this->redirectToRoute('movimentacao_import');
    }


    /**
     *
     * @Route("/fin/movimentacao/import/alterarLote/", name="movimentacao_import_alterarLote")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function alterarLote(Request $request)
    {
        if ($request->get('btnAlterarEmLote')) {
            if (!$request->get('movsSelecionadas')) {
                $this->addFlash('warn', 'Nenhuma movimentação selecionada.');
                return $this->redirectToRoute('movimentacao_list');
            }
            $movsSel = $request->get('movsSelecionadas');
            $this->setMovimentacoesNaSessao('selecionadas', $movsSel);
        }

        $form = $this->createForm(MovimentacaoAlterarEmLoteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $lote = [];
                $movsSel = $this->getMovimentacoesDaSessao('selecionadas');
                $movsImportadas = $this->getMovimentacoesDaSessao('importadas');
                foreach ($movsSel as $uuid => $on) {
                    $lote[$uuid] = $movsImportadas[$uuid];
                }

                $movimentacao = $form->getData();
                $this->business->alterarEmLote($lote, $movimentacao);

                foreach ($lote as $uuid => $mov) {
                    $movsImportadas[$uuid] = $mov;
                }
                $this->setMovimentacoesNaSessao('importadas', $movsImportadas);

                $this->addFlash('success', 'Movimentações alteradas com sucesso.');
                return $this->redirectToRoute('movimentacao_import');
            }
            $form->getErrors(true, false);
        }

        // Pode ou não ter vindo algo no $parameters. Independentemente disto, só adiciono form e foi-se.
        $parameters['form'] = $form->createView();

        return $this->doRender('Financeiro/movimentacaoAlterarEmLoteForm.html.twig', $parameters);
    }


    /**
     *
     * @Route("/fin/movimentacao/import/removerExistentes", name="movimentacao_import_removerExistentes")
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function removerExistentes(): RedirectResponse
    {
        $movsImportadas = $this->getMovimentacoesDaSessao('importadas');
        $nMovsImportadas = [];
        /** @var Movimentacao $movImportada */
        foreach ($movsImportadas as $movImportada) {
            if (!$movImportada->getId()) {
                $nMovsImportadas[] = $movImportada;
            }
        }
        $this->setMovimentacoesNaSessao('importadas', $nMovsImportadas);

        return $this->redirectToRoute('movimentacao_import');

    }


}
