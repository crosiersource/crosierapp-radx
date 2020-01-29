<?php

namespace App\Business\Financeiro;

use App\Entity\Financeiro\Banco;
use App\Entity\Financeiro\BandeiraCartao;
use App\Entity\Financeiro\Cadeia;
use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\CentroCusto;
use App\Entity\Financeiro\Grupo;
use App\Entity\Financeiro\GrupoItem;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\OperadoraCartao;
use App\Entity\Financeiro\TipoLancto;
use App\EntityHandler\Financeiro\CadeiaEntityHandler;
use App\EntityHandler\Financeiro\MovimentacaoEntityHandler;
use App\Repository\Financeiro\CarteiraRepository;
use App\Repository\Financeiro\MovimentacaoRepository;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use Doctrine\ORM\EntityManagerInterface;
use NumberFormatter;
use Psr\Log\LoggerInterface;

/**
 * Class MovimentacaoBusiness
 *
 * @package App\Business\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoBusiness
{

    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var GrupoBusiness */
    private $grupoBusiness;

    /** @var MovimentacaoEntityHandler */
    private $movimentacaoEntityHandler;

    /** @var CadeiaEntityHandler */
    private $cadeiaEntityHandler;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @required
     * @param EntityManagerInterface $doctrine
     */
    public function setDoctrine(EntityManagerInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @required
     * @param GrupoBusiness $grupoBusiness
     */
    public function setGrupoBusiness(GrupoBusiness $grupoBusiness): void
    {
        $this->grupoBusiness = $grupoBusiness;
    }

    /**
     * @required
     * @param mixed $movimentacaoEntityHandler
     */
    public function setMovimentacaoEntityHandler(MovimentacaoEntityHandler $movimentacaoEntityHandler): void
    {
        $this->movimentacaoEntityHandler = $movimentacaoEntityHandler;
    }

    /**
     * @required
     * @param mixed $cadeiaEntityHandler
     */
    public function setCadeiaEntityHandler(CadeiaEntityHandler $cadeiaEntityHandler): void
    {
        $this->cadeiaEntityHandler = $cadeiaEntityHandler;
    }

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }


    /**
     * @param $movs
     * @return float|null
     */
    public function somarMovimentacoes($movs): ?float
    {
        $total = 0.0;
        /** @var Movimentacao $m */
        foreach ($movs as $m) {
            $total = $m->getCategoria()->getCodigoSuper() === 1 ? $total + $m->getValorTotal() : $total - $m->getValorTotal();
        }
        return $total;
    }

    /**
     * @param Movimentacao $movimentacao
     * @param int $qtdeParcelas
     * @param float $valor
     * @param \DateTime $dtPrimeiroVencto
     * @param bool $isValorTotal
     * @param array|null $parcelas
     * @throws ViewException
     */
    public function gerarParcelas(Movimentacao $movimentacao, int $qtdeParcelas, float $valor, \DateTime $dtPrimeiroVencto, bool $isValorTotal = true, array $parcelas = null): void
    {
        $valorParcela = $isValorTotal ? bcdiv($valor, $qtdeParcelas, 2) : $valor;
        $resto = $isValorTotal ? bcsub($valor, bcmul($valorParcela, $qtdeParcelas, 2), 2) : 0.0;

        $cadeia = new Cadeia();
        $movimentacao->setCadeia($cadeia);

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);

        if (isset($parcelas[0])) {
            $dtVencto = DateTimeUtils::parseDateStr($parcelas[0]['dtVencto']);
            $dtVenctoEfetiva = DateTimeUtils::parseDateStr($parcelas[0]['dtVenctoEfetiva']);
            $valor = DecimalUtils::parseStr($parcelas[0]['valor']);
            $documentoNum = $parcelas[0]['documentoNum'] ?? null;
            $chequeNumCheque = $parcelas[0]['chequeNumCheque'] ?? null;
            $movimentacao->setDtVencto($dtVencto);
            $movimentacao->setDtVenctoEfetiva($dtVenctoEfetiva);
            $movimentacao->setValor($valor);
            $movimentacao->setDocumentoNum($documentoNum);
            $movimentacao->setChequeNumCheque($chequeNumCheque);
        } else {
            $movimentacao->setDtVencto(clone $dtPrimeiroVencto);
            $proxDiaUtilFinanceiro = $repoDiaUtil->findDiaUtil($movimentacao->getDtVencto(), null, true);
            $movimentacao->setDtVenctoEfetiva($proxDiaUtilFinanceiro);
            $movimentacao->setValor($valorParcela);
        }


        $movimentacao->setCadeiaQtde($qtdeParcelas);
        $movimentacao->setCadeiaOrdem(1);
        $movimentacao->setDescricao(strtoupper($movimentacao->getDescricao()));

        $movimentacao->calcValorTotal();
        $cadeia->getMovimentacoes()->add($movimentacao);

        $proxDtVencto = clone $dtPrimeiroVencto;
        for ($i = 2; $i <= $qtdeParcelas; $i++) {
            $parcela = clone $movimentacao;
            $parcela->setCadeiaOrdem($i);

            // Se foi passado o array com alterações nas parcelas
            if (isset($parcelas[$i - 1])) {
                $dtVencto = DateTimeUtils::parseDateStr($parcelas[$i - 1]['dtVencto']);
                $dtVenctoEfetiva = DateTimeUtils::parseDateStr($parcelas[$i - 1]['dtVenctoEfetiva']);
                $valor = DecimalUtils::parseStr($parcelas[$i - 1]['valor']);
                $documentoNum = $parcelas[$i - 1]['documentoNum'] ?? null;
                $chequeNumCheque = $parcelas[$i - 1]['chequeNumCheque'] ?? null;
                $parcela->setDtVencto($dtVencto);
                $parcela->setDtVenctoEfetiva($dtVenctoEfetiva);
                $parcela->setValor($valor);
                $parcela->setDocumentoNum($documentoNum);
                $parcela->setChequeNumCheque($chequeNumCheque);
            } else {
                $proxDtVencto = DateTimeUtils::incMes(clone $proxDtVencto);
                $parcela->setDtVencto($proxDtVencto);
                $proxDiaUtilFinanceiro = $repoDiaUtil->findDiaUtil($parcela->getDtVencto(), null, true);
                $parcela->setDtVenctoEfetiva($proxDiaUtilFinanceiro);
                if ($i === $qtdeParcelas) {
                    $parcela->setValor(bcadd($valorParcela, $resto, 2));
                }
            }
            $parcela->calcValorTotal();
            $cadeia->getMovimentacoes()->add($parcela);
        }

    }

    /**
     * Salva um parcelamento.
     *
     * @param Movimentacao $primeiraParcela
     * @param $parcelas
     * @return Cadeia
     * @throws \Exception
     */
    public function salvarParcelas(Movimentacao $primeiraParcela, $parcelas): Cadeia
    {
        $this->doctrine->beginTransaction();

        $parcelamento = new Cadeia();
        $this->doctrine->persist($parcelamento);


        $i = 1;
        $valorTotal = 0.0;
        foreach ($parcelas as $parcela) {
            $movimentacao = clone $primeiraParcela;

            $movimentacao->setCadeia($parcelamento);
            $movimentacao->setCadeiaOrdem($i++);

            $valor = (new NumberFormatter('pt_BR', NumberFormatter::DECIMAL))->parse($parcela['valor']);
            $movimentacao->setValor($valor);
            $valorTotal = bcadd($valor, $valorTotal);

            $dtVencto = \DateTime::createFromFormat('d/m/Y', $parcela['dtVencto']);
            $movimentacao->setDtVencto($dtVencto);

            $dtVenctoEfetiva = \DateTime::createFromFormat('d/m/Y', $parcela['dtVenctoEfetiva']);
            $movimentacao->setDtVenctoEfetiva($dtVenctoEfetiva);

            $documentoNum = $parcela['documentoNum'];
            $movimentacao->setDocumentoNum($documentoNum);

            // Em casos de grupos de itens...
            /** @var GrupoItem $giAtual */
            $giAtual = $movimentacao->getGrupoItem();
            if ($giAtual) {
                if ($giAtual->getProximo() !== null) {
                    $proximoId = $giAtual->getProximo()->getId();
                    $giAtual = $this->doctrine->getRepository(Grupo::class)->find($proximoId);
                } else {
                    $giAtual = $this->grupoBusiness->gerarNovo($giAtual->getPai());
                }
                $movimentacao->setGrupoItem($giAtual);
            }

            try {
                $this->movimentacaoEntityHandler->save($movimentacao);
            } catch (\Exception $e) {
                $msg = ExceptionUtils::treatException($e);
                $this->doctrine->rollback();
                throw new ViewException('Erro ao salvar parcelas (' . $msg . ')', 0);
            }
        }

        $this->doctrine->flush();

        $this->doctrine->commit();

        return $parcelamento;


    }

    /**
     * Corrige os valores de OperadoraCartao.
     *
     * @param \DateTime $dtPagto
     * @param Carteira $carteira
     * @return array|string
     * @throws \Exception
     */
    public function corrigirOperadoraCartaoMovimentacoesCartoesDebito(\DateTime $dtPagto, Carteira $carteira)
    {

        $modo = $this->doctrine->getRepository(Modo::class)->findBy(['codigo' => 10]);

        $c101 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 101]);
        $c102 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 102]);
        $c299 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 299]);
        $c199 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 199]);

        /** @var MovimentacaoRepository $repo */
        $repo = $this->doctrine->getRepository(Movimentacao::class);

        $movs = $repo->findByFiltersSimpl(
            [
                ['carteira', 'EQ', $carteira],
                ['dtPagto', 'EQ', $dtPagto->format('Y-m-d')],
                ['modo', 'EQ', $modo],
                ['categoria', 'IN', [$c101, $c102]]
            ], null, 0, -1);

        $results = [];

        /** @var Movimentacao $mov */
        foreach ($movs as $mov) {

            $cadeia = $mov->getCadeia();

            if (!$cadeia) {
                throw new \Exception('Movimentação sem $cadeia.');
            }
            // else

            try {

                /** @var Movimentacao $m299 */
                $m299 = $this->doctrine->getRepository(Movimentacao::class)->findOneBy(['$cadeia' => $cadeia,
                    'categoria' => $c299
                ]);

                /** @var Movimentacao $m199 */
                $m199 = $this->doctrine->getRepository(Movimentacao::class)->findOneBy(['$cadeia' => $cadeia,
                    'categoria' => $c199
                ]);

                $operadoraCartao = null;

                if ($m199->getOperadoraCartao() === null) {
                    /** @var OperadoraCartao $operadoraCartao */
                    $operadoraCartao = $this->doctrine->getRepository(OperadoraCartao::class)->findOneBy(['carteira' => $m199->getCarteira()]);
                    $m199->setOperadoraCartao($operadoraCartao);

                    $m199 = $this->movimentacaoEntityHandler->save($m199);
                    $results[] = 'Operadora corrigida para "' . $m199->getDescricao() . '" - R$ ' . $m199->getValor() . ' (1.99): ' . $operadoraCartao->getDescricao();
                } else {
                    $operadoraCartao = $m199->getOperadoraCartao();
                }

                if ($m299->getOperadoraCartao() === null) {
                    // provavelmente TAMBÉM isso não deveria ser necessário, visto que na importação isto já deve ter sido acertado.
                    $m299->setOperadoraCartao($operadoraCartao);
                    $m299 = $this->movimentacaoEntityHandler->save($m299);
                    $results[] = 'Operadora corrigida para "' . $m299->getDescricao() . '" - R$ ' . $m299->getValor() . ' (2.99): ' . $operadoraCartao->getDescricao();
                }

                if ($mov->getOperadoraCartao() === null) {
                    // provavelmente isso não deveria ser necessário, visto que na importação isto já deve ter sido acertado.
                    $mov->setOperadoraCartao($operadoraCartao);
                    $mov = $this->movimentacaoEntityHandler->save($mov);
                    $results[] = 'Operadora corrigida para "' . $mov->getDescricao() . '" - R$ ' . $mov->getValor() . ' (1.01): ' . $operadoraCartao->getDescricao();
                }

            } catch (\Exception $e) {
                $results[] = 'ERRO: Não foi possível consolidar ' . $mov->getDescricao() . ' - R$ ' . $mov->getValor() . ' (' . $e->getMessage() . ')';
            }
        }

        return $results;
    }

    /**
     * Consolida as movimentações 101 lançadas manualmente com as 199/299 importadas pelo extrato.
     *
     * @param \DateTime $dtPagto
     * @param Carteira $carteira
     * @return array
     * @throws ViewException
     */
    public function consolidarMovimentacoesCartoesDebito(\DateTime $dtPagto, Carteira $carteira): array
    {
        $dtPagto->setTime(0, 0, 0, 0);
        $modo = $this->doctrine->getRepository(Modo::class)->find(10); // RECEB. CARTÃO DÉBITO
        $c101 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 101]);
        $c102 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 102]);

        /** @var MovimentacaoRepository $repo */
        $repo = $this->doctrine->getRepository(Movimentacao::class);

        $movs = $repo->findByFiltersSimpl(
            [
                ['carteira', 'EQ', $carteira],
                ['dtPagto', 'EQ', $dtPagto->format('Y-m-d')],
                ['modo', 'EQ', $modo],
                ['categoria', 'IN', [$c101, $c102]]
            ], null, 0, -1);

        $results = [];

        /** @var Movimentacao $mov */
        $this->movimentacaoEntityHandler->getDoctrine()->beginTransaction();
        foreach ($movs as $mov) {
            try {
                if ($mov->getCadeia() === null) {
                    $results[] = $this->consolidarMovimentacaoDebito($mov, $dtPagto, $carteira);
                }
            } catch (\Exception $e) {
                $results[] = 'ERRO: não foi possível consolidar ' . $mov->getDescricao() . ' - R$ ' . $mov->getValor() . ' (' . $e->getMessage() . ')';
            }
        }
        $this->movimentacaoEntityHandler->getDoctrine()->commit();

        return $results;
    }

    /**
     * Faz a consolidação das movimentações de cartão de débito (após as correspondentes terem sido importadas).
     *
     * @param Movimentacao $m101
     * @param \DateTime $dtMoviment
     * @param Carteira $carteira
     * @return string
     * @throws ViewException
     */
    public function consolidarMovimentacaoDebito(Movimentacao $m101, \DateTime $dtMoviment, Carteira $carteira): string
    {

        /** @var Categoria $c299 */
        $c299 = $this->doctrine->getRepository(Categoria::class)->findBy(['codigo' => 299]);

        // pesquisa movimentação 299 nesta
        // retorna uma lista pois pode encontrar mais de 1

        $m299s = $this->doctrine->getRepository(Movimentacao::class)->findBy([
            'dtMoviment' => $dtMoviment,
            'valorTotal' => $m101->getValor(),
            'carteira' => $carteira,
            'bandeiraCartao' => $m101->getBandeiraCartao(),
            'categoria' => $c299
        ]);

        // Encontra a m299 que faça parte de uma $cadeia com apenas 2 movimentações: 199 e 299 (para evitar de incluir 2 vezes uma 101 na mesma $cadeia).
        $m299 = null;
        /** @var Movimentacao $_m299 */
        foreach ($m299s as $_m299) {
            if ($_m299->getCadeia()->getMovimentacoes()->count() === 2) {
                $m299 = $_m299;
                break;
            }
        }

        if ($m299 === null) {
            $result = 'ERRO: Nenhuma movimentação 2.99 encontrada para "' . $m101->getDescricao() . '" - R$ ' . number_format($m101->getValor(), 2, ',', '.');
            return $result;
        }

        // Incluir na $cadeia
        $m101->setCadeia($m299->getCadeia());
        $m101->setCadeiaOrdem(3);
        $m299->getCadeia()->getMovimentacoes()->add($m101);

        $this->movimentacaoEntityHandler->save($m101);
        // ...para poder atualizar a m299 no entityManager, e dessa forma saber que ela já está em uma $cadeia com 3 movimentações, pulando o if no for acima.

        $result = 'SUCESSO: Movimentação consolidada >> "' . $m101->getDescricao() . '" - R$ ' . number_format($m101->getValor(), 2, ',', '.');

        return $result;
    }

    /**
     * Cálcula a taxa do cartão com base no valor lançado do custo financeiro mensal.
     * @param Carteira $carteira
     * @param $debito
     * @param $totalVendas
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @return float
     */
    public function calcularTaxaCartao(Carteira $carteira, $debito, $totalVendas, \DateTime $dtIni, \DateTime $dtFim): float
    {
        if ($debito) {
            $cCustoOperacionalCartao = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 202005002]);
        } else {
            $cCustoOperacionalCartao = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 202005001]);
        }
        $tCustoOperacionalCartao = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $carteira, $cCustoOperacionalCartao);

        $taxaCartao = 0.0;

        if (($tCustoOperacionalCartao !== null) && ($totalVendas !== null) && ($tCustoOperacionalCartao > 0)
            && ($totalVendas > 0)) {
            $taxaCartao = bcmul(bcdiv($tCustoOperacionalCartao, $totalVendas, 6), 100, 2);
        }
        return $taxaCartao;
    }

    /**
     * Verifica se pode exibir os campos para setar/alterar a recorrência da movimentação.
     * Regras: somente se...
     *  - É um registro novo.
     *  - Ainda não for recorrente.
     *  - É recorrente, mas é a última da $cadeia.
     * @param Movimentacao $movimentacao
     * @return bool
     */
    public function exibirRecorrente(?Movimentacao $movimentacao): bool
    {
        if (!$movimentacao || !$movimentacao->getId() || $movimentacao->getRecorrente() === false) {
            return true;
        }

        $cadeia = $movimentacao->getCadeia();
        return !$cadeia || $cadeia->getMovimentacoes()->last()->getId() === $movimentacao->getId();
    }

    /**
     * Processa um conjunto de movimentações e gera suas recorrentes.
     *
     * @param $movs
     * @return string
     */
    public function processarRecorrentes($movs): ?string
    {
        $this->doctrine->beginTransaction();
        try {
            $results = '';
            $i = 1;
            foreach ($movs as $mov) {
                $results .= $i++ . ' - ' . $this->processarRecorrente($mov) . "\r\n";
            }
            $this->doctrine->commit();
            return $results;
        } catch (\Exception $e) {
            $this->doctrine->rollback();
            throw new \RuntimeException('Erro ao processar recorrentes', 0, $e);
        }
    }

    /**
     * @param Movimentacao $originante
     * @return mixed
     * @throws \Exception
     */
    private function processarRecorrente(Movimentacao $originante)
    {
        $result = '';

        if (!$originante->getRecorrente()) {
            // Tem que ter sido passada uma List com movimentações que sejam recorrentes
            throw new ViewException('Movimentação não recorrente não pode ser processada (' . $originante->getDescricao() . ')');
        }

        if (!$originante->getRecorrFrequencia() || $originante->getRecorrFrequencia() === 'NENHUMA') {
            throw new ViewException('Recorrência com frequência = "NENHUMA" (' . $originante->getDescricao() . ')');
        }
        if (!$originante->getRecorrTipoRepet() || $originante->getRecorrTipoRepet() === 'NENHUMA') {
            throw new ViewException('Recorrência com tipo de repetição = "NENHUMA" (' . $originante->getDescricao() . ')');
        }

        // verifico se já existe a movimentação $posterior
        if ($originante->getCadeia() !== null) {

            $proxMes = (clone $originante->getDtVencto())->add(new \DateInterval('P1M'));
            $dtIni = DateTimeUtils::getPrimeiroDiaMes($proxMes);
            $dtFim = DateTimeUtils::getUltimoDiaMes($proxMes);

            /** @var Movimentacao $posterior */
            $aPosterior = $this->doctrine->getRepository(Movimentacao::class)
                ->findByFiltersSimpl(
                    [['cadeia', 'EQ', $originante->getCadeia()],
                        ['dtVencto', 'BETWEEN', [$dtIni, $dtFim]]]);
            $posterior = $aPosterior[0] ?? null;


            // Só altera uma posterior caso não tenha dtPagto
            if ($posterior) {

                $posterior->setRecorrente(true);
                $posterior->setRecorrDia($originante->getRecorrDia());
                $posterior->setRecorrVariacao($originante->getRecorrVariacao());
                $posterior->setRecorrFrequencia($originante->getRecorrFrequencia());
                $posterior->setRecorrTipoRepet($originante->getRecorrTipoRepet());

                if ($posterior->getDtPagto()) {
                    $result = 'Posterior já realizada. Não será possível alterar: ' . $originante->getDescricao() . '"';
                } // verifico se teve alterações na originante
                else if ($originante->getUpdated()->getTimestamp() > $posterior->getUpdated()->getTimestamp()) {

                    $posterior->setDescricao($originante->getDescricao());

                    $posterior->setValor($originante->getValor());
                    $posterior->setAcrescimos($originante->getAcrescimos());
                    $posterior->setDescontos($originante->getDescontos());
                    $posterior->setValorTotal(null); // null para recalcular no beforeSave

                    $posterior->setSacado($originante->getSacado());
                    $posterior->setCedente($originante->getCedente());

                    $posterior->setCarteira($originante->getCarteira());
                    $posterior->setCategoria($originante->getCategoria());
                    $posterior->setCentroCusto($originante->getCentroCusto());

                    $posterior->setModo($originante->getModo());

                    $this->calcularNovaDtVencto($originante, $posterior);

                }
                try {
                    $this->movimentacaoEntityHandler->save($posterior);
                    $result = 'SUCESSO ao atualizar movimentação: ' . $originante->getDescricao();
                } catch (\Exception $e) {
                    $result = 'ERRO ao atualizar movimentação: ' . $originante->getDescricao() . '. (' . $e->getMessage() . ')';
                }

                return $result;
            }
        }

        $salvarOriginal = false;

        $nova = clone $originante;
        $nova->setUUID(null);

        $nova->setId(null);
        $nova->setDtPagto(null);

        $cadeia = $originante->getCadeia();

        // Se ainda não possui uma $cadeia...
        if ($cadeia !== null) {
            $nova->setCadeiaOrdem($cadeia->getMovimentacoes()->count() + 1);
        } else {
            $cadeia = new Cadeia();

            // Como está sendo gerada uma $cadeia nova, tenho que atualizar a movimentação ||iginal e mandar salva-la também.
            $originante->setCadeiaOrdem(1);
            $originante->setCadeia($cadeia);
            $salvarOriginal = true; // tem que salvar a ||iginante porque ela foi incluída na $cadeia

            $nova->setCadeiaOrdem(2);
        }

        $cadeia->setVinculante(false);
        $cadeia->setFechada(false);

        $nova->setCadeia($cadeia);

        $this->calcularNovaDtVencto($originante, $nova);

        $nova->setStatus('ABERTA'); // posso setar como ABERTA pois no beforeSave(), se for CHEQUE, ele altera para A_COMPENSAR.

        /** @var TipoLancto $aPagarReceber */
        $aPagarReceber = $this->doctrine->getRepository(TipoLancto::class)->findOneBy(['codigo' => 20]);

        $nova->setTipoLancto($aPagarReceber);

        // seto o número do cheque para ????, para que seja informado $posteriormente.
        if ($nova->getChequeNumCheque() !== null) {
            $nova->setChequeNumCheque('????');
        }

        // Tem que salvar a $cadeia, pois foi removido os Cascades devido a outros problemas...

        $this->cadeiaEntityHandler->save($cadeia);

        if ($salvarOriginal) {
            try {
                $this->movimentacaoEntityHandler->save($originante);
                $result .= 'SUCESSO ao salvar movimentação originante: ' . $originante->getDescricao();
            } catch (\Exception $e) {
                $result .= 'ERRO ao salvar movimentação originante: ' . $originante->getDescricao() . '. (' . $e->getMessage() . ')';
            }
            $nova->setCadeia($originante->getCadeia());
        }

        try {
            $this->movimentacaoEntityHandler->save($nova);
            $result .= 'SUCESSO ao gerar movimentação: ' . $nova->getDescricao();
        } catch (\Exception $e) {
            $result .= 'ERRO ao atualizar movimentação: ' . $originante->getDescricao() . '. (' . $e->getMessage() . ')';
        }

        return $result;
    }

    /**
     * @param Movimentacao $originante
     * @param Movimentacao $nova
     * @throws ViewException
     */
    private function calcularNovaDtVencto(Movimentacao $originante, Movimentacao $nova): void
    {
        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);

        $novaDtVencto = clone $originante->getDtVencto();
        if ($nova->getRecorrFrequencia() === 'ANUAL') {
            $novaDtVencto = $novaDtVencto->setDate((int)$novaDtVencto->format('Y') + 1, $novaDtVencto->format('m'), $novaDtVencto->format('d'));
        } else {
            // uso o dia 1 aqui, pois ali embaixo ele vai acertar o dia conforme as outras regras
            $novaDtVencto = $novaDtVencto->setDate($novaDtVencto->format('Y'), (int)$novaDtVencto->format('m') + 1, 1);
        }


        if ($nova->getRecorrTipoRepet() === 'DIA_FIXO') {
            // se foi marcado com dia da recorrência maior ou igual a 31
            // ou se estiver processando fevereiro e a data de vencimento for maior ou igual a 29...
            // então sempre setará para o último dia do mês
            if (($nova->getRecorrDia() >= 31) || ($nova->getRecorrDia() >= 29 && $novaDtVencto->format('m') === 2)) {
                // como já tinha adicionado +1 mês ali em cima, só pega o último dia do mês
                $novaDtVencto = \DateTime::createFromFormat('Y-m-d', $novaDtVencto->format('Y-m-t'));
            } else {
                $novaDtVencto->setDate($novaDtVencto->format('Y'), $novaDtVencto->format('m'), $nova->getRecorrDia());
            }
            $nova->setDtVencto($novaDtVencto);
        } else if ($nova->getRecorrTipoRepet() === 'DIA_UTIL') {
            // Procuro o dia útil ordinalmente...
            $novaDtVencto = $novaDtVencto->setDate($novaDtVencto->format('Y'), (int)$novaDtVencto->format('m'), 01);

            /** @var DiaUtilRepository $repoDiaUtil */
            $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);

            $diaUtil = $repoDiaUtil->findEnesimoDiaUtil($novaDtVencto, $nova->getRecorrDia(), true);
            $nova->setDtVencto($diaUtil);
        }

        $nova->setDtVenctoEfetiva(null);
    }

    /**
     * Verifica se está pedindo para editar uma 1.99. Neste caso, troca para a 2.99.
     * @param Movimentacao $movimentacao
     * @return null
     * @throws \Exception
     */
    public function checkEditTransfPropria(Movimentacao $movimentacao)
    {
        if ($movimentacao->getCategoria() && $movimentacao->getCategoria()->getCodigo() === 199) {

            $categ299 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 299]);
            $cadeia = $movimentacao->getCadeia();
            if ($cadeia === null) {
                throw new ViewException('Movimentação de transferência própria sem cadeia');
            }
            $moviment299 = $this->doctrine->getRepository(Movimentacao::class)->findOneBy(['cadeia' => $cadeia, 'categoria' => $categ299]);
            if (!$moviment299) {
                throw new ViewException('Cadeia de transferência própria já existe, porém sem a 2.99');
            }
            return $moviment299;
        }
        return null;
    }

    /**
     * @param \DateTime $data
     * @param Carteira $carteira
     * @return array
     * @throws \Exception
     */
    public function calcularSaldos(\DateTime $data, Carteira $carteira): array
    {
        $saldos = array();

        /** @var MovimentacaoRepository $movimentacaoRepo */
        $movimentacaoRepo = $this->doctrine->getRepository(Movimentacao::class);
        $saldoPosterior = $movimentacaoRepo->findSaldo($data, $carteira->getId(), 'SALDO_POSTERIOR_REALIZADAS');
        $saldoPosteriorComCheques = $movimentacaoRepo->findSaldo($data, $carteira->getId(), 'SALDO_POSTERIOR_COM_CHEQUES');
        $saldos['SALDO_POSTERIOR_REALIZADAS'] = $saldoPosterior;
        $saldos['SALDO_POSTERIOR_COM_CHEQUES'] = $saldoPosteriorComCheques;
        $saldos['TOTAL_CHEQUES'] = $saldoPosterior - $saldoPosteriorComCheques;

        return $saldos;
    }

    /**
     * Altera as movimentações do lote com o que tiver sido setado em $movComAlteracoes
     *
     * @param array $lote
     * @param Movimentacao $movComAlteracoes
     * @throws ViewException
     */
    public function alterarEmLote(array $lote, Movimentacao $movComAlteracoes): void
    {
        /** @var Movimentacao $mov */
        foreach ($lote as $mov) {

            $this->refindAll($movComAlteracoes);

            if ($movComAlteracoes->getModo()) {
                $mov->setModo($movComAlteracoes->getModo());
            }

            if ($movComAlteracoes->getDocumentoBanco()) {
                $mov->setDocumentoBanco($movComAlteracoes->getDocumentoBanco());
            }

            if ($movComAlteracoes->getDocumentoNum()) {
                $mov->setDocumentoNum($movComAlteracoes->getDocumentoNum());
            }

            if ($movComAlteracoes->getSacado()) {
                $mov->setSacado($movComAlteracoes->getSacado());
            }

            if ($movComAlteracoes->getCedente()) {
                $mov->setCedente($movComAlteracoes->getCedente());
            }

            if ($movComAlteracoes->getQuitado()) {
                $mov->setQuitado($movComAlteracoes->getQuitado());
            }

            if ($movComAlteracoes->getTipoLancto()) {
                $mov->setTipoLancto($movComAlteracoes->getTipoLancto());
            }

            if ($movComAlteracoes->getCarteira()) {
                $mov->setCarteira($movComAlteracoes->getCarteira());
            }

            if ($movComAlteracoes->getCarteiraDestino()) {
                $mov->setCarteiraDestino($movComAlteracoes->getCarteiraDestino());
            }

            if ($movComAlteracoes->getCategoria()) {
                $mov->setCategoria($movComAlteracoes->getCategoria());
            }

            if ($movComAlteracoes->getCentroCusto()) {
                $mov->setCentroCusto($movComAlteracoes->getCentroCusto());
            }

            if ($movComAlteracoes->getGrupoItem()) {
                $mov->setGrupoItem($movComAlteracoes->getGrupoItem());
            }

            if ($movComAlteracoes->getGrupoItem()) {
                $mov->setGrupoItem($movComAlteracoes->getGrupoItem());
            }

            if ($movComAlteracoes->getStatus()) {
                $mov->setStatus($movComAlteracoes->getStatus());
            }

            if ($movComAlteracoes->getDescricao()) {
                $mov->setDescricao($movComAlteracoes->getDescricao());
            }

            if ($movComAlteracoes->getDtMoviment()) {
                $mov->setDtMoviment($movComAlteracoes->getDtMoviment());
            }

            if ($movComAlteracoes->getDtVencto()) {
                $mov->setDtVencto($movComAlteracoes->getDtVencto());
            }

            if ($movComAlteracoes->getDtVenctoEfetiva()) {
                $mov->setDtVenctoEfetiva($movComAlteracoes->getDtVenctoEfetiva());
            }

            if ($movComAlteracoes->getDtPagto()) {
                $mov->setDtPagto($movComAlteracoes->getDtPagto());
            }

            if ($movComAlteracoes->getChequeBanco()) {
                $mov->setChequeBanco($movComAlteracoes->getChequeBanco());
            }

            if ($movComAlteracoes->getChequeAgencia()) {
                $mov->setChequeAgencia($movComAlteracoes->getChequeAgencia());
            }

            if ($movComAlteracoes->getChequeConta()) {
                $mov->setChequeConta($movComAlteracoes->getChequeConta());
            }

            if ($movComAlteracoes->getChequeNumCheque()) {
                $mov->setChequeNumCheque($movComAlteracoes->getChequeNumCheque());
            }

            if ($movComAlteracoes->getOperadoraCartao()) {
                $mov->setOperadoraCartao($movComAlteracoes->getOperadoraCartao());
            }

            if ($movComAlteracoes->getBandeiraCartao()) {
                $mov->setBandeiraCartao($movComAlteracoes->getBandeiraCartao());
            }

            if ($movComAlteracoes->getPlanoPagtoCartao()) {
                $mov->setPlanoPagtoCartao($movComAlteracoes->getPlanoPagtoCartao());
            }

            if ($movComAlteracoes->getRecorrente()) {
                $mov->setRecorrente($movComAlteracoes->getRecorrente());
            }

            if ($movComAlteracoes->getRecorrDia()) {
                $mov->setRecorrDia($movComAlteracoes->getRecorrDia());
            }

            if ($movComAlteracoes->getRecorrFrequencia()) {
                $mov->setRecorrFrequencia($movComAlteracoes->getRecorrFrequencia());
            }

            if ($movComAlteracoes->getRecorrTipoRepet()) {
                $mov->setRecorrTipoRepet($movComAlteracoes->getRecorrTipoRepet());
            }

            if ($movComAlteracoes->getRecorrVariacao()) {
                $mov->setRecorrVariacao($movComAlteracoes->getRecorrVariacao());
            }

            if ($movComAlteracoes->getValor()) {
                $mov->setValor($movComAlteracoes->getValor());
            }

            if ($movComAlteracoes->getDescontos()) {
                $mov->setDescontos($movComAlteracoes->getDescontos());
            }

            if ($movComAlteracoes->getAcrescimos()) {
                $mov->setAcrescimos($movComAlteracoes->getAcrescimos());
            }

            if ($movComAlteracoes->getValorTotal()) {
                $mov->setValorTotal($movComAlteracoes->getValorTotal());
            }

            if ($movComAlteracoes->getObs()) {
                $mov->setObs($movComAlteracoes->getObs());
            }
        }
    }

    /**
     * @param Movimentacao $movimentacao
     * @throws ViewException
     */
    public function refindAll(Movimentacao $movimentacao): void
    {
        try {
            $em = $this->doctrine;

            if ($movimentacao->getCategoria() && $movimentacao->getCategoria()->getId()) {
                /** @var Categoria $categoria */
                $categoria = $em->find(Categoria::class, $movimentacao->getCategoria()->getId());
                $movimentacao->setCategoria($categoria);
            }
            if ($movimentacao->getTipoLancto() && $movimentacao->getTipoLancto()->getId()) {
                /** @var TipoLancto $tipoLancto */
                $tipoLancto = $em->find(TipoLancto::class, $movimentacao->getTipoLancto()->getId());
                $movimentacao->setTipoLancto($tipoLancto);
            }
            if ($movimentacao->getCarteira() && $movimentacao->getCarteira()->getId()) {
                /** @var Carteira $carteira */
                $carteira = $em->find(Carteira::class, $movimentacao->getCarteira()->getId());
                $movimentacao->setCarteira($carteira);
            }
            if ($movimentacao->getCarteiraDestino() && $movimentacao->getCarteiraDestino()->getId()) {
                /** @var Carteira $carteiraDestino */
                $carteiraDestino = $em->find(Carteira::class, $movimentacao->getCarteiraDestino()->getId());
                $movimentacao->setCarteiraDestino($carteiraDestino);
            }
            if ($movimentacao->getCentroCusto() && $movimentacao->getCentroCusto()->getId()) {
                /** @var CentroCusto $centroCusto */
                $centroCusto = $em->find(CentroCusto::class, $movimentacao->getCentroCusto()->getId());
                $movimentacao->setCentroCusto($centroCusto);
            }
            if ($movimentacao->getModo() && $movimentacao->getModo()->getId()) {
                /** @var Modo $modo */
                $modo = $em->find(Modo::class, $movimentacao->getModo()->getId());
                $movimentacao->setModo($modo);
            }
            if ($movimentacao->getGrupoItem() && $movimentacao->getGrupoItem()->getId()) {
                /** @var GrupoItem $grupoItem */
                $grupoItem = $em->find(GrupoItem::class, $movimentacao->getGrupoItem()->getId());
                $movimentacao->setGrupoItem($grupoItem);
            }
            if ($movimentacao->getOperadoraCartao() && $movimentacao->getOperadoraCartao()->getId()) {
                /** @var OperadoraCartao $operadoraCartao */
                $operadoraCartao = $em->find(OperadoraCartao::class, $movimentacao->getOperadoraCartao()->getId());
                $movimentacao->setOperadoraCartao($operadoraCartao);
            }
            if ($movimentacao->getBandeiraCartao() && $movimentacao->getBandeiraCartao()->getId()) {
                /** @var BandeiraCartao $bandeiraCartao */
                $bandeiraCartao = $em->find(BandeiraCartao::class, $movimentacao->getBandeiraCartao()->getId());
                $movimentacao->setBandeiraCartao($bandeiraCartao);
            }
            if ($movimentacao->getCadeia() && $movimentacao->getCadeia()->getId()) {
                /** @var Cadeia $cadeia */
                $cadeia = $em->find(Cadeia::class, $movimentacao->getCadeia()->getId());
                $movimentacao->setCadeia($cadeia);
            }
            if ($movimentacao->getDocumentoBanco() && $movimentacao->getDocumentoBanco()->getId()) {
                /** @var Banco $documentoBanco */
                $documentoBanco = $em->find(Banco::class, $movimentacao->getDocumentoBanco()->getId());
                $movimentacao->setDocumentoBanco($documentoBanco);
            }
            if ($movimentacao->getChequeBanco() && $movimentacao->getChequeBanco()->getId()) {
                /** @var Banco $chequeBanco */
                $chequeBanco = $em->find(Banco::class, $movimentacao->getChequeBanco()->getId());
                $movimentacao->setChequeBanco($chequeBanco);
            }
        } catch (\Exception $e) {
            throw new ViewException('Erro ao realizar o refindAll');
        }
    }

    /**
     * Monta os <option> para campo carteira.
     * FIXME: corrigir para o padrão do autoSelect2 do Crosier.
     *
     * @param $params
     * @return null|string
     * @throws ViewException
     */
    public function getFilterCarteiraOptions($params): ?string
    {
        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->doctrine->getRepository(Carteira::class);
        $carteiras = $repoCarteira->findByFiltersSimpl([['atual', 'EQ', true]], ['e.codigo' => 'ASC'], 0, -1);

        $param = null;

        foreach ($params as $p) {
            if ($p->field[0] === 'e.carteira') {
                $param = $p;
                break;
            }
        }
        if (!$param) {
            return null;
        }

        $str = '';
        $selected = '';
        /** @var Carteira $carteira */
        foreach ($carteiras as $carteira) {
            if ($param->val) {
                if ($carteira->getId() === (int)$param->val) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            }
            $str .= '<option value="' . $carteira->getId() . '" ' . $selected . '>' . $carteira->getCodigo(true) . ' - ' . $carteira->getDescricao() . '</option>';
        }
        return $str;
    }


    /**
     * "Paga" uma movimentação aberta com outra já realizada e em seguida exclui esta.
     * @param Movimentacao $aberta
     * @param Movimentacao $realizada
     */
    public function pagarAbertaComRealizada(Movimentacao $aberta, Movimentacao $realizada)
    {
        try {
            $this->movimentacaoEntityHandler->getDoctrine()->beginTransaction();
            $aberta->setDtPagto($realizada->getDtPagto());
            $aberta->setCarteira($realizada->getCarteira());
            $aberta->setCedente($aberta->getCedente() ?? $realizada->getCedente());
            $aberta->setSacado($aberta->getSacado() ?? $realizada->getSacado());

            $aberta->setValor($realizada->getValor());
            $aberta->setDescontos($realizada->getDescontos());
            $aberta->setAcrescimos($realizada->getAcrescimos());
            $aberta->setValorTotal($realizada->getValorTotal());

            $this->movimentacaoEntityHandler->save($aberta);
            $this->movimentacaoEntityHandler->delete($realizada);
            $this->movimentacaoEntityHandler->getDoctrine()->commit();
        } catch (\Exception $e) {
            $this->logger->error('Erro ao pagarAbertaComRealizada. aberta.id: ' . $aberta->getId() . '. realizada.id: ' . $realizada->getId());
            $this->movimentacaoEntityHandler->getDoctrine()->rollback();
        }
    }

    /**
     *
     * @throws ViewException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function corrigirDtVenctos(): void
    {
        $qry = $this->movimentacaoEntityHandler
            ->getDoctrine()
            ->createQuery("SELECT m FROM App\Entity\Financeiro\Movimentacao m WHERE m.dtPagto IS NULL AND (m.dtVencto != m.dtVenctoEfetiva OR m.dtUtil != m.dtVencto OR m.dtUtil != m.dtVenctoEfetiva)");
        $rs = $qry->getResult();

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);

        /** @var Movimentacao $mov */
        foreach ($rs as $mov) {
            $dtVenctoEfetivaCerta = $repoDiaUtil->findDiaUtil($mov->getDtVencto(), null, true);
            $dtVenctoEfetivaCerta->setTime(0, 0, 0, 0);
            $mov->setDtVenctoEfetiva($dtVenctoEfetivaCerta);
            $mov->setDtUtil($dtVenctoEfetivaCerta);
        }
        $this->movimentacaoEntityHandler->getDoctrine()->flush();
    }


}