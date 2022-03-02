<?php

namespace App\Business\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegraImportacaoLinha;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\BandeiraCartaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CentroCustoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\ModoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\RegraImportacaoLinhaRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\TipoLanctoRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

const TXT_LINHA_NAO_IMPORTADA = '<<< LINHAS NÃO IMPORTADAS >>>';

const TXT_LINHA_IMPORTADA = '<<< LINHAS IMPORTADAS >>>';

/**
 * Classe responsável pelas regras de negócio de importação de extratos.
 *
 * @package CrosierSource\CrosierLibRadxBundle\Business\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoImporter
{

    private EntityManagerInterface $doctrine;

    private ?string $stringExtrato = null;

    private ?array $linhas = [];

    private ?array $linhasAImportar = [];

    /**
     * Armazena linhas de descrição complementar para verificações durante o processo.
     */
    private array $linhasComplementares = [];

    /**
     * Armazena as movimentações de categoria 1.01 que já foram importadas.
     */
    private array $movs101JaImportadas = [];

    /**
     * Para armazenar as movimentações já importadas afim de que não sejam importadas 2x por duplicidade.
     */
    private array $movsJaImportadas = [];


    private ?Carteira $carteiraExtrato = null;

    private ?GrupoItem $grupoItem = null;

    private ?bool $identificarPorCabecalho = false;

    private ModoRepository $repoModo;

    private MovimentacaoRepository $repoMovimentacao;

    private CategoriaRepository $repoCategoria;

    private TipoLanctoRepository $repoTipoLancto;

    private BandeiraCartaoRepository $repoBandeiraCartao;

    private CentroCustoRepository $repoCentroCusto;

    private MovimentacaoEntityHandler $movimentacaoEntityHandler;

    private ?string $uuidLote = null;

    private array $duplicacoes = [];

    private ?\DateTime $menorDt = null;

    private ?\DateTime $maiorDt = null;


    public function __construct(EntityManagerInterface $doctrine, MovimentacaoEntityHandler $movimentacaoEntityHandler)
    {
        $this->doctrine = $doctrine;
        $this->repoModo = $this->doctrine->getRepository(Modo::class);
        $this->repoMovimentacao = $this->doctrine->getRepository(Movimentacao::class);
        $this->repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $this->repoTipoLancto = $this->doctrine->getRepository(TipoLancto::class);
        $this->repoBandeiraCartao = $this->doctrine->getRepository(BandeiraCartao::class);
        $this->repoCentroCusto = $this->doctrine->getRepository(CentroCusto::class);
        $this->movimentacaoEntityHandler = $movimentacaoEntityHandler;

        $this->uuidLote = StringUtils::guidv4();
    }

    /**
     * @throws ViewException
     */
    public function importarExtratoSimples(Carteira $carteiraExtrato, string $linhasExtrato)
    {
        $this->stringExtrato = $linhasExtrato;
        $this->carteiraExtrato = $carteiraExtrato;

        $conn = $this->movimentacaoEntityHandler->getDoctrine()->getConnection();

        try {
            $conn->beginTransaction();

            $this->movsJaImportadas = [];

            $linhasImportadas = []; // para exibir no resultado final
            $linhasNaoImportadas = []; // para exibir no resultado final

            $this->linhasAImportar = [];

            $this->linhas = explode("\n", $this->stringExtrato);

            $r = [];
            $r['LINHAS_RESULT'] = null;
            $r['movs'] = null;
            $r['err'] = null;

            $qtdeLinhas = count($this->linhas);


            for ($i = 0; $i < $qtdeLinhas; $i++) {
                $linha = trim($this->linhas[$i]);

                // Verifica se é uma linha (de descrição) complementar já importada
                if (in_array($i, $this->linhasComplementares, true)) {
                    $this->linhasAImportar[] = $linha;
                    continue;
                }

                if (!$linha || trim($linha) === TXT_LINHA_IMPORTADA || trim($linha) === TXT_LINHA_NAO_IMPORTADA) {
                    $linhasNaoImportadas[] = $linha;
                    continue;
                }

                if ($this->tipoExtrato === 'EXTRATO_SIMPLES' && !$this->ehLinhaExtratoSimplesOuSaldo($linha)) {
                    $linhasNaoImportadas[] = $linha . ' (NÃO É LINHA DE EXTRATO?)';
                    continue;
                }

                $this->linhasAImportar[] = $linha;
            }

            $this->checkDuplicacoes($this->linhasAImportar);

            foreach ($this->linhasAImportar as $i => $linha) {
                try {
                    $camposLinha = $this->obterCamposLinha($i);
                    $movimentacao = $this->camposLinhaToMovimentacao($camposLinha);

                    if ($movimentacao) {
                        $movimentacao->jsonData['importacao_linha'] = $linha;
                        $movimentacao->jsonData['uuid_lote'] = $this->uuidLote;
                        $this->movimentacaoEntityHandler->save($movimentacao);
                        $this->movsJaImportadas[] = $movimentacao;
                        $linhasImportadas[] = $linha;
                    } else {
                        $linhasNaoImportadas[] = $linha . ' (NÃO GEROU NOVA MOVIMENTAÇÃO)';
                    }
                } catch (ViewException $e) {
                    $r['err'][] = [
                        'linha' => $linha,
                        'errMsg' => $e->getMessage()
                    ];
                    $linhasNaoImportadas[] = $linha . ' (ERRO AO IMPORTAR: ' . $e->getMessage() . ')';
                } catch (\Throwable $e) {
                    $r['err'][] = [
                        'linha' => $linha,
                        'errMsg' => 'Erro geral ao processar linha: ' . $linha
                    ];
                    $linhasNaoImportadas[] = $linha . ' (ERRO AO IMPORTAR!)';
                }
            }

            $r['LINHAS_RESULT'] = '';
            if (count($linhasNaoImportadas) > 0) {
                $r['LINHAS_RESULT'] .= TXT_LINHA_NAO_IMPORTADA . "\n" .
                    implode("\n", $linhasNaoImportadas) . "\n\n\n\n\n";
            }
            $r['LINHAS_RESULT'] .= TXT_LINHA_IMPORTADA . "\n" .
                implode("\n", $linhasImportadas);

            $r['qtdeImportadas'] = count($linhasImportadas);
            $r['qtdeNaoImportadas'] = count($linhasNaoImportadas);


            $r['menorData'] = $this->menorDt ? $this->menorDt->format('Y-m-d') : null;
            $r['maiorData'] = $this->maiorDt ? $this->maiorDt->format('Y-m-d') : null;

            $conn->commit();
            $r['RESULT'] = 'OK';
            return $r;
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    throw new ViewException('Erro ao fazer rollback', 0, $e);
                }
            }
            throw new ViewException('Erro ao importar', 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    public function importarExtratoCartao(OperadoraCartao $operadoraCartao, string $linhasExtrato)
    {
        $r = [];
        $linhas = explode("\n", $linhasExtrato);
        $primeira = $linhas[0];
        $cabecalho = explode("\t", $primeira);

        $rsPadroesCabecalho = $this->movimentacaoEntityHandler->getDoctrine()->getConnection()
            ->fetchAssociative(
                'SELECT valor FROM cfg_app_config WHERE ' .
                'app_uuid = \'9121ea11-dc5d-4a22-9596-187f5452f95a\' AND ' .
                'chave = \'fin.extratos_cartoes.padroes_cabecalhos\'');

        $padroesCabecalho = json_decode($rsPadroesCabecalho['valor'], true);
        $achouPadrao = false;
        foreach ($padroesCabecalho as $padraoCabecalho) {
            if ($padraoCabecalho['cabecalho'] === $primeira) {
                $achouPadrao = true;
                break;
            }
        }
        if (!$achouPadrao) {
            throw new ViewException('Formato de extrato não encontrado para o cabeçalho informado');
        }


        $conn = $this->movimentacaoEntityHandler->getDoctrine()->getConnection();

        /** @var MovimentacaoRepository $repoMovimentacao */
        $repoMovimentacao = $this->doctrine->getRepository(Movimentacao::class);

        $categ195 = $this->doctrine->getRepository(Categoria::class)->findOneByCodigo(195);

        $linhasNaoImportadas = [];
        $linhasImportadas = [];
        try {
            $conn->beginTransaction();

            for ($i = 1; $i < count($linhas); $i++) {

                $linha = trim($linhas[$i]);
                if (!$linha) continue;
                try {

                    $campoz = explode("\t", $linha);
                    $campos = [];
                    foreach ($campoz as $k => $valor) {
                        $campos[$cabecalho[$k]] = $valor;
                    }

                    $idTransacaoCartao = $campos[$padraoCabecalho['campos']['idTransacaoCartao']];
                    $dtVenda = DateTimeUtils::parseDateStr($campos[$padraoCabecalho['campos']['dtMoviment']]);
                    $valor = abs(StringUtils::parseFloat($campos[$padraoCabecalho['campos']['valor']], true));
                    $numCartao = $campos[$padraoCabecalho['campos']['numCartao']] ?? '????';

                    // Duas formas de encontrar a movimentação já lançada:
                    // 1) Pelo idTransacaoCartao
                    $movimentacao = $repoMovimentacao->findOneByFiltersSimpl([
                        ['operadoraCartao', 'EQ', $operadoraCartao],
                        ['idTransacaoCartao', 'EQ', $idTransacaoCartao],
                        ['dtMoviment', 'EQ', $dtVenda->format('Y-m-d')],
                        ['valor', 'EQ', $valor],
                    ]);
                    if (!$movimentacao) {
                        // 2) Ou pelos 4 últimos dígitos (quando é lançado via movimentação de caixa)
                        $movimentacao = $repoMovimentacao->findOneByFiltersSimpl([
                            ['operadoraCartao', 'EQ', $operadoraCartao],
                            ['numCartao', 'LIKE_END', substr($numCartao, -4)],
                            ['dtMoviment', 'EQ', $dtVenda->format('Y-m-d')],
                            ['valor', 'EQ', $valor],
                        ]);
                        if (!$movimentacao) {
                            $movimentacao = new Movimentacao();
                            $movimentacao->categoria = $categ195;
                            $descricao = sprintf('%s %s %s (Transação: %s)',
                                $campos[$padraoCabecalho['campos']['debitoOuCredito']],
                                $campos[$padraoCabecalho['campos']['bandeira']],
                                $campos[$padraoCabecalho['campos']['numCartao']],
                                $campos[$padraoCabecalho['campos']['idTransacaoCartao']]);

                            $movimentacao->descricao = $descricao;
                        }
                    }

                    $movimentacao->dtMoviment = $dtVenda;
                    $movimentacao->valor = $valor;
                    $movimentacao->idTransacaoCartao = $idTransacaoCartao;

                    if (!$this->menorDt || DateTimeUtils::diffInDias($dtVenda, $this->menorDt) < 0) {
                        $this->menorDt = $dtVenda;
                    }
                    if (!$this->maiorDt || DateTimeUtils::diffInDias($dtVenda, $this->maiorDt) > 0) {
                        $this->maiorDt = $dtVenda;
                    }

                    $statusExtrato = mb_strtoupper($campos[$padraoCabecalho['campos']['status']]);

                    if (in_array($statusExtrato, ['CAPTURADA'])) {
                        $movimentacao->status = 'ABERTA';
                    } else {
                        $movimentacao->status = 'REALIZADA';
                        $movimentacao->dtVencto = DateTimeUtils::parseDateStr($campos[$padraoCabecalho['campos']['dtVencto']]);
                        $movimentacao->dtPagto = DateTimeUtils::parseDateStr($campos[$padraoCabecalho['campos']['dtVencto']]);
                    }

                    $tipo = $campos[$padraoCabecalho['campos']['debitoOuCredito']];

                    preg_match('/(?<tipo>Credito|Crédito|Debito|Débito){1}(\s(?<numParcelas>\d)x)?/i', $tipo, $outputTipo);
                    $tipo = mb_strtoupper($outputTipo['tipo']);
                    $tipo = $tipo === 'DEBITO' ? 'DÉBITO' : $tipo;
                    $tipo = $tipo === 'CREDITO' ? 'CRÉDITO' : $tipo;

                    $numParcelas = $campos[$padraoCabecalho['campos']['qtdeParcelas'] ?? 'nao_definido'] ?? $outputTipo['numParcelas'];
                    $movimentacao->qtdeParcelas = $numParcelas;
                    $movimentacao->numParcela = $numParcelas;
                    $movimentacao->cadeiaOrdem = $campos[$padraoCabecalho['campos']['numParcela'] ?? 'nao_definido'] ?? 1;

                    $movimentacao->numCartao = $numCartao;


                    $modo = $this->repoModo->find($tipo === 'DÉBITO' ? 10 : 9);
                    $movimentacao->modo = $modo;

                    $movimentacao->operadoraCartao = $operadoraCartao;

                    $bandeira = $campos[$padraoCabecalho['campos']['bandeira']];
                    if (!$bandeira) {
                        $bandeira = $tipo === 'DÉBITO' ? 'N INF DÉB' : 'N INF CRÉD';
                    }
                    $bandeiraCartao = $this->repoBandeiraCartao->findByLabelsAndModo($bandeira, $modo);
                    $movimentacao->bandeiraCartao = $bandeiraCartao;

                    $movimentacao->jsonData['importacao_linha'] = $linha;
                    $movimentacao->jsonData['importacao_campos'] = $campos;

                    $movimentacao = $this->movimentacaoEntityHandler->save($movimentacao);

                    $linhasImportadas[] = $linha;
                } catch (ViewException $e) {
                    $r['err'][] = [
                        'linha' => $linha,
                        'errMsg' => $e->getMessage()
                    ];
                    $linhasNaoImportadas[] = $linha . ' (ERRO AO IMPORTAR: ' . $e->getMessage() . ')';
                } catch (\Throwable $e) {
                    $r['err'][] = [
                        'linha' => $linha,
                        'errMsg' => 'Erro geral ao processar linha: ' . $linha
                    ];
                    $linhasNaoImportadas[] = $linha . ' (ERRO AO IMPORTAR!)';
                }
            }

            $r['LINHAS_RESULT'] = '';
            if (count($linhasNaoImportadas) > 0) {
                $r['LINHAS_RESULT'] .= TXT_LINHA_NAO_IMPORTADA . "\n" .
                    implode("\n", $linhasNaoImportadas) . "\n\n\n\n\n";
            }
            $r['LINHAS_RESULT'] .= TXT_LINHA_IMPORTADA . "\n" .
                implode("\n", $linhasImportadas);

            $r['menorData'] = $this->menorDt ? $this->menorDt->format('Y-m-d') : null;
            $r['maiorData'] = $this->maiorDt ? $this->maiorDt->format('Y-m-d') : null;

            $r['qtdeImportadas'] = count($linhasImportadas);
            $r['qtdeNaoImportadas'] = count($linhasNaoImportadas);

            $conn->commit();
            $r['RESULT'] = 'OK';
            return $r;
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    throw new ViewException('Erro ao fazer rollback', 0, $e);
                }
            }
            throw new ViewException('Erro ao importar', 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    public function importarExtratoGrupo(GrupoItem $grupoItem, string $linhasExtrato)
    {
        $conn = $this->movimentacaoEntityHandler->getDoctrine()->getConnection();

        try {
            $conn->beginTransaction();

            $movimentacoes = [];

            $i = 0;
            foreach ($this->linhasAImportar as $linha) {

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

                if ($importada && !$importada->dtPagto) {
                    $importada->status = 'REALIZADA';
                    $importada->dtPagto = $dtVenctoEfetiva;
                } else {

                    $importada = new Movimentacao();
                    $importada->UUID = (StringUtils::guidv4());

                    $importada->grupoItem = ($this->grupoItem);

                    /** @var Categoria $categ101 */
                    $categ101 = $this->repoCategoria->findOneBy(['codigo' => '202001']);  // 2.02.001 - CUSTOS DE MERCADORIAS
                    $importada->categoria = ($categ101);

                    $importada->centroCusto = ($this->repoCentroCusto->find(1));
                    $importada->modo = ($this->repoModo->find(50));

                    $importada->valor = ($valor);
                    $importada->descontos = ($desconto);
                    $importada->valorTotal = ($valorTotal);

                    $importada->descricao = (str_replace('  ', ' ', $descricao));
                    /** @var TipoLancto $deGrupo */
                    $deGrupo = $this->repoTipoLancto->findOneBy(['codigo' => 70]);
                    $importada->tipoLancto = ($deGrupo);
                    $importada->status = ('REALIZADA');

                    $importada->dtMoviment = ($dtMoviment);
                    $importada->dtVencto = ($dtVenctoEfetiva);
                    $importada->dtVenctoEfetiva = ($dtVenctoEfetiva);
                    $importada->dtPagto = ($dtVenctoEfetiva);

                    $importada->bandeiraCartao = (null);
                }

                $movimentacoes[] = $importada;
            }

            return $movimentacoes;

            $conn->commit();
            $r['RESULT'] = 'OK';
            return $r;
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    throw new ViewException('Erro ao fazer rollback', 0, $e);
                }
            }
            throw new ViewException('Erro ao importar', 0, $e);
        }
    }


    private function checkDuplicacoes(array &$linhas): void
    {
        $this->duplicacoes = [];

        foreach ($linhas as $linha) {
            $camposLinha = $this->obterCamposLinha(array_search($linha, $this->linhasAImportar, true));
            $dtMovimentF = $camposLinha['dtMoviment']->format('d-m-Y');
            $chave = $dtMovimentF . '___' . $camposLinha['valorTotal'];
            $this->duplicacoes[$chave] = $this->duplicacoes[$chave] ?? 0;
            $this->duplicacoes[$chave]++;
        }
    }

    private function removerLinha(array &$linhas, $linha, ?int $qtde = null)
    {
        $i = 0;
        while (true) {
            $index = array_search($linha, $linhas, true);
            if ($index === FALSE) break;
            if ($qtde && $i >= $qtde) break;
            $i++;
            array_splice($linhas, $index, 1);
        }
    }


    private function obterCamposLinha(int $numLinha): ?array
    {
        $linha = $this->linhasAImportar[$numLinha];
        $camposLinha['linha'] = $linha;

        $antesDoPrimeiroEspaco = substr($linha, 0, StringUtils::strposRegex($linha, '\s'));
        $provavelData = substr($antesDoPrimeiroEspaco, 0, 10);

        $dataStr = DateTimeUtils::parseDateStr($provavelData)->format('d/m/Y');
        $linha = substr($linha, StringUtils::strposRegex($linha, '\s') + 1);

        preg_match(StringUtils::PATTERN_MONEY, $linha, $matches);
        $matches['SINAL_F'] = isset($matches['SINAL_F']) && $matches['SINAL_F'] === 'D' ? '-' : ($matches['SINAL_F'] ?? null);
        $valorStr = ($matches['SINAL_I'] ?: $matches['SINAL_F'] ?: '') . $matches['money'];

        $dtVenctoEfetiva = DateTimeUtils::parseDateStr($dataStr);

        if (!$this->menorDt || DateTimeUtils::diffInDias($dtVenctoEfetiva, $this->menorDt) < 0) {
            $this->menorDt = $dtVenctoEfetiva;
        }
        if (!$this->maiorDt || DateTimeUtils::diffInDias($dtVenctoEfetiva, $this->maiorDt) > 0) {
            $this->maiorDt = $dtVenctoEfetiva;
        }

        $valor = StringUtils::parseFloat($valorStr, true);

        $entradaOuSaida = $valor < 0 ? 2 : 1;

        $descricao = trim(str_replace($valorStr, '', $linha));
        $descricao = preg_replace('/\s/', ' ', $descricao);

        // Se ainda não for a última linha...
        if ($numLinha < count($this->linhasAImportar) - 1) {
            // ...verifica se a próxima linha é uma linha completa (DATA DESCRIÇÃO VALOR), ou se é uma linha de complemento da linha anterior
            $linhaComplementar = trim($this->linhasAImportar[$numLinha + 1]);
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


    private function camposLinhaToMovimentacao(array $camposLinha)
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


        $dtMoviment = $camposLinha['dtMoviment'];

        $movs = $this->doctrine->getConnection()->fetchAssociative(
            'SELECT count(m.id) as qt FROM fin_movimentacao m, fin_categoria c WHERE ' .
            'm.carteira_id = :carteiraId AND ' .
            'm.categoria_id = c.id AND ' .
            'm.valor_total = :valor AND ' .
            'm.dt_pagto = :dtPagto AND ' . // ou seja, só considera as realizadas
            'c.codigo_super = :codigoSuper',
            [
                'valor' => abs($valorTotal),
                'dtPagto' => $dtMoviment->format('Y-m-d'),
                'codigoSuper' => $valorNegativo ? 2 : 1,
                'carteiraId' => $this->carteiraExtrato->getId(),
            ]
        );
        $valorReal = (abs($valorTotal) * ($valorNegativo ? -1 : 1));
        $chaveDuplicacao = $dtMoviment->format('d-m-Y') . '___' . $valorReal;
        if ($movs && ((int)($movs['qt'] ?? 0) >= ($this->duplicacoes[$chaveDuplicacao] ?? 0))) {
            return null;
        }

        /** @var \DateTime $dtVenctoEfetiva */
        $dtVenctoEfetiva = $camposLinha['dtVenctoEfetiva'];
        // $entradaOuSaida = $camposLinha['entradaOuSaida'];

        $planoPagtoCartao = $camposLinha['planoPagtoCartao'];
        $bandeiraCartao = $camposLinha['bandeiraCartao'];


        $numCheque = null;

        /** @var RegraImportacaoLinhaRepository $repoRegraImportacaoLinha */
        $repoRegraImportacaoLinha = $this->doctrine->getRepository(RegraImportacaoLinha::class);

        $cache = new FilesystemAdapter($_SERVER['CROSIERAPPRADX_UUID'] . '.MovimentacaoImporter', 36000, $_SERVER['CROSIER_SESSIONS_FOLDER']);
        $regras = $cache->get('regrasPorCarteira', function (ItemInterface $item) use ($repoRegraImportacaoLinha) {
            return $repoRegraImportacaoLinha->findAllBy($this->carteiraExtrato);
        });


        /** @var RegraImportacaoLinha $regra */
        $regra = null;
        /** @var RegraImportacaoLinha $r */
        foreach ($regras as $r) {
            if ($r->regraRegexJava) {
                if (preg_match('@' . $r->regraRegexJava . '@', $descricao)) {
                    if ($r->sinalValor === 0 ||
                        ($r->sinalValor === -1 && $valorNegativo) ||
                        ($r->sinalValor === 1 && !$valorNegativo)) {
                        $regra = $repoRegraImportacaoLinha->find($r->getId());
                        break;
                    }
                }
            }
        }

        if ($regra) {
            preg_match('@' . $regra->regraRegexJava . '@', $descricao, $matches);
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
            $movsAbertasMesmoDia = $this->repoMovimentacao->findByFiltersSimpl($filterByCheque, null, 0, -1);
            foreach ($movsAbertasMesmoDia as $mov) {
                if ((!$this->checkJaImportada($mov)) && !in_array($mov->getId(), $movs, true)) {
                    $movimentacao = $this->repoMovimentacao->find($mov->getId());
                    if (!$movimentacao->UUID) {
                        $movimentacao->UUID = StringUtils::guidv4();
                    }
                    $movimentacao->dtPagto = ($dtVenctoEfetiva);
                    $movimentacao->status = ('REALIZADA');
                    $movimentacao->carteira = ($this->carteiraExtrato);
                    return $movimentacao;
                }
            }
        }


        if ($regra) {
            $movimentacao = new Movimentacao();

            $movimentacao->UUID = StringUtils::guidv4();

            $carteiraOrigem = $regra->carteira ? $regra->carteira : $this->carteiraExtrato;
            $carteiraDestino = $regra->carteiraDestino ?? null;

            $movimentacao->carteira = ($carteiraOrigem);
            $movimentacao->carteiraDestino = ($carteiraDestino);

            if ($regra->tipoLancto->codigo === 60) {
                // Nas transferências entre contas próprias, a regra informa a carteira de ||igem.
                // A de destino, se não for informada na regra, será a do extrato.

                if (!$regra->categoria->codigo === '299') {
                    throw new ViewException('Regras para transferências entre carteiras próprias devem ser apenas com categoria 2.99');
                }

                // Se a regra informar a carteira da 299, prevalesce
                $cart299 = $regra->carteira ?: $this->carteiraExtrato;

                $cart199 = $regra->carteiraDestino;
                if ((!$cart199) || $cart199->codigo === '99') {
                    $cart199 = $this->carteiraExtrato;
                }

                $movimentacao->carteira = ($cart299);
                $carteiraDestino = $cart199;
                $movimentacao->carteiraDestino = ($carteiraDestino);
                // se NÃO for regra para TRANSF_PROPRIA
            } else {
                if (in_array($regra->tipoLancto->codigo, [40, 41], true)) {

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
                        $movimentacao->dtPagto = $dtVenctoEfetiva;
                        return $movimentacao;
                    }
                    // else
                    $movimentacao = new Movimentacao();
                    $movimentacao->UUID = (StringUtils::guidv4());
                    $movimentacao->chequeNumCheque = ($numCheque);
                    /** @var Carteira $carteira */
                    $carteira = $regra->carteira ?: $carteiraOrigem;
                    $movimentacao->carteira = ($carteira);
                    $movimentacao->chequeBanco = ($carteira->banco);
                    $movimentacao->chequeAgencia = ($carteira->agencia);
                    $movimentacao->chequeConta = ($carteira->conta);

                } else if (in_array($regra->tipoLancto->codigo, [42, 43], true)) {
                    $movimentacao->chequeNumCheque = $numCheque;

                    if ($regra->chequeConta) {
                        $movimentacao->chequeAgencia = ($regra->chequeAgencia);
                        $movimentacao->chequeConta = ($regra->chequeConta);
                        $movimentacao->chequeBanco = ($regra->chequeBanco);
                    } else {
                        $movimentacao->chequeAgencia = ('9999');
                        $movimentacao->chequeConta = ('99999-9');
                        $movimentacao->chequeBanco = (null);
                    }
                }
            }

            $movimentacao->tipoLancto = ($regra->tipoLancto);

            if ($movimentacao->tipoLancto->codigo === 60) {
                $movimentacao->carteiraDestino = ($carteiraDestino);
            }

            $movimentacao->descricao = ($descricao);

            $movimentacao->categoria = $regra->categoria;
            $movimentacao->centroCusto = ($regra->centroCusto);

            $movimentacao->dtMoviment = ($dtVenctoEfetiva);
            $movimentacao->dtVencto = ($dtVenctoEfetiva);

            $movimentacao->status = $regra->status;

            $movimentacao->modo = ($regra->modo);
            $movimentacao->valor = ($valor);
            $movimentacao->valorTotal = ($valor);

            if ($regra->status === 'REALIZADA') {
                $movimentacao->dtPagto = ($dtVenctoEfetiva);
            }

            return $movimentacao;
        }

        // se for pra gerar movimentações que não se encaixem nas regras...
        $movimentacao = new Movimentacao();
        $movimentacao->UUID = (StringUtils::guidv4());
        $movimentacao->carteira = ($this->carteiraExtrato);
        $movimentacao->valor = ($valor);
        $movimentacao->descontos = ($desconto);
        $movimentacao->valorTotal = ($valorTotal);
        $movimentacao->descricao = ($descricao);
        /** @var TipoLancto $realizada */
        $realizada = $this->repoTipoLancto->findOneBy(['codigo' => 20]);
        $movimentacao->tipoLancto = ($realizada);
        $movimentacao->status = ('REALIZADA');
        $movimentacao->modo = ($modo);
        $movimentacao->dtMoviment = ($dtMoviment);
        $movimentacao->dtVencto = ($dtVenctoEfetiva);
        $movimentacao->dtVenctoEfetiva = ($dtVenctoEfetiva);
        $movimentacao->dtPagto = ($dtVenctoEfetiva);
        $movimentacao->bandeiraCartao = ($bandeiraCartao);

        /** @var Categoria $categoria */
        $categoria = null;
        if ($categoriaCodigo) {
            $categoria = $this->repoCategoria->findOneBy(['codigo' => $categoriaCodigo]);
        } else if ($valorNegativo) {
            $categoria = $this->repoCategoria->findOneBy(['codigo' => 2]);
        } else {
            $categoria = $this->repoCategoria->findOneBy(['codigo' => 1]);
        }
        $movimentacao->categoria = ($categoria);

        return $movimentacao;
    }


    /**
     * Verifica se é uma linha normal (DATA DESCRIÇÃO VALOR) ou não.
     * @param $linha
     * @return bool
     */
    private function ehLinhaExtratoSimplesOuSaldo(string $linha): bool
    {
        if (strpos(str_replace(' ', '', $linha), 'SALDO') !== FALSE) {
            return true;
        }
        if (preg_match(StringUtils::PATTERN_DATA, $linha, $matches) && preg_match(StringUtils::PATTERN_MONEY, $linha, $matches)) {
            return true;
        }

        return false;
    }


    private function checkJaImportada(Movimentacao $movimentacao): bool
    {
        if ($movimentacao->getId()) {
            /** @var Movimentacao $movsJaImportada */
            foreach ($this->movsJaImportadas as $movJaImportada) {
                if ($movJaImportada->getId() === $movimentacao->getId()) {
                    return true;
                }
            }
        }
        return false;
    }


}
