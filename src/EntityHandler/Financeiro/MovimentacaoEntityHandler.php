<?php

namespace App\EntityHandler\Financeiro;

use App\Business\Financeiro\MovimentacaoBusiness;
use App\Entity\Financeiro\Cadeia;
use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\CentroCusto;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\TipoLancto;
use App\Repository\Financeiro\TipoLanctoRepository;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\PessoaRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

/**
 * Class MovimentacaoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoEntityHandler extends EntityHandler
{

    /** @var MovimentacaoBusiness */
    private $movimentacaoBusiness;

    /** @var CadeiaEntityHandler */
    private $cadeiaEntityHandler;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Descrição das regras em http://docs.crosier.com.br/books/finan/page/regras-para-movimenta%C3%A7%C3%B5es/edit
     *
     *
     * @param $movimentacao
     * @return Movimentacao
     * @throws ViewException
     */
    public function beforeSave($movimentacao)
    {
        /** @var Movimentacao $movimentacao */
        if (!$movimentacao->getTipoLancto()) {
            throw new ViewException('Campo "Tipo de Lançamento" precisa ser informado');
        }
        if (!$movimentacao->getCarteira()) {
            throw new ViewException('Campo "Carteira" precisa ser informado');
        }
        if (!$movimentacao->getModo()) {
            throw new ViewException('Campo "Modo" precisa ser informado');
        }
        if (!$movimentacao->getCategoria()) {
            throw new ViewException('Campo "Categoria" precisa ser informado');
        }
        if ('' === trim($movimentacao->getDescricao())) {
            throw new ViewException('Campo "Descrição" precisa ser informado');
        }

        if (!$movimentacao->getUUID()) {
            $movimentacao->setUUID(StringUtils::guidv4());
        }

        if (!$movimentacao->getCentroCusto()) {
            /** @var CentroCusto $centroCusto */
            $centroCusto = $this->doctrine->getRepository(CentroCusto::class)->find(1); // 1,'GLOBAL'
            $movimentacao->setCentroCusto($centroCusto);
        }


        /** @var TipoLanctoRepository $repoTipoLancto */
        $repoTipoLancto = $this->doctrine->getRepository(TipoLancto::class);

        // Regras Gerais
        $movimentacao->setDescricao(trim($movimentacao->getDescricao()));

        if ($movimentacao->getModo()->getCodigo() === 50) { // 50,'MOVIMENTAÇÃO AGRUPADA'
            if (!$movimentacao->getGrupoItem()) {
                throw new ViewException('Campo "Grupo Item" precisa ser informado');
            }

            $movimentacao->setDtVencto($movimentacao->getGrupoItem()->getDtVencto());
            $movimentacao->setDtVenctoEfetiva($movimentacao->getGrupoItem()->getDtVencto());
            $movimentacao->setDtPagto($movimentacao->getGrupoItem()->getDtVencto());

            /** @var Carteira $carteiraMovsAgrupadas */
            $carteiraMovsAgrupadas = $this->doctrine->getRepository(Carteira::class)->findOneBy(['codigo' => 7]); // 7 ('MOVIMENTAÇÕES AGRUPADAS')
            $movimentacao->setCarteira($carteiraMovsAgrupadas);
        }


        if ($movimentacao->getCarteira()->getCaixa()) {
            $movimentacao->setDtPagto($movimentacao->getDtMoviment());
            $movimentacao->setDtVencto($movimentacao->getDtMoviment());
            $movimentacao->setDtVenctoEfetiva($movimentacao->getDtMoviment());
        }

        if ($movimentacao->getTipoLancto()->getCodigo() === 10 && !$movimentacao->getCarteira()->getCaixa()) {
            throw new ViewException('Movimentações de caixa só podem ser lançadas em carteiras de caixas.');
        }

        // Para 10 - MOVIMENTAÇÃO DE CAIXA e 60 - TRANSFERÊNCIA ENTRE CARTEIRAS
        if (in_array($movimentacao->getTipoLancto()->getCodigo(), [10, 60], true)) {
            if (!$movimentacao->getDtVencto()) {
                $movimentacao->setDtVencto($movimentacao->getDtMoviment());
            }
            $movimentacao->setDtPagto($movimentacao->getDtMoviment());
        }


        // Regras para Datas
        if (!$movimentacao->getDtPagto()) {
            $movimentacao->setStatus('ABERTA');
        } else {
            $movimentacao->setStatus('REALIZADA');
            if (!$movimentacao->getDtVencto()) {
                $movimentacao->setDtVencto($movimentacao->getDtPagto());
            }
            if (!$movimentacao->getDtMoviment()) {
                $movimentacao->setDtMoviment($movimentacao->getDtPagto());
            }
        }

        if (!$movimentacao->getDtVencto()) {
            throw new ViewException('Campo "Dt Vencto" precisa ser informado');
        }
        if (!$movimentacao->getDtMoviment()) {
            throw new ViewException('Campo "Dt Moviment" precisa ser informado');
        }
        if (!$movimentacao->getDtVenctoEfetiva()) {
            /** @var DiaUtilRepository $repoDiaUtil */
            $repoDiaUtil = $this->doctrine->getRepository(DiaUtil::class);
            $proxDiaUtilFinanceiro = $repoDiaUtil->findDiaUtil($movimentacao->getDtVencto(), null, true);
            $movimentacao->setDtVenctoEfetiva($proxDiaUtilFinanceiro);
        }
        $movimentacao->setDtUtil($movimentacao->getDtPagto() ?? $movimentacao->getDtVenctoEfetiva());


        // Por enquanto...
        if (!$movimentacao->getQuitado()) {
            $movimentacao->setQuitado($movimentacao->getStatus() === 'REALIZADA');
        }


        // Regras para valores
        $movimentacao->setValor($movimentacao->getValor() ? abs($movimentacao->getValor()) : 0);
        $movimentacao->setDescontos($movimentacao->getDescontos() ? (-1 * abs($movimentacao->getDescontos())) : 0);
        $movimentacao->setAcrescimos($movimentacao->getAcrescimos() ? abs($movimentacao->getAcrescimos()) : 0);
        $movimentacao->calcValorTotal();


        // Regras para Status
        if ($movimentacao->getStatus() === 'REALIZADA') {
            if (!$movimentacao->getCarteira()->getConcreta()) {
                throw new ViewException('Somente carteiras concretas podem conter movimentações com status "REALIZADA"');
            }
            if ($movimentacao->getModo()->getCodigo() === 99 && !in_array($movimentacao->getCategoria()->getCodigo(), [195, 295], true)) {
                throw new ViewException('Não é possível salvar uma movimentação com status "REALIZADA" em modo 99 (INDEFINIDO)');
            }
        } else { // if ($movimentacao->getStatus() === 'ABERTA') {
            if (!$movimentacao->getCarteira()->getAbertas()) {
                throw new ViewException('Esta carteira não pode conter movimentações com status "ABERTA".');
            }
        }

        // Regras para Movimentações de Grupos
        if (in_array($movimentacao->getTipoLancto()->getCodigo(), [70, 71], true)) { // 70,'MOVIMENTAÇÃO DE GRUPO'
            /** @var Modo $modo50 */
            $modo50 = $this->doctrine->getRepository(Modo::class)->findOneBy(['codigo' => 50]);
            $movimentacao->setModo($modo50);
        } else if ($movimentacao->getModo()->getCodigo() === 50) { // 50,'MOVIMENTAÇÃO AGRUPADA'
            /** @var TipoLancto $tipoLancto70 */
            $tipoLancto70 = $repoTipoLancto->findOneBy(['codigo' => 70]);
            $movimentacao->setTipoLancto($tipoLancto70);
        }

        // Regras para movimentações de cartões
        if (FALSE === $movimentacao->getModo()->getModoDeCartao()) {
            $movimentacao->setPlanoPagtoCartao(null);
            $movimentacao->setBandeiraCartao(null);
            $movimentacao->setOperadoraCartao(null);
        } else {
            if ($movimentacao->getCarteira()->getOperadoraCartao()) {
                // $this->doctrine->refresh($movimentacao->getCarteira()->getOperadoraCartao());
                $movimentacao->setOperadoraCartao($movimentacao->getCarteira()->getOperadoraCartao());
            }
            if ($movimentacao->getBandeiraCartao()) {

                if (!trim($movimentacao->getDescricao())) {
                    $movimentacao->setDescricao($movimentacao->getBandeiraCartao()->getDescricao());
                }

                if ($movimentacao->getBandeiraCartao()->getModo()->getId() !== $movimentacao->getModo()->getId()) {
                    throw new ViewException(
                        vsprintf(
                            'Bandeira de cartão selecionada para o modo %s (%s), porém a movimentação foi informada como sendo %s',
                            [$movimentacao->getBandeiraCartao()->getModo()->getDescricao(),
                                $movimentacao->getBandeiraCartao()->getDescricao(),
                                $movimentacao->getModo()->getDescricao()]));
                }
            }
        }

        $movimentacao->setParcelamento($movimentacao->getCadeia() &&
            !$movimentacao->getRecorrente() &&
            !$movimentacao->isTransferenciaEntradaCaixa() &&
            !$movimentacao->isTransferenciaEntreCarteiras());


        // Regras para movimentações com cheque
        if (FALSE === $movimentacao->getModo()->getModoDeCheque()) {
            $movimentacao->setChequeNumCheque(null);
            $movimentacao->setChequeAgencia(null);
            $movimentacao->setChequeBanco(null);
            $movimentacao->setChequeConta(null);
        }

        if (in_array($movimentacao->getTipoLancto()->getCodigo(), [40, 41], true)) {
            $movimentacao->setChequeAgencia($movimentacao->getCarteira()->getAgencia());
            $movimentacao->setChequeBanco($movimentacao->getCarteira()->getBanco());
            $movimentacao->setChequeConta($movimentacao->getCarteira()->getConta());
        }

        // Regras para movimentações recorrentes
        if (!$movimentacao->getRecorrente()) {
            $movimentacao->setRecorrente(false);
        }

        /** @var PessoaRepository $repoPessoa */
        $repoPessoa = $this->doctrine->getRepository(Pessoa::class);

        if ($movimentacao->getSacado()) {
            /** @var Pessoa $pessoa */
            $pessoa = $repoPessoa->find($movimentacao->getSacado());
            $movimentacao->setSacadoInfo($pessoa->getNome());
        }

        if ($movimentacao->getCedente()) {
            /** @var Pessoa $pessoa */
            $pessoa = $repoPessoa->find($movimentacao->getCedente());
            $movimentacao->setCedenteInfo($pessoa->getNome());
        }


        // Trava para Dt Consolidado
        if ($movimentacao->getDtPagto()) {
            $dtPagto = (clone $movimentacao->getDtPagto())->setTime(0, 0);
            $dtConsolidado_carteira = (clone $movimentacao->getCarteira()->getDtConsolidado())->setTime(0, 0);
            if ($dtPagto <= $dtConsolidado_carteira) {
                throw new ViewException('Carteira ' . $movimentacao->getCarteira()->getDescricao() . ' está consolidada em ' . $movimentacao->getCarteira()->getDtConsolidado()->format('d/m/Y'));
            }
            if ($movimentacao->getCarteiraDestino()) {
                $dtConsolidado_carteiraDestino = (clone $movimentacao->getCarteira()->getDtConsolidado())->setTime(0, 0);
                if ($dtPagto <= $dtConsolidado_carteiraDestino) {
                    throw new ViewException('Carteira ' . $movimentacao->getCarteiraDestino()->getDescricao() . ' está consolidada em ' . $movimentacao->getCarteiraDestino()->getDtConsolidado()->format('d/m/Y'));
                }
            }
        }

        return $movimentacao;
    }

    /**
     * @param array|ArrayCollection $movs
     * @param bool $todasNaMesmaCadeia
     * @throws ViewException
     */
    public function saveAll($movs, bool $todasNaMesmaCadeia = false): void
    {
        try {
            $this->doctrine->beginTransaction();

            $cadeia = null;
            if ($todasNaMesmaCadeia) {
                /** @var Movimentacao $primeira */
                foreach ($movs as $key => $primeira) {
                    // RTA para pegar o primeiro elemento do array
                    break;
                }

                $cadeia = null;
                if ($primeira->getCadeia() && !$primeira->getCadeia()->getId()) {
                    $cadeia = $primeira->getCadeia();
                    $cadeia->setMovimentacoes(null);
                    $this->cadeiaEntityHandler->save($cadeia);
                }
            }
            /** @var Movimentacao $mov */
            foreach ($movs as $mov) {
                if ($cadeia) {
                    $mov->setCadeia($cadeia);
                }
                $this->movimentacaoBusiness->refindAll($mov);
                $this->save($mov);
                $this->doctrine->clear();
            }
            $this->doctrine->commit();
        } catch (ViewException | \Throwable $e) {
            $this->logger->error('Erro no saveAll()');
            $this->logger->error($e->getMessage());
            $this->doctrine->clear();
            $this->doctrine->rollback();
            $err = 'Erro ao salvar movimentações';
            if ($e instanceof ViewException) {
                $err = $e->getMessage();
            }
            if (isset($mov)) {
                $err .= ' (' . $mov->getDescricao() . ')';
            }
            throw new ViewException($err);
        }
    }

    /**
     * Tratamento diferenciado para cada tipoLancto.
     *
     * @param EntityId $movimentacao
     * @param bool $flush
     * @return \CrosierSource\CrosierLibBaseBundle\Entity\EntityId|Movimentacao|EntityId|null|object
     * @throws ViewException
     */
    public function save(EntityId $movimentacao, $flush = true)
    {
        /** @var Movimentacao $movimentacao */
        if (!$movimentacao->getTipoLancto()) {
            throw new ViewException('Tipo Lancto não informado para ' . $movimentacao->getDescricaoMontada());
        }

        // 60 - TRANSFERÊNCIA ENTRE CARTEIRAS
        if ($movimentacao->getTipoLancto()->getCodigo() === 60) {
            return $this->saveTransfPropria($movimentacao);
        }

        // 61 - TRANSFERÊNCIA DE ENTRADA DE CAIXA
        if ($movimentacao->getTipoLancto()->getCodigo() === 61) {
            return $this->saveTransfEntradaCaixa($movimentacao);
        }
        // else
        return parent::save($movimentacao);

    }

    /**
     * Salva uma transferência entre carteiras.
     *
     * A $movimentacao passada não deverá ser 199 ou 299.
     *
     * @param Movimentacao $movimentacao
     * @return Movimentacao
     * @throws ViewException
     */
    public function saveTransfPropria(Movimentacao $movimentacao): Movimentacao
    {

        $this->getDoctrine()->beginTransaction();
        /** @var Categoria $categ299 */
        $categ299 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 299]);
        /** @var Categoria $categ199 */
        $categ199 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 199]);

        if (!in_array($movimentacao->getCategoria()->getCodigo(), [199, 299], true)) {
            throw new ViewException('Apenas movimentações 1.99 ou 2.99 podem ser salvas.');
        }

        $categOposta = $movimentacao->getCategoria()->getCodigo() === 199 ? $categ299 : $categ199;

        // Está editando
        if ($movimentacao->getId()) {

            if ($movimentacao->getCadeia() && $movimentacao->getCadeia()->getMovimentacoes() &&
                $movimentacao->getCadeia()->getMovimentacoes()->count() !== 2) {
                throw new ViewException('Apenas cadeias com 2 odem ser editadas ("TRANSFERÊNCIA ENTRE CARTEIRAS")');
            }

            $movimentOposta = $this->getDoctrine()->getRepository(Movimentacao::class)
                ->findOneBy(
                    [
                        'cadeia' => $movimentacao->getCadeia(),
                        'categoria' => $categOposta
                    ]);

            // Campos que podem ser editados
            $movimentOposta->setDescricao($movimentacao->getDescricao());
            $movimentOposta->setCategoria($categOposta);
            $movimentOposta->setModo($movimentacao->getModo());
            if ($movimentacao->getCarteiraDestino()) {
                $movimentOposta->setCarteira($movimentacao->getCarteiraDestino());
            }
            $movimentOposta->setCarteiraDestino($movimentacao->getCarteira());
            $movimentOposta->setValor($movimentacao->getValor());
            $movimentOposta->setValorTotal($movimentacao->getValorTotal());
            $movimentOposta->setCentroCusto($movimentacao->getCentroCusto());
            $movimentOposta->setDtMoviment($movimentacao->getDtMoviment());
            $movimentOposta->setDtVencto($movimentacao->getDtVencto());
            $movimentOposta->setDtVenctoEfetiva($movimentacao->getDtVenctoEfetiva());
            $movimentOposta->setDtPagto($movimentacao->getDtPagto());

            /** @var Movimentacao $movimentOposta */
            $movimentOposta = parent::save($movimentOposta);

            /** @var Movimentacao $movimentacao */
            $movimentacao->setCarteiraDestino($movimentOposta->getCarteira());
            $movimentacao = parent::save($movimentacao);
            $this->getDoctrine()->commit();
            return $movimentacao;
        }
        // else

        $cadeia = new Cadeia();
        $cadeia->setVinculante(true);
        $cadeia->setFechada(true);
        /** @var Cadeia $cadeia */
        $cadeia = $this->cadeiaEntityHandler->save($cadeia);

        $cadeiaOrdem = $movimentacao->getCategoria()->getCodigo() === 299 ? 1 : 2;
        $movimentacao->setCadeia($cadeia);
        $movimentacao->setCadeiaOrdem($cadeiaOrdem);
        $movimentacao->setCadeiaQtde(2);

        $cadeiaOrdemOposta = $movimentacao->getCategoria()->getCodigo() === 299 ? 2 : 1;

        /** @var Movimentacao $movimentOposta */
        $movimentOposta = new Movimentacao();
        $movimentOposta->setCadeia($cadeia);
        $movimentOposta->setCadeiaOrdem($cadeiaOrdemOposta);
        $movimentOposta->setCadeiaQtde(2);
        $movimentOposta->setDescricao($movimentacao->getDescricao());
        $movimentOposta->setCategoria($categOposta);
        $movimentOposta->setCentroCusto($movimentacao->getCentroCusto());
        $movimentOposta->setModo($movimentacao->getModo());
        $movimentOposta->setCarteira($movimentacao->getCarteiraDestino());
        $movimentOposta->setCarteiraDestino($movimentacao->getCarteira());
        $movimentOposta->setStatus('REALIZADA');
        $movimentOposta->setValor($movimentacao->getValor());
        $movimentOposta->setDescontos($movimentacao->getDescontos());
        $movimentOposta->setAcrescimos($movimentacao->getAcrescimos());
        $movimentOposta->setValorTotal($movimentacao->getValorTotal());

        $movimentOposta->setDtMoviment($movimentacao->getDtMoviment());
        $movimentOposta->setDtVencto($movimentacao->getDtVencto());
        $movimentOposta->setDtVenctoEfetiva($movimentacao->getDtVenctoEfetiva());
        $movimentOposta->setDtPagto($movimentacao->getDtPagto());

        $movimentOposta->setTipoLancto($movimentacao->getTipoLancto());

        parent::save($movimentOposta);
        parent::save($movimentacao);
        $this->getDoctrine()->commit();

        return $movimentacao;
    }


    /**
     * Salva uma transferência de entrada de caixa. Uma cadeia com 3 movimentações:
     * 101 - na carteira do caixa
     * 299 - na carteira do caixa
     * 199 - na carteira destino
     *
     * @param Movimentacao $movimentacao
     * @return Movimentacao
     * @throws ViewException
     */
    public function saveTransfEntradaCaixa(Movimentacao $movimentacao): Movimentacao
    {
        $this->getDoctrine()->beginTransaction();

        /** @var Categoria $categ299 */
        $categ299 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 299]);
        /** @var Categoria $categ199 */
        $categ199 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 199]);

        // Está editando
        if ($movimentacao->getId()) {
            if ($movimentacao->getCadeia()->getMovimentacoes()->count() !== 3) {
                throw new ViewException('Apenas cadeias com 3 movimentações podem ser editadas (TRANSFERÊNCIA DE ENTRADA DE CAIXA).');
            }

            $movs = $movimentacao->getCadeia()->getMovimentacoes();
            $outraMov = null;
            foreach ($movs as $mov) {
                if ($mov->getId() !== $movimentacao->getId()) {
                    $mov->setDescricao($movimentacao->getDescricao());
                    $mov->setCategoria($categ299);
                    $mov->setModo($movimentacao->getModo());
                    $mov->setValor($movimentacao->getValor());
                    $mov->setValorTotal($movimentacao->getValorTotal());
                    $mov->setCentroCusto($movimentacao->getCentroCusto());
                    $mov->setDtMoviment($movimentacao->getDtMoviment());
                    $mov->setDtVencto($movimentacao->getDtVencto());
                    $mov->setDtVenctoEfetiva($movimentacao->getDtVenctoEfetiva());
                    $mov->setDtPagto($movimentacao->getDtPagto());
                    $mov->setCadeiaQtde(3);
                    /** @var Movimentacao $mov */
                    parent::save($mov);
                }
            }

            /** @var Movimentacao $movimentacao */
            $movimentacao = parent::save($movimentacao);

            $this->getDoctrine()->commit();
            return $movimentacao;
        }
        // else

        if (!in_array($movimentacao->getCategoria()->getCodigo(), [101, 102], true)) {
            throw new ViewException('TRANSFERÊNCIA DE ENTRADA DE CAIXA precisa ser lançada a partir de uma movimentação de categoria 1.01 ou 1.02');

        }

        $cadeia = new Cadeia();
        $cadeia->setVinculante(true);
        $cadeia->setFechada(true);
        /** @var Cadeia $cadeia */
        $cadeia = $this->cadeiaEntityHandler->save($cadeia);

        $movimentacao->setCadeia($cadeia);
        $movimentacao->setCadeiaOrdem(1);

        /** @var Movimentacao $moviment299 */
        $moviment299 = new Movimentacao();
        $moviment299->setTipoLancto($movimentacao->getTipoLancto());
        $moviment299->setCadeia($cadeia);
        $moviment299->setCadeiaOrdem(2);
        $moviment299->setCadeiaQtde(3);
        $moviment299->setDescricao($movimentacao->getDescricao());
        $moviment299->setCategoria($categ299);
        $moviment299->setCentroCusto($movimentacao->getCentroCusto());
        $moviment299->setModo($movimentacao->getModo());
        $moviment299->setCarteira($movimentacao->getCarteira());
        $moviment299->setCarteiraDestino($movimentacao->getCarteiraDestino());
        $moviment299->setStatus('REALIZADA');
        $moviment299->setValor($movimentacao->getValor());
        $moviment299->setDescontos($movimentacao->getDescontos());
        $moviment299->setAcrescimos($movimentacao->getAcrescimos());
        $moviment299->setValorTotal($movimentacao->getValorTotal());

        $moviment299->setDtMoviment($movimentacao->getDtMoviment());
        $moviment299->setDtVencto($movimentacao->getDtMoviment());
        $moviment299->setDtVenctoEfetiva($movimentacao->getDtMoviment());
        $moviment299->setDtPagto($movimentacao->getDtMoviment());

        $moviment299->setTipoLancto($movimentacao->getTipoLancto());
        parent::save($moviment299);

        /** @var Movimentacao $moviment199 */
        $moviment199 = new Movimentacao();
        $moviment199->setTipoLancto($movimentacao->getTipoLancto());
        $moviment199->setCadeia($cadeia);
        $moviment199->setCadeiaOrdem(3);
        $moviment199->setCadeiaQtde(3);
        $moviment199->setDescricao($movimentacao->getDescricao());
        $moviment199->setCategoria($categ199);
        $moviment199->setCentroCusto($movimentacao->getCentroCusto());
        $moviment199->setModo($movimentacao->getModo());
        $moviment199->setCarteira($movimentacao->getCarteiraDestino());
        $moviment199->setCarteiraDestino($movimentacao->getCarteira());
        $moviment199->setStatus('REALIZADA');
        $moviment199->setValor($movimentacao->getValor());
        $moviment199->setDescontos($movimentacao->getDescontos());
        $moviment199->setAcrescimos($movimentacao->getAcrescimos());
        $moviment199->setValorTotal($movimentacao->getValorTotal());

        $moviment199->setDtMoviment($movimentacao->getDtMoviment());
        $moviment199->setDtVencto($movimentacao->getDtMoviment());
        $moviment199->setDtVenctoEfetiva($movimentacao->getDtMoviment());
        $moviment199->setDtPagto($movimentacao->getDtMoviment());

        $moviment199->setTipoLancto($movimentacao->getTipoLancto());
        parent::save($moviment199);

        parent::save($movimentacao);
        $this->getDoctrine()->commit();

        return $movimentacao;
    }

    /**
     * Tratamento para casos de movimentação em cadeia.
     * @param $movimentacao
     * @throws ViewException
     */
    public function delete($movimentacao)
    {
        /** @var Movimentacao $movimentacao */

        if ($movimentacao->getCadeia() && $movimentacao->getCadeia()->getMovimentacoes()) {
            if ($movimentacao->getCadeia()->getMovimentacoes()->count() === 2 || $movimentacao->getCadeia()->getMovimentacoes()->count() === 3) {
                /** @var Movimentacao $movimentacao0 */
                $movimentacao0 = $movimentacao->getCadeia()->getMovimentacoes()->current();
                if (in_array($movimentacao0->getTipoLancto()->getCodigo(), [60, 61], true)) {
                    $cadeia = $movimentacao->getCadeia();
                    foreach ($cadeia->getMovimentacoes() as $m) {
                        parent::delete($m);
                    }
                    return;
                }
            }
        }
        // else
        parent::delete($movimentacao);

    }

    /**
     * @required
     * @param mixed $movimentacaoBusiness
     */
    public function setMovimentacaoBusiness(MovimentacaoBusiness $movimentacaoBusiness): void
    {
        $this->movimentacaoBusiness = $movimentacaoBusiness;
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


    public function getEntityClass()
    {
        return Movimentacao::class;
    }


}