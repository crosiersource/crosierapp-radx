<?php

namespace App\Business\Financeiro;

use App\Entity\Financeiro\BandeiraCartao;
use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\CentroCusto;
use App\Entity\Financeiro\GrupoItem;
use App\Entity\Financeiro\ImportExtratoCabec;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\OperadoraCartao;
use App\Entity\Financeiro\RegraImportacaoLinha;
use App\Entity\Financeiro\TipoLancto;
use App\Repository\Financeiro\BandeiraCartaoRepository;
use App\Repository\Financeiro\CategoriaRepository;
use App\Repository\Financeiro\CentroCustoRepository;
use App\Repository\Financeiro\ModoRepository;
use App\Repository\Financeiro\MovimentacaoRepository;
use App\Repository\Financeiro\RegraImportacaoLinhaRepository;
use App\Repository\Financeiro\TipoLanctoRepository;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\ORM\EntityManagerInterface;

const TXT_LINHA_NAO_IMPORTADA = '<<< LINHAS NÃO IMPORTADAS >>>';

const TXT_LINHA_IMPORTADA = '<<< LINHAS IMPORTADAS >>>';

/**
 * Classe responsável pelas regras de negócio de importação de extratos.
 *
 * @package App\Business\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoImporter
{

    private $doctrine;

    private $linhas;

    /**
     * Armazena linhas de descrição complementar para verificações durante o processo.
     *
     * @var array
     */
    private $linhasComplementares = array();

    /**
     * Armazena as movimentações de categoria 1.01 que já foram importadas.
     *
     * @var array
     */
    private $movs101JaImportadas = array();

    /**
     * Para armazenar as movimentações já importadas afim de que não sejam importadas 2x por duplicidade.
     *
     * @var array
     */
    private $movsJaImportadas = array();

    private $linhasExtrato;

    private $tipoExtrato;

    private $carteiraExtrato;

    private $carteiraDestino;

    private $grupoItem;

    private $gerarSemRegras;

    private $gerarAConferir;

    private $identificarPorCabecalho;

    private $arrayCabecalho;

    /** @var ModoRepository */
    private $repoModo;

    /** @var MovimentacaoRepository */
    private $repoMovimentacao;

    /** @var CategoriaRepository */
    private $repoCategoria;

    /** @var TipoLanctoRepository */
    private $repoTipoLancto;

    /** @var BandeiraCartaoRepository */
    private $repoBandeiraCartao;

    /** @var CentroCustoRepository */
    private $repoCentroCusto;


    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->repoModo = $this->doctrine->getRepository(Modo::class);
        $this->repoMovimentacao = $this->doctrine->getRepository(Movimentacao::class);
        $this->repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $this->repoTipoLancto = $this->doctrine->getRepository(TipoLancto::class);
        $this->repoBandeiraCartao = $this->doctrine->getRepository(BandeiraCartao::class);
        $this->repoCentroCusto = $this->doctrine->getRepository(CentroCusto::class);
    }

    /**
     * @param $tipoExtrato
     * @param $linhasExtrato
     * @param Carteira|null $carteiraExtrato
     * @param Carteira|null $carteiraDestino
     * @param GrupoItem|null $grupoItem
     * @param $gerarSemRegras
     * @param bool $identificarPorCabecalho
     *
     * @return mixed
     *
     * @throws ViewException
     */
    public function importar($tipoExtrato, $linhasExtrato, ?Carteira $carteiraExtrato, ?Carteira $carteiraDestino,
                             ?GrupoItem $grupoItem, $gerarSemRegras, $identificarPorCabecalho = false, $gerarAConferir = true)
    {
        $this->tipoExtrato = $tipoExtrato;
        $this->linhasExtrato = $linhasExtrato;
        $this->carteiraExtrato = $carteiraExtrato;
        $this->carteiraDestino = $carteiraDestino;
        $this->grupoItem = $grupoItem;
        $this->gerarSemRegras = $gerarSemRegras;
        $this->gerarAConferir = $gerarAConferir;
        $this->identificarPorCabecalho = $identificarPorCabecalho;

        if ($identificarPorCabecalho) {
            $this->buildArrayCabecalho();
        }

        if (strpos($tipoExtrato, 'DEBITO') !== FALSE) {
            if (!$carteiraExtrato || !$carteiraExtrato) {
                throw new ViewException('Para extratos de cartões de débito, é necessário informar a carteira de ||igem e de destino.');
            }
        } elseif (strpos($tipoExtrato, 'GRUPO') !== FALSE) {
            if (!$grupoItem) {
                throw new ViewException('Para extratos de grupos de movimentações, é necessário informar o grupo.');
            }
        }

        switch ($tipoExtrato) {
            case 'EXTRATO_GRUPO_MOVIMENTACOES':
                return $this->importGrupoMovimentacao();
            default:
                return $this->importarPadrao();
        }
    }


    /**
     * Constrói o array 'de-para' baseado no cabeçalho.
     *
     * @throws ViewException
     */
    public function buildArrayCabecalho(): void
    {
        $linhas = explode("\n", $this->linhasExtrato);
        $primeira = $linhas[0];
        if (strpos($primeira, '<<< LINHAS NÃO IMPORTADAS >>>') !== FALSE) {
            $primeira = $linhas[1];
        }
        $camposCSV = explode("\t", $primeira);

        $arrayCabecalho = [];

        $camposDePara = $this->doctrine->getRepository(ImportExtratoCabec::class)->findBy(['tipoExtrato' => $this->tipoExtrato]);
        if ($camposDePara) {
            /** @var ImportExtratoCabec $dePara */
            foreach ($camposDePara as $dePara) {
                // Se não está separado por vírgula, é um campo único (1-para-1).
                if (strpos($dePara->getCamposCabecalho(), ',') === FALSE) {
                    $achou = false;
                    foreach ($camposCSV as $key => $campoCSV) {
                        if ($dePara->getCamposCabecalho() === $campoCSV) {
                            $arrayCabecalho[$dePara->getCampoSistema()] = $key;
                            $achou = true;
                            break;
                        }
                    }
                    if (!$achou) {
                        throw new ViewException('Não foi possível montar o array do cabeçalho.');
                    }
                } else {
                    $camposCabecalho = explode(',', $dePara->getCamposCabecalho());
                    foreach ($camposCabecalho as $campoCabecalho) {
                        $achou = false;
                        foreach ($camposCSV as $key => $campoCSV) {
                            if ($campoCabecalho === $campoCSV) {
                                $arrayCabecalho[$dePara->getCampoSistema()]['campos'][] = $key;
                                $achou = true;
                                break;
                            }
                        }
                        if (!$achou) {
                            throw new ViewException('Não foi possível montar o array do cabeçalho.');
                        }
                    }
                    $arrayCabecalho[$dePara->getCampoSistema()]['formato'] = $dePara->getFormato();
                }
            }
        }

        $this->arrayCabecalho = $arrayCabecalho;

    }

    /**
     * @return mixed
     */
    private function importarPadrao()
    {
        $this->movsJaImportadas = [];


        $linhasNaoImportadas = array();
        $linhasImportadas = array();

        $this->linhas = explode("\n", $this->linhasExtrato);

        $r = [];
        $r['LINHAS_RESULT'] = null;
        $r['movs'] = null;
        $r['err'] = null;

        $qtdeLinhas = count($this->linhas);

        for ($i = 0; $i < $qtdeLinhas; $i++) {
            if ($this->identificarPorCabecalho && $this->arrayCabecalho && $i === 0) {
                // pula o cabeçalho
                continue;
            }
            $linha = trim($this->linhas[$i]);

            // Verifica se é uma linha (de descrição) complementar já importada
            if (in_array($i, $this->linhasComplementares, true)) {
                $linhasImportadas[] = $linha;
                continue;
            }

            if (!$linha || trim($linha) === TXT_LINHA_IMPORTADA || trim($linha) === TXT_LINHA_NAO_IMPORTADA) {
                continue;
            }

            if ($this->tipoExtrato === 'EXTRATO_SIMPLES' && !$this->ehLinhaExtratoSimplesOuSaldo($linha)) {
                $linhasNaoImportadas[] = $linha;
                continue;
            }

            try {
                // importa a linha
                $movimentacao = $this->importarLinha($i);
                if ($movimentacao) {
                    $this->movsJaImportadas[] = $movimentacao;
                    $linhasImportadas[] = $linha;
                } else {
                    $linhasNaoImportadas[] = $linha;
                }
            } catch (ViewException $e) {
                $r['err'][] = [
                    'linha' => $linha,
                    'errMsg' => $e->getMessage()
                ];
                $linhasNaoImportadas[] = $linha;
            } catch (\Throwable $e) {
                $r['err'][] = [
                    'linha' => $linha,
                    'errMsg' => 'Erro geral ao processar linha: ' . $linha
                ];
                $linhasNaoImportadas[] = $linha;
            }


        }

        $r['LINHAS_RESULT'] = '';
        if (count($linhasNaoImportadas) > 0) {
            $r['LINHAS_RESULT'] .= TXT_LINHA_NAO_IMPORTADA . "\n" .
                implode("\n", $linhasNaoImportadas) . "\n\n\n\n\n";
        }
        $r['LINHAS_RESULT'] .= TXT_LINHA_IMPORTADA . "\n" .
            implode("\n", $linhasImportadas);

        $r['movs'] = $this->movsJaImportadas;

        return $r;
    }

    /**
     * @param $numLinha
     * @return mixed
     * @throws ViewException
     */
    private function importarLinha($numLinha)
    {
        switch ($this->tipoExtrato) {
            case 'EXTRATO_SIMPLES':
                $camposLinha = $this->importLinhaExtratoSimples($numLinha);
                break;
            case 'EXTRATO_MODERNINHA_DEBITO':
                $camposLinha = $this->importLinhaExtratoModerninhaDebito($numLinha);
                break;
            case 'EXTRATO_CIELO_DEBITO':
                $camposLinha = $this->importLinhaExtratoCieloDebitoNovo($numLinha);
                break;
            case 'EXTRATO_CIELO_CREDITO':
                $camposLinha = $this->importLinhaExtratoCieloCreditoNovo($numLinha);
                break;
            case 'EXTRATO_STONE_DEBITO':
                $camposLinha = $this->importLinhaExtratoStoneDebito($numLinha);
                break;
            case 'EXTRATO_STONE_CREDITO':
                $camposLinha = $this->importLinhaExtratoStoneCredito($numLinha);
                break;
            default:
                throw new ViewException('Tipo de extrato inválido.');
        }

        if ($camposLinha) {
            if (strpos($this->tipoExtrato, 'DEBITO') !== FALSE) {
                return $this->handleLinhaImportadaDebito($camposLinha);
            }

            return strpos($this->tipoExtrato, 'CREDITO') !== FALSE ?
                $this->handleLinhaImportadaCredito($camposLinha) :
                $this->handleLinhaImportadaPadrao($camposLinha);
        }

        return null;
    }

    /**
     * @param $camposLinha
     * @return Carteira|Movimentacao|OperadoraCartao
     * @throws ViewException
     */
    private function handleLinhaImportadaDebito($camposLinha)
    {
        $descricao = $camposLinha['descricao'];
        /** @var \DateTime $dtMoviment */
        $dtMoviment = $camposLinha['dtMoviment'];
        $dtVenctoEfetiva = $camposLinha['dtVenctoEfetiva'];
        $valor = $camposLinha['valor'];
        $valorTotal = $camposLinha['valorTotal'];
        $bandeiraCartao = $camposLinha['bandeiraCartao'];


        /** @var Categoria $categ101 */
        $categ101 = $this->repoCategoria->findOneBy(['codigo' => 101]);
        /** @var Categoria $categ102 */
        $categ102 = $this->repoCategoria->findOneBy(['codigo' => 102]);
        /** @var Categoria $categ299 */
        $categ299 = $this->repoCategoria->findOneBy(['codigo' => 299]);

        /** @var TipoLancto $transfPropria */
        $transfPropria = $this->repoTipoLancto->findOneBy(['codigo' => 60]);


        /** @var Modo $modo */
        $modo = $this->repoModo->find(10); // 'RECEB. CARTÃO DÉBITO';

        // Primeiro tento encontrar a movimentação original do cartão, que é a movimentação de entrada (101) no caixa a vista (anotado na folhinha de fechamento de caixa, lançado manualmente).
        $dtMoviment = $dtMoviment->setTime(0, 0, 0, 0);
        $dtMovimentIni = clone $dtMoviment;
        $dtMovimentFim = (clone $dtMoviment)->add(new \DateInterval('P5D'));
        $movs101Todas = $this->repoMovimentacao
            ->findByFiltersSimpl([
                ['dtMoviment', 'BETWEEN_DATE', [$dtMovimentIni, $dtMovimentFim]],
                ['valorTotal', 'EQ', $valorTotal],
                ['carteira', 'EQ', $this->carteiraDestino],
                ['bandeiraCartao', 'EQ', $bandeiraCartao],
                ['categoria', 'IN', [$categ101, $categ102]]
            ],
                ['dtMoviment' => 'ASC'], 0, -1);


        // Ignora as que já foram importadas (ou melhor, associadas, pois pode ter uma mesma movimentação, com mesmo valor,
        // mesma data, mesma bandeira
        $mov101 = null;
        /** @var Movimentacao $_mov101 */
        foreach ($movs101Todas as $_mov101) {
            if (!in_array($_mov101->getId(), $this->movs101JaImportadas, true)) {
                $mov101 = $_mov101;
                break;
            }
        }
        if ($mov101) {
            $this->movs101JaImportadas[] = $mov101->getId();
        }

        // Se não encontrar, avisa
        if (!$mov101) {
            throw new ViewException('Movimentação (1.01) original não encontrada (' . $descricao . ' - R$ ' . number_format($valorTotal, 2, '.', ','));
        }

        // Aqui as carteiras são invertidas, pois é a 299 (a destino do método é a do extrato, e a destino da importação é a 'origem' no método)
        $movs299Todas = $this->repoMovimentacao
            ->findByFiltersSimpl([
                ['dtMoviment', 'BETWEEN_DATE', [$dtMovimentIni, $dtMovimentFim]],
                ['valorTotal', 'EQ', $valorTotal],
                ['carteira', 'EQ', $this->carteiraDestino],
                ['carteiraDestino', 'EQ', $this->carteiraExtrato],
                ['bandeiraCartao', 'EQ', $bandeiraCartao],
                ['categoria', 'EQ', $categ299]
            ],
                ['dtMoviment' => 'ASC'], 0, -1);


        // Remove as já importadas para resolver o bug de ter duas movimentações de mesma bandeira e mesmo valor no mesmo dia
        $jaTem101Associada = false;
        /** @var Movimentacao $mov299 */
        foreach ($movs299Todas as $mov299) {
            if ($mov299->getCadeia()) {
                foreach ($mov299->getCadeia()->getMovimentacoes() as $movCadeia) {
                    if ($movCadeia->getCategoria()->getCodigo() === 101 && $movCadeia->getId() !== $mov101->getId()) {
                        $jaTem101Associada = true;
                        break;
                    }
                }
            }
            if (!$jaTem101Associada && !$this->checkJaImportada($mov299)) {
                return $mov299;
            }
        }


        // Crio as movimentações 299 (no caixa AV) e 199 (na carteira extrato)

        $mov299 = new Movimentacao();
        // aqui se inverte as carteiras, pois para salvar uma transferência entre carteiras se deve sempre começar pela 299 (ver como funciona o MovimentacaoDataMapperImpl.processSave)
        $mov299->setCarteira($this->carteiraDestino); // vai debitar no 'CAIXA A VISTA'
        $mov299->setCarteiraDestino($this->carteiraExtrato); // vai creditar na carteira do cartão (199)
        $mov299->setCategoria($categ299);
        $mov299->setValor($valor);
        $mov299->setDescontos(0.00);
        $mov299->setValorTotal($valorTotal);
        $mov299->setDescricao($descricao);
        $mov299->setTipoLancto($transfPropria); // para gerar as duas (299+199)
        $mov299->setStatus('REALIZADA');
        $mov299->setModo($modo);
        $mov299->setDtMoviment($mov101->getDtMoviment());
        $mov299->setDtVencto($dtVenctoEfetiva);
        $mov299->setDtVenctoEfetiva($dtVenctoEfetiva); // por questão de informação, a data efetiva em que o cartão pagou o valor fica na dt vencto nossa
        // tenho que deixar a dtPagto como a dtMoviment porque a 299
        // no caixa a vista tem que ser com a mesma data da 101 (que foi lançada através do fechamento de caixa diário).
        // e não posso ter uma 199 com data diferente da 299 correspondente
        $mov299->setDtPagto($mov101->getDtMoviment());

        $mov299->setBandeiraCartao($bandeiraCartao);
        $mov299->setUUID(StringUtils::guidv4());

        /** @var OperadoraCartao $operadoraCartao */
        $operadoraCartao = $this->doctrine->getRepository(OperadoraCartao::class)->findOneBy(['carteira' => $this->carteiraExtrato]);

        $mov299->setOperadoraCartao($operadoraCartao);

        return $mov299;
    }

    /**
     * @param $camposLinha
     * @return Movimentacao|null|object
     * @throws ViewException
     */
    private function handleLinhaImportadaCredito($camposLinha)
    {
        $valor = $camposLinha['valor'];
        $desconto = 0.00;
        $valorTotal = $camposLinha['valor'];
        $valorNegativo = $valor < 0.0;
        $valor = abs($valor);
        $categoriaCodigo = $camposLinha['categoriaCodigo'];
        $descricao = $camposLinha['descricao'];
        $dtMoviment = $camposLinha['dtMoviment'];
        $dtVenctoEfetiva = $camposLinha['dtVenctoEfetiva'];
        $modo = $camposLinha['modo'];
        $planoPagtoCartao = $camposLinha['planoPagtoCartao'];
        $bandeiraCartao = $camposLinha['bandeiraCartao'];
        $numCheque = null;


        $movs = $this->repoMovimentacao
            ->findBy([
                'descricao' => mb_strtoupper($descricao),
                'valor' => $valor,
                'bandeiraCartao' => $bandeiraCartao,
                'modo' => $modo,
                'dtVenctoEfetiva' => $dtVenctoEfetiva
            ]);

        // Se achou alguma movimentação já lançada, pega a primeira
        if ($movs) {

            if (count($movs) > 1) {
                throw new ViewException('Mais de uma movimentação encontrada para "' . $descricao . '"');
            }

            return $movs[0];
        }
        // else
        // se for pra gerar movimentações que não se encaixem nas regras...
        $movimentacao = new Movimentacao();
        $movimentacao->setUUID(StringUtils::guidv4());
        $movimentacao->setCarteira($this->carteiraExtrato);
        $movimentacao->setValor($valor);
        $movimentacao->setDescontos($desconto);
        $movimentacao->setValorTotal($valorTotal);
        $movimentacao->setDescricao($descricao);

        /** @var TipoLancto $realizada */
        $realizada = $this->repoTipoLancto->findOneBy(['codigo' => 20]);
        $movimentacao->setTipoLancto($realizada);

        $movimentacao->setStatus('REALIZADA');
        $movimentacao->setModo($modo);
        $movimentacao->setDtMoviment($dtMoviment);
        $movimentacao->setDtVencto($dtVenctoEfetiva);
        $movimentacao->setDtVenctoEfetiva($dtVenctoEfetiva);
        $movimentacao->setDtPagto($dtVenctoEfetiva);
        $movimentacao->setBandeiraCartao($bandeiraCartao);
        $movimentacao->setPlanoPagtoCartao($planoPagtoCartao);

        /** @var Categoria $categoria */
        $categoria = null;
        if ($categoriaCodigo) {
            $categoria = $this->repoCategoria->findOneBy(['codigo' => $categoriaCodigo]);
        } else {
            if ($valorNegativo) {
                $categoria = $this->repoCategoria->findOneBy(['codigo' => 2]);
            } else {
                $categoria = $this->repoCategoria->findOneBy(['codigo' => 1]);
            }
        }
        $movimentacao->setCategoria($categoria);

        return $movimentacao;
    }

    /**
     * @param $camposLinha
     * @return Movimentacao|null|object
     * @throws ViewException
     */
    private function handleLinhaImportadaPadrao($camposLinha)
    {
        $valor = $camposLinha['valor'];
        $desconto = $camposLinha['desconto'] ?? null;
        $valorTotal = $camposLinha['valorTotal'];
        $categoriaCodigo = $camposLinha['categoriaCodigo'];
        $valorNegativo = $valor < 0.0;
        $valor = abs($valor);
        $descricao = trim($camposLinha['descricao']);

        /** @var Modo $modo */
        $modo = $camposLinha['modo'];

        if ($this->gerarAConferir) {
            if (!$categoriaCodigo) {
                $categoriaCodigo = $valorNegativo ? 295 : 195;
            }
            if (!$modo) {

                if (strpos($descricao, 'TRANSF') !== FALSE || strpos($descricao, 'TED') !== FALSE) {
                    $modo = $this->repoModo->findOneBy(['codigo' => 7]);
                } else if (strpos($descricao, 'DEPÓSITO') !== FALSE || strpos($descricao, 'DEPOSITO') !== FALSE) {
                    $modo = $this->repoModo->findOneBy(['codigo' => 5]);
                } else if (strpos($descricao, 'TÍTULO') !== FALSE || strpos($descricao, 'TITULO') !== FALSE) {
                    $modo = $this->repoModo->findOneBy(['codigo' => 6]);
                } else {
                    $modo = $this->repoModo->findOneBy(['codigo' => 99]);
                }
            }
        }


        $dtMoviment = $camposLinha['dtMoviment'];
        /** @var \DateTime $dtVenctoEfetiva */
        $dtVenctoEfetiva = $camposLinha['dtVenctoEfetiva'];
        // $entradaOuSaida = $camposLinha['entradaOuSaida'];

        $planoPagtoCartao = $camposLinha['planoPagtoCartao'];
        $bandeiraCartao = $camposLinha['bandeiraCartao'];
        $numCheque = null;


        /** @var RegraImportacaoLinhaRepository $repoRegraImportacaoLinha */
        $repoRegraImportacaoLinha = $this->doctrine->getRepository(RegraImportacaoLinha::class);
        $regras = $repoRegraImportacaoLinha->findAllBy($this->carteiraExtrato);

        /** @var RegraImportacaoLinha $regra */
        $regra = null;
        /** @var RegraImportacaoLinha $r */
        foreach ($regras as $r) {
            if ($r->getRegraRegexJava()) {
                if (preg_match('@' . $r->getRegraRegexJava() . '@', $descricao)) {
                    if ($r->getSinalValor() === 0 ||
                        ($r->getSinalValor() === -1 && $valorNegativo) ||
                        ($r->getSinalValor() === 1 && !$valorNegativo)) {
                        $regra = $r;
                        break;
                    }
                }
            }
        }

        if ($regra) {
            preg_match('@' . $regra->getRegraRegexJava() . '@', $descricao, $matches);
            if (isset($matches['NUMCHEQUE'])) {
                $numCheque = (int)preg_replace('[^\\d]', '', $matches['NUMCHEQUE']);
            }
        }


        // Se é uma linha de cheque
        if ($numCheque) {
            if ($valorNegativo) {
                $modo = $valorNegativo ? $this->repoModo->findBy(['codigo' => 3]) : $this->repoModo->findBy(['codigo' => 4]); // CHEQUE PRÓPRIO
            }
            $filterByCheque = [
                ['carteira', 'EQ', $this->carteiraExtrato],
                ['chequeNumCheque', 'LIKE_ONLY', $numCheque]
            ];
            $movsAbertas = $this->repoMovimentacao->findByFiltersSimpl($filterByCheque, null, 0, -1);
        } else {

            // Primeiro tenta encontrar movimentações em aberto de qualquer carteira, com o mesmo valor e dtVencto
            // Depois tenta encontrar movimentações de qualquer status somente da carteira do extrato
            // Junto os dois resultados
            $filtersSimplAbertas = [
                ['dtVenctoEfetiva', 'EQ', $dtVenctoEfetiva->format('Y-m-d')],
                ['valor', 'EQ', $valor],
                ['status', 'EQ', 'ABERTA']
            ];
            $movsAbertas = $this->repoMovimentacao->findByFiltersSimpl($filtersSimplAbertas, null, 0, -1);
        }

        $filtersSimplTodas = [
            ['dtUtil', 'EQ', $dtVenctoEfetiva->format('Y-m-d')],
            ['valorTotal', 'EQ', $valor],
            ['carteira', 'EQ', $this->carteiraExtrato]
        ];

        $movsTodas = $this->repoMovimentacao->findByFiltersSimpl($filtersSimplTodas, null, 0, -1);

        // array para atribuir a união dos outros dois
        $movs = array();
        /** @var Movimentacao $mov */
        foreach ($movsAbertas as $mov) {
            if ((!$this->checkJaImportada($mov)) && !in_array($mov->getId(), $movs, true)) {
                $movs[] = $mov->getId();
            }
        }
        /** @var Movimentacao $mov */
        foreach ($movsTodas as $mov) {
            if ((!$this->checkJaImportada($mov)) && !in_array($mov->getId(), $movs, true)) {
                $movs[] = $mov->getId();
            }
        }


        // Se achou alguma movimentação já lançada, pega a primeira
        if (count($movs) > 0) {
            /** @var Movimentacao $movimentacao */
            $movimentacao = $this->repoMovimentacao->find($movs[0]);
            if (!$movimentacao->getUUID()) {
                $movimentacao->setUUID(StringUtils::guidv4());
            }
            $movimentacao->setDtPagto($dtVenctoEfetiva);
            $movimentacao->setStatus('REALIZADA');
            $movimentacao->setCarteira($this->carteiraExtrato);
            return $movimentacao;
        }
        // else
        if ($regra) {
            $movimentacao = new Movimentacao();

            $movimentacao->setUUID(StringUtils::guidv4());

            $carteiraOrigem = $regra->getCarteira() ? $regra->getCarteira() : $this->carteiraExtrato;
            $carteiraDestino = $regra->getCarteiraDestino() ? $regra->getCarteiraDestino() : $this->carteiraDestino;

            $movimentacao->setCarteira($carteiraOrigem);
            $movimentacao->setCarteiraDestino($carteiraDestino);

            if ($regra->getTipoLancto()->getCodigo() === 60) {
                // Nas transferências entre contas próprias, a regra informa a carteira de ||igem.
                // A de destino, se não for informada na regra, será a do extrato.

                if (!$regra->getCategoria()->getCodigo() === '299') {
                    throw new ViewException('Regras para transferências entre carteiras próprias devem ser apenas com categoria 2.99');
                }

                // Se a regra informar a carteira da 299, prevalesce
                $cart299 = $regra->getCarteira() ?: $this->carteiraExtrato;

                $cart199 = $regra->getCarteiraDestino();
                if ((!$cart199) || $cart199->getCodigo() === '99') {
                    $cart199 = $this->carteiraExtrato;
                }

                $movimentacao->setCarteira($cart299);
                $carteiraDestino = $cart199;
                $movimentacao->setCarteiraDestino($carteiraDestino);
                // se NÃO for regra para TRANSF_PROPRIA
            } else {
                if (in_array($regra->getTipoLancto()->getCodigo(), [40, 41], true)) {

                    $movimentacao = $this->repoMovimentacao
                        ->findOneBy([
                            'valor' => $valorTotal,
                            'carteira' => $this->carteiraExtrato,
                            'chequeNumCheque' => $numCheque
                        ]);


                    if ($movimentacao && $this->checkJaImportada($movimentacao)) {
                        $movimentacao = null;
                    }

                    // Se achou a movimentação deste cheque, só seta a dtPagto
                    if ($movimentacao) {
                        $movimentacao->setDtPagto($dtVenctoEfetiva);
                        return $movimentacao;
                    }
                    // else
                    $movimentacao = new Movimentacao();
                    $movimentacao->setUUID(StringUtils::guidv4());
                    $movimentacao->setChequeNumCheque($numCheque);
                    /** @var Carteira $carteira */
                    $carteira = $regra->getCarteira() ?: $carteiraOrigem;
                    $movimentacao->setCarteira($carteira);
                    $movimentacao->setChequeBanco($carteira->getBanco());
                    $movimentacao->setChequeAgencia($carteira->getAgencia());
                    $movimentacao->setChequeConta($carteira->getConta());

                } else if (in_array($regra->getTipoLancto()->getCodigo(), [42, 43], true)) {
                    $movimentacao->setChequeNumCheque($numCheque);

                    if ($regra->getChequeConta()) {
                        $movimentacao->setChequeAgencia($regra->getChequeAgencia());
                        $movimentacao->setChequeConta($regra->getChequeConta());
                        $movimentacao->setChequeBanco($regra->getChequeBanco());
                    } else {
                        $movimentacao->setChequeAgencia('9999');
                        $movimentacao->setChequeConta('99999-9');
                        $movimentacao->setChequeBanco(null);
                    }
                }
            }

            $movimentacao->setTipoLancto($regra->getTipoLancto());


            if ($movimentacao->getTipoLancto()->getCodigo() === 60) {
                $movimentacao->setCarteiraDestino($carteiraDestino);
            }

            $movimentacao->setDescricao($descricao);

            $movimentacao->setCategoria($regra->getCategoria());
            $movimentacao->setCentroCusto($regra->getCentroCusto());

            $movimentacao->setDtMoviment($dtVenctoEfetiva);
            $movimentacao->setDtVencto($dtVenctoEfetiva);

            $movimentacao->setStatus($regra->getStatus());

            $movimentacao->setModo($regra->getModo());
            $movimentacao->setValor($valor);
            $movimentacao->setValorTotal($valor);

            if ($regra->getStatus() === 'REALIZADA') {
                $movimentacao->setDtPagto($dtVenctoEfetiva);
            }

            $movimentacao->setPlanoPagtoCartao($planoPagtoCartao);

            return $movimentacao;
        }
        // else
        if ($this->gerarSemRegras) {
            // se for pra gerar movimentações que não se encaixem nas regras...
            $movimentacao = new Movimentacao();
            $movimentacao->setUUID(StringUtils::guidv4());
            $movimentacao->setCarteira($this->carteiraExtrato);
            $movimentacao->setValor($valor);
            $movimentacao->setDescontos($desconto);
            $movimentacao->setValorTotal($valorTotal);
            $movimentacao->setDescricao($descricao);
            /** @var TipoLancto $realizada */
            $realizada = $this->repoTipoLancto->findOneBy(['codigo' => 20]);
            $movimentacao->setTipoLancto($realizada);
            $movimentacao->setStatus('REALIZADA');
            $movimentacao->setModo($modo);
            $movimentacao->setDtMoviment($dtMoviment);
            $movimentacao->setDtVencto($dtVenctoEfetiva);
            $movimentacao->setDtVenctoEfetiva($dtVenctoEfetiva);
            $movimentacao->setDtPagto($dtVenctoEfetiva);
            $movimentacao->setBandeiraCartao($bandeiraCartao);
            $movimentacao->setPlanoPagtoCartao($planoPagtoCartao);

            /** @var Categoria $categoria */
            $categoria = null;
            if ($categoriaCodigo) {
                $categoria = $this->repoCategoria->findOneBy(['codigo' => $categoriaCodigo]);
            } else if ($valorNegativo) {
                $categoria = $this->repoCategoria->findOneBy(['codigo' => 2]);
            } else {
                $categoria = $this->repoCategoria->findOneBy(['codigo' => 1]);
            }
            $movimentacao->setCategoria($categoria);

            return $movimentacao;
        }

        return null;
    }


    /**
     * Verifica se é uma linha normal (DATA DESCRIÇÃO VALOR) ou não.
     * @param $linha
     * @return bool
     */
    private function ehLinhaExtratoSimplesOuSaldo($linha): bool
    {
        if (strpos(str_replace(' ', '', $linha), 'SALDO') !== FALSE) {
            return true;
        }
        if (preg_match(StringUtils::PATTERN_DATA, $linha, $matches) && preg_match(StringUtils::PATTERN_MONEY, $linha, $matches)) {
            return true;
        }

        return false;
    }


    /**
     * @param $numLinha
     * @return mixed
     * @throws ViewException
     */
    private function importLinhaExtratoSimples($numLinha)
    {
        $linha = trim($this->linhas[$numLinha]);
        preg_match(StringUtils::PATTERN_DATA, $linha, $matches);
        $dataStr = $matches['data'];

        preg_match(StringUtils::PATTERN_MONEY, $linha, $matches);
        $matches['SINAL_F'] = isset($matches['SINAL_F']) && $matches['SINAL_F'] === 'D' ? '-' : ($matches['SINAL_F'] ?? null);
        $valorStr = ($matches['SINAL_I'] ?: $matches['SINAL_F'] ?: '') . $matches['money'];

        $dtVenctoEfetiva = DateTimeUtils::parseDateStr($dataStr);

        $valor = StringUtils::parseFloat($valorStr, true);

        $entradaOuSaida = $valor < 0 ? 2 : 1;

        $descricao = str_replace(array($dataStr, $valorStr), '', $linha);

        // Se ainda não for a última linha...
        if ($numLinha < count($this->linhas) - 1) {
            // ...verifica se a próxima linha é uma linha completa (DATA DESCRIÇÃO VALOR), ou se é uma linha de complemento da linha anterior
            $linhaComplementar = trim($this->linhas[$numLinha + 1]);
            if ($linhaComplementar && !$this->ehLinhaExtratoSimplesOuSaldo($linhaComplementar)) {
                $this->linhasComplementares[] = $numLinha + 1;
                $descricao .= ' (' . trim($linhaComplementar) . ')';
            }
        }
        $descricao = str_replace('  ', ' ', $descricao);

        $camposLinha['descricao'] = mb_strtoupper($descricao);
        $camposLinha['dtVenctoEfetiva'] = $dtVenctoEfetiva;
        $camposLinha['dtMoviment'] = $dtVenctoEfetiva; // passo o mesmo por se tratar de extrato simples (diferente de extrato de cartão).
        $camposLinha['valor'] = $valor;
        $camposLinha['desconto'] = null;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;
        $camposLinha['modo'] = null;
        $camposLinha['categoriaCodigo'] = null;
        $camposLinha['planoPagtoCartao'] = null;
        $camposLinha['bandeiraCartao'] = null;

        return $camposLinha;
    }

    /**
     * @return array
     * @throws ViewException
     */
    private function importGrupoMovimentacao(): array
    {
        $movimentacoes = array();

        $i = 0;
        foreach ($this->linhas as $linha) {

            if (!$linha || $linha === TXT_LINHA_NAO_IMPORTADA || $linha === TXT_LINHA_IMPORTADA) {
                continue;
            }

            $camposLinha = $this->importLinhaExtratoSimples($i);

            $descricao = $camposLinha['descricao'];
            $dtMoviment = $camposLinha['dtMoviment'];
            $dtVenctoEfetiva = $camposLinha['dtVenctoEfetiva'];
            $valor = $camposLinha['valor'];
            $desconto = $camposLinha['desconto'];
            $valorTotal = $camposLinha['valorTotal'];

            // Tenta encontrar uma movimentação com as características passadas.
            $movs = $this->repoMovimentacao
                ->findBy([
                    'dtMoviment' => $dtMoviment,
                    'valor' => $valor,
                    'grupoItem' => $this->grupoItem
                ]);

            /** @var Movimentacao $importada */
            $importada = null;
            if ($movs && count($movs) > 0) {
                $importada = $movs[0];
            }

            if ($importada && !$importada->getDtPagto()) {
                $importada->setStatus('REALIZADA');
                $importada->setDtPagto($dtVenctoEfetiva);
            } else {

                $importada = new Movimentacao();
                $importada->setUUID(StringUtils::guidv4());

                $importada->setGrupoItem($this->grupoItem);

                /** @var Categoria $categ101 */
                $categ101 = $this->repoCategoria->findOneBy(['codigo' => '202001']);  // 2.02.001 - CUSTOS DE MERCADORIAS
                $importada->setCategoria($categ101);


                $importada->setCentroCusto($this->repoCentroCusto->find(1));
                $importada->setModo($this->repoModo->find(50));

                $importada->setValor($valor);
                $importada->setDescontos($desconto);
                $importada->setValorTotal($valorTotal);

                $importada->setDescricao(str_replace('  ', ' ', $descricao));
                /** @var TipoLancto $deGrupo */
                $deGrupo = $this->repoTipoLancto->findOneBy(['codigo' => 70]);
                $importada->setTipoLancto($deGrupo);
                $importada->setStatus('REALIZADA');

                $importada->setDtMoviment($dtMoviment);
                $importada->setDtVencto($dtVenctoEfetiva);
                $importada->setDtVenctoEfetiva($dtVenctoEfetiva);
                $importada->setDtPagto($dtVenctoEfetiva);

                $importada->setBandeiraCartao(null);
            }

            $movimentacoes[] = $importada;
        }

        return $movimentacoes;
    }

    /**
     * @param $numLinha
     * @return array|null
     * @throws ViewException
     */
    private function importLinhaExtratoModerninhaDebito($numLinha)
    {
        /**
         * 0 - Data_Transacao
         * 1 - 'MODERNINHA'
         * 2 - Tipo_Pagamento
         * 3 - Transacao_ID
         * 4 - Valor_Bruto
         */
        $linha = trim($this->linhas[$numLinha]);
        $camposLinha = array();
        $campos = explode("\t", $linha);

        if (count($campos) < 4) {
            return null;
        }

        $dtVenda = DateTimeUtils::parseDateStr($campos[0]);
        $valor = abs(StringUtils::parseFloat($campos[4], true));
        $entradaOuSaida = $valor < 0 ? 2 : 1;

        $descricao = $campos[1] . ' - ' . $campos[3] . ' (' . $campos[2] . ')';
        $descricao = preg_replace('@\n|\r|\t@', '', $descricao);

        $bandeira = 'N INF DÉB';

        $modo = $this->repoModo->find(10); // 'RECEB. CARTÃO DÉBITO'

        $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($bandeira, $modo);

        $camposLinha['bandeiraCartao'] = $bandeiraCartao;
        $camposLinha['planoPagtoCartao'] = 'DEBITO';
        $camposLinha['descricao'] = $descricao;
        $camposLinha['dtMoviment'] = $dtVenda;
        $camposLinha['dtVenctoEfetiva'] = $dtVenda;
        $camposLinha['valor'] = $valor;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;
        $camposLinha['categoriaCodigo'] = 199;

        return $camposLinha;
    }


    private function importLinhaExtratoCieloDebitoNovo($numLinha)
    {
        $linha = trim($this->linhas[$numLinha]);
        $campos = explode("\t", $linha);
        if (count($campos) < 10) {
            return null;
        }

        /**
         * 0 Data da venda
         * 1 Data da autorização
         * 2 Bandeira
         * 3 Forma de pagamento
         * 4 Quantidade de parcelas
         * 5 Valor da venda
         * 6 Taxa de administração (%)
         * 7 Valor descontado
         * 8 Previsão de pagamento
         * 9 Valor líquido da venda
         * 10 Número Lógico
         */

        $dtVenda = DateTimeUtils::parseDateStr($campos[0]);
        $valor = abs(StringUtils::parseFloat($campos[5], true));
        $entradaOuSaida = $valor < 0 ? 2 : 1;
        $descricao = 'DÉBITO ' . $campos[2]; // + ' ' + campos[1] + ' (' + campos[4] + ')';
        $modo = $this->repoModo->find(10); // 'RECEB. CARTÃO DÉBITO'

        $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($campos[2], $modo);

        $camposLinha['bandeiraCartao'] = $bandeiraCartao;
        $camposLinha['planoPagtoCartao'] = 'DEBITO';
        $camposLinha['descricao'] = $descricao;
        $camposLinha['dtMoviment'] = $dtVenda;
        $camposLinha['dtVenctoEfetiva'] = $dtVenda;
        $camposLinha['valor'] = $valor;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;

        return $camposLinha;
    }


    private function importLinhaExtratoCieloCreditoNovo($numLinha)
    {
        $linha = trim($this->linhas[$numLinha]);
        $campos = explode("\t", $linha);
        if (count($campos) < 9) {
            return null;
        }
        /**
         * 0 Data de pagamento
         * 1 Data de venda
         * 2 Forma de pagamento
         * 3 NSU
         * 4 Número do cartão
         * 5 Valor bruto
         * 6 Status
         * 7 Valor líquido
         * 8 TID
         * 9 Taxa
         * 10 Número do EC
         * 11 Bandeira
         */

        $dtVenda = DateTimeUtils::parseDateStr($campos[1]);
        $dtPrevistaPagto = DateTimeUtils::parseDateStr($campos[0]);
        $numeroCartao = trim($campos[4]);
        $tid = trim($campos[8]);
        $codigoAutorizacao = trim($campos[10]);
        $bandeira = trim($campos[11]);
        $formaDePagamento = trim($campos[2]);
        $valor = abs(StringUtils::parseFloat($campos[5], true));

        $entradaOuSaida = $valor < 0 ? 2 : 1;


        $modo = $this->repoModo->find(9); // 'RECEB. CARTÃO CRÉDITO'

        $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($bandeira, $modo);

        $planoPagtoCartao = (stripos($formaDePagamento, 'parc') === FALSE) ? 'CREDITO_30DD' : 'CREDITO_PARCELADO';

        $descricao = $formaDePagamento . ' - ' . $bandeira . ' - ' . $numeroCartao . ' (' . $codigoAutorizacao . ') ' . $tid;
        $descricao = str_replace('  ', ' ', $descricao);

        $camposLinha['modo'] = $modo;
        $camposLinha['bandeiraCartao'] = $bandeiraCartao;
        $camposLinha['planoPagtoCartao'] = $planoPagtoCartao;
        $camposLinha['descricao'] = $descricao;
        $camposLinha['dtMoviment'] = $dtVenda;
        $camposLinha['dtVenctoEfetiva'] = $dtPrevistaPagto;
        $camposLinha['valor'] = $valor;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;
        $camposLinha['categoriaCodigo'] = '101';
        return $camposLinha;
    }


    /**
     * @param $numLinha
     * @return array
     * @throws ViewException
     */
    private function importLinhaExtratoCartaoArrayCabecalho($numLinha)
    {
        $linha = trim($this->linhas[$numLinha]);
        $campos = explode("\t", $linha);

        $camposLinha = [];

        $dtVenda = null;
        $dtPrevistaPagto = null;
        $tipo = null;
        $bandeira = null;
        $valor = null;
        $descricao = null;

        foreach ($this->arrayCabecalho as $campo => $key) {
            switch ($campo) {
                case 'dtVenda':
                    {
                        $dtVenda = DateTimeUtils::parseDateStr($campos[$key]);
                        break;
                    }
                case 'dtPrevistaPagto':
                    {
                        $dtPrevistaPagto = DateTimeUtils::parseDateStr($campos[$key]);
                        break;
                    }
                case 'tipo':
                    {
                        $tipo = trim($campos[$key]);
                        break;
                    }
                case 'bandeira':
                    {
                        $bandeira = trim($campos[$key]);
                        break;
                    }
                case 'valor':
                    {
                        $valor = abs(StringUtils::parseFloat($campos[$key], true));
                        break;
                    }
                case 'descricao':
                    {
                        if (is_array($key)) {
                            $valores = [];
                            foreach ($key['campos'] as $campoCSV) {
                                $valores[] = trim($campos[$campoCSV]);
                            }
                            $descricao = vsprintf($key['formato'], $valores);
                        } else {
                            $descricao = trim($campos[$key]);
                        }
                        break;
                    }
            }
        }

        $entradaOuSaida = $valor < 0 ? 2 : 1;

        /** @var Modo $modo */
        $modo = null;
        if (strpos($this->tipoExtrato, 'CREDITO') !== FALSE) {
            $modo = $this->repoModo->find(9); // 'RECEB. CARTÃO CRÉDITO'
        }
        if (strpos($this->tipoExtrato, 'DEBITO') !== FALSE) {
            $modo = $this->repoModo->find(10); // 'RECEB. CARTÃO DEBITO'
        }

        $bandeiraCartao = null;
        if ($modo && $bandeira) {
            $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($bandeira, $modo);
        }

        $planoPagtoCartao = (stripos($descricao, 'parc') === FALSE) ? 'CREDITO_30DD' : 'CREDITO_PARCELADO';

        $camposLinha['modo'] = $modo;
        $camposLinha['bandeiraCartao'] = $bandeiraCartao;
        $camposLinha['planoPagtoCartao'] = $planoPagtoCartao;
        $camposLinha['descricao'] = $descricao;
        $camposLinha['dtMoviment'] = $dtVenda;
        $camposLinha['dtVenctoEfetiva'] = $dtPrevistaPagto;
        $camposLinha['valor'] = $valor;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;
        $camposLinha['categoriaCodigo'] = '101';
        return $camposLinha;
    }

    /**
     * @param $numLinha
     * @return array
     * @throws ViewException
     */
    private function importLinhaExtratoStoneCredito($numLinha)
    {
        /**
         * 0 CATEGORIA
         * 1 HORA DA VENDA
         * 2 DATA DE VENCIMENTO
         * 3 TIPO
         * 4 Nº DA PARCELA
         * 5 QTD DE PARCELAS
         * 6 BANDEIRA
         * 7 STONE ID
         * 8 N° CARTÃO
         * 9 VALOR BRUTO
         * 10 VALOR LÍQUIDO
         * 11 ÚLTIMO STATUS
         * 12 DATA DO ÚLTIMO STATUS
         */

        if ($this->identificarPorCabecalho) {
            return $this->importLinhaExtratoCartaoArrayCabecalho($numLinha);
        }
        // else

        $linha = trim($this->linhas[$numLinha]);
        $campos = explode("\t", $linha);
        if (count($campos) < 12) {
            return null;
        }

        $dtVenda = DateTimeUtils::parseDateStr($campos[1]);
        $dtPrevistaPagto = DateTimeUtils::parseDateStr($campos[2]);
        $tipo = trim($campos[3]);
        $bandeira = trim($campos[6]);

        $descricao = trim($campos[0]) . ' - ' . $tipo . ' - ' . $bandeira . ' (' . trim($campos[4]) . '/' . trim($campos[5]) . ') ' . trim($campos[8]);

        $valor = abs(StringUtils::parseFloat($campos[9], true));
        $entradaOuSaida = $valor < 0 ? 2 : 1;

        $modo = $this->repoModo->find(9); // 'RECEB. CARTÃO CRÉDITO'

        $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($bandeira, $modo);

        $planoPagtoCartao = (stripos($descricao, 'parc') === FALSE) ? 'CREDITO_30DD' : 'CREDITO_PARCELADO';

        $camposLinha['modo'] = $modo;
        $camposLinha['bandeiraCartao'] = $bandeiraCartao;
        $camposLinha['planoPagtoCartao'] = $planoPagtoCartao;
        $camposLinha['descricao'] = $descricao;
        $camposLinha['dtMoviment'] = $dtVenda;
        $camposLinha['dtVenctoEfetiva'] = $dtPrevistaPagto;
        $camposLinha['valor'] = $valor;
        $camposLinha['valorTotal'] = $valor;
        $camposLinha['entradaOuSaida'] = $entradaOuSaida;
        $camposLinha['categoriaCodigo'] = '101';
        return $camposLinha;
    }


    private function importLinhaExtratoStoneDebito($numLinha)
    {

        /**
         * 0 HORA DA VENDA
         * 1 TIPO
         * 2 BANDEIRA
         * 3 MEIO DE CAPTURA
         * 4 STONE ID
         * 5 VALOR BRUTO
         * 6 VALOR LÍQUIDO
         * 7 N° CARTÃO
         * 8 SERIAL NUMBER
         * 9 ÚLTIMO STATUS
         * 10 DATA DO ÚLTIMO STATUS
         */

        $linha = trim($this->linhas[$numLinha]);
        $campos = explode("\t", $linha);
        if (count($campos) < 9) {
            return null;
        }


        try {
            $dtVenda = DateTimeUtils::parseDateStr($campos[0]);
            $valor = abs(StringUtils::parseFloat($campos[5], true));
            $entradaOuSaida = $valor < 0 ? 2 : 1;
            $descricao = $campos[1] . ' - ' . $campos[4] . ' - ' . $campos[7];
            $descricao = preg_replace('@\n|\r|\t@', '', $descricao);
            $modo = $this->repoModo->find(10);// 'RECEB. CARTÃO DÉBITO'
            $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($campos[2], $modo);
            $camposLinha['bandeiraCartao'] = $bandeiraCartao;
            $camposLinha['planoPagtoCartao'] = 'DEBITO';
            $camposLinha['descricao'] = $descricao;
            $camposLinha['dtMoviment'] = $dtVenda;
            $camposLinha['dtVenctoEfetiva'] = $dtVenda;
            $camposLinha['valor'] = $valor;
            $camposLinha['valorTotal'] = $valor;
            $camposLinha['entradaOuSaida'] = $entradaOuSaida;
            return $camposLinha;
        } catch (\Exception $e) {
            return null;
        }


    }


    /**
     * @param $movs
     * @param $tipoExtrato
     * @param Carteira|null $carteiraExtrato
     * @param Carteira|null $carteiraDestino
     * @param GrupoItem|null $grupoItem
     */
    public function verificarImportadasAMais($movs, $tipoExtrato, ?Carteira $carteiraExtrato, ?Carteira $carteiraDestino, ?GrupoItem $grupoItem): void
    {
        /** @var Movimentacao $primeira */
        $primeira = $movs[0];
        $dtPagto = $primeira->getDtPagto();
        $dtIni = DateTimeUtils::getPrimeiroDiaMes($dtPagto);
        $dtFim = DateTimeUtils::getUltimoDiaMes($dtPagto);

        if (strpos($tipoExtrato, 'DEBITO') !== FALSE) {
            $dql = 'SELECT m FROM App\Entity\Financeiro\Movimentacao m 
                WHERE 
                m.dtPagto BETWEEN :dtIni && :dtFim && 
                m.carteira = :carteiraDestino &&
                modo = :modo && 
                && cadeia IN (SELECT m.cadeia FROM App\Entity\Financeiro\Movimentacao m2 WHERE m2.cadeia = m.cadeia && m2.carteira = :carteiraExtrato)';

            $qry = $this->doctrine->createQuery($dql);
            $qry->setParameter('dtIni', $dtIni);
            $qry->setParameter('dtFim', $dtFim);
            $qry->setParameter('carteiraDestino', $carteiraDestino);
            $qry->setParameter('carteiraExtrato', $carteiraExtrato);
            $modo = $this->repoModo->find(10); // 'RECEB. CARTÃO DEBITO'
            $qry->setParameter('modo', $modo);
            $rs = $qry->getResult();
// FIXME: terminar

        }

    }

    /**
     * @param Movimentacao $movimentacao
     * @return bool
     */
    private function checkJaImportada(Movimentacao $movimentacao): bool
    {
        if ($movimentacao->getId()) {
            /** @var Movimentacao $movsJaImportada */
            foreach ($this->movsJaImportadas as $movsJaImportada) {
                if ($movsJaImportada->getId() === $movimentacao->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

}