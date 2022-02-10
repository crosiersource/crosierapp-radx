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
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\ImportExtratoCabec;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
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


    private ?string $tipoExtrato = null;

    private ?Carteira $carteiraExtrato = null;

    private ?GrupoItem $grupoItem = null;

    private ?bool $gerarSemRegras = true;

    private ?bool $gerarAConferir = true;

    private ?bool $identificarPorCabecalho = false;

    private ?array $arrayCabecalho = null;

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
    }

    /**
     * @throws ViewException
     */
    public function importar($tipoExtrato, $linhasExtrato,
                             Carteira $carteiraExtrato,
                             ?GrupoItem $grupoItem = null,
                             ?bool $gerarSemRegras = true,
                             ?bool $identificarPorCabecalho = false,
                             ?bool $gerarAConferir = true): ?array
    {
        $this->uuidLote = StringUtils::guidv4();
        $this->tipoExtrato = $tipoExtrato;
        $this->stringExtrato = $linhasExtrato;
        $this->carteiraExtrato = $carteiraExtrato;
        $this->grupoItem = $grupoItem;
        $this->gerarSemRegras = $gerarSemRegras;
        $this->gerarAConferir = $gerarAConferir;
        $this->identificarPorCabecalho = $identificarPorCabecalho;

        if ($identificarPorCabecalho) {
            $this->buildArrayCabecalho();
        }

        if ($tipoExtrato === 'MOVS_AGRUPADAS' && !$grupoItem) {
            throw new ViewException('Para extratos de grupos de movimentações, é necessário informar o grupo.');
        }

        $conn = $this->movimentacaoEntityHandler->getDoctrine()->getConnection();

        try {
            $conn->beginTransaction();
            $r = null;
            switch ($tipoExtrato) {
                case 'EXTRATO_SIMPLES':
                    $r = $this->importarExtratoSimples();
                    break;
                case 'EXTRATO_CARTAO':
                    $r = $this->importarExtratoCartao();
                    break;
                case 'MOVS_AGRUPADAS':
                    $r = $this->importarGrupoMovimentacao();
                    break;
                default:
                    throw new ViewException('Tipo de Importação não contemplado');
            }
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


    private function importarExtratoSimples(): ?array
    {
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
            if ($this->identificarPorCabecalho && $this->arrayCabecalho && $i === 0) {
                $linhasNaoImportadas[] = $linha . ' (CABEÇALHO?)';
                // pula o cabeçalho
                continue;
            }

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

        $r['movs'] = $this->movsJaImportadas;

        
        $r['menorData'] = $this->menorDt ? $this->menorDt->format('Y-m-d') : null;
        $r['maiorData'] = $this->maiorDt ? $this->maiorDt->format('Y-m-d') : null;

        return $r;
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
        // else
        if ($this->gerarSemRegras) {
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

        return null;
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


    private function importarGrupoMovimentacao(): array
    {
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
    }


    private function importLinhaExtratoCartao($numLinha)
    {
        // TODO: arrumar
        /**
         * 0 - Data_Transacao
         * 1 - 'MODERNINHA'
         * 2 - Tipo_Pagamento
         * 3 - Transacao_ID
         * 4 - Valor_Bruto
         */
        $linha = trim($this->linhasAImportar[$numLinha]);
        $camposLinha = [];
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


    public function buildArrayCabecalho(): void
    {
        $linhas = explode("\n", $this->stringExtrato);
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
                if (strpos($dePara->camposCabecalho, ',') === FALSE) {
                    $achou = false;
                    foreach ($camposCSV as $key => $campoCSV) {
                        if ($dePara->camposCabecalho === $campoCSV) {
                            $arrayCabecalho[$dePara->campoSistema] = $key;
                            $achou = true;
                            break;
                        }
                    }
                    if (!$achou) {
                        throw new ViewException('Não foi possível montar o array do cabeçalho.');
                    }
                } else {
                    $camposCabecalho = explode(',', $dePara->camposCabecalho);
                    foreach ($camposCabecalho as $campoCabecalho) {
                        $achou = false;
                        foreach ($camposCSV as $key => $campoCSV) {
                            if ($campoCabecalho === $campoCSV) {
                                $arrayCabecalho[$dePara->campoSistema]['campos'][] = $key;
                                $achou = true;
                                break;
                            }
                        }
                        if (!$achou) {
                            throw new ViewException('Não foi possível montar o array do cabeçalho.');
                        }
                    }
                    $arrayCabecalho[$dePara->campoSistema]['formato'] = $dePara->formato;
                }
            }
        }

        $this->arrayCabecalho = $arrayCabecalho;

    }

}
