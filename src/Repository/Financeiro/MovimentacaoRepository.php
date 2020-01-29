<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\CentroCusto;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Repository para a entidade Movimentacao.
 *
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoRepository extends FilterRepository
{


    public function getEntityClass(): string
    {
        return Movimentacao::class;
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder|void
     */
    public function handleFrombyFilters(QueryBuilder $qb)
    {
        return $qb->from($this->getEntityClass(), 'e')
            ->join(Carteira::class, 'cart', 'WITH', 'e.carteira = cart')
            ->join(Categoria::class, 'categ', 'WITH', 'e.categoria = categ')
            ->join(CentroCusto::class, 'cc', 'WITH', 'e.centroCusto = cc')
            ->join(Modo::class, 'modo', 'WITH', 'e.modo = modo');
    }

    /**
     * @param DateTime $dtVenctoEfetiva
     * @param Carteira $carteira
     * @return mixed
     * @throws ViewException
     */
    public function findAbertasAnteriores(DateTime $dtVenctoEfetiva, Carteira $carteira)
    {
        $dtVenctoEfetivaS = $dtVenctoEfetiva->setTime(0, 0, 0, 0)->format('Y-m-d');
        $filterDatas = [
            (new FilterData())->setField(['dtVenctoEfetiva'])->setFilterType('LT')->setVal($dtVenctoEfetivaS),
            (new FilterData())->setField(['carteira'])->setFilterType('EQ')->setVal($carteira),
            (new FilterData())->setField(['status'])->setFilterType('EQ')->setVal('ABERTA')
        ];
        $orders =
            [
                'e.dtVenctoEfetiva' => 'asc',
                'e.valorTotal' => 'asc'
            ];
        return $this->findByFilters($filterDatas, $orders, 0, 0);
    }

    /**
     * @param DateTime $dtSaldo
     * @param $carteirasIds
     * @param $tipoSaldo
     * @return mixed
     * @throws Exception
     */
    public function findSaldo(DateTime $dtSaldo, $carteirasIds, $tipoSaldo)
    {
        $ql = 'SELECT SUM( IF (categ.codigo_super=1,m.valor_total,m.valor_total*-1) ) as valor_total ' .
            'FROM fin_movimentacao m, fin_modo modo, fin_categoria categ ' .
            'WHERE ' .
            'm.modo_id = modo.id AND ' .
            'm.categoria_id = categ.id AND ' .
            'm.carteira_id IN (:carteirasIds) AND ' .
            '( ' .
            '(m.status = "REALIZADA" AND m.dt_pagto <= :dtSaldo) ';


        if (in_array($tipoSaldo, ['SALDO_ANTERIOR_REALIZADAS', 'SALDO_ANTERIOR_COM_CHEQUES'])) {
            $dtSaldo->sub(new DateInterval('P1D'));
        }

        if (in_array($tipoSaldo, ['SALDO_POSTERIOR_COM_CHEQUES', 'SALDO_ANTERIOR_COM_CHEQUES'])) {
            $ql .= 'OR (m.status = "ABERTA" AND modo.codigo IN (3,4) AND (m.dt_vencto_efetiva <= :dtSaldo OR m.dt_pagto > :dtSaldo))';
        }

        if ($tipoSaldo === 'SALDO_POSTERIOR_REALIZADAS_SEM_DEBITOS') {
            $ql .= 'AND (modo.codigo != 10)';
        }

        $ql .= ')';

        if (strpos($tipoSaldo, 'POSTERIOR') !== FALSE) {
            $dtSaldo->setTime(23, 59, 59, 99999);
        } else {
            $dtSaldo->setTime(0, 0, 0, 00000);
        }

        $carteirasIds = is_array($carteirasIds) ? $carteirasIds : array($carteirasIds);

        $rsm = new ResultSetMapping ();
        $qry = $this->getEntityManager()->createNativeQuery($ql, $rsm);
        $qry->setParameter('dtSaldo', $dtSaldo);
        $qry->setParameter('carteirasIds', $carteirasIds);
        $qry->getSQL();
        $qry->getParameters();
        $rsm->addScalarResult('valor_total', 'valor_total');
        $r = $qry->getResult();

        return $r[0]['valor_total'];
    }

    /**
     * @param DateTime $dtIni
     * @param DateTime $dtFim
     * @param Carteira|null $carteira
     * @param Categoria|null $categoria
     * @param Modo|null $modo
     * @param OperadoraCartao|null $operadoraCartao
     * @return float|null
     */
    public function findTotal(DateTime $dtIni, DateTime $dtFim, ?Carteira $carteira = null, ?Categoria $categoria = null, ?Modo $modo = null, ?OperadoraCartao $operadoraCartao = null)
    {
        $dtIni->setTime(0, 0, 0, 0);
        $dtFim->setTime(23, 59, 59, 999999);

        $params = [];
        $params['dtIni'] = $dtIni;
        $params['dtFim'] = $dtFim;

        $ql = 'SELECT SUM( m.valor_total ) as valor_total FROM fin_movimentacao m WHERE m.dt_pagto BETWEEN :dtIni AND :dtFim';

        if ($carteira) {
            $ql .= ' AND m.carteira_id = :carteiraId';
            $params['carteiraId'] = $carteira->getId();
        }
        if ($categoria) {
            $ql .= ' AND m.categoria_id = :categoriaId';
            $params['categoriaId'] = $categoria->getId();
        }
        if ($modo) {
            $ql .= ' AND m.modo_id = :modoId';
            $params['modoId'] = $modo->getId();
        }
        if ($operadoraCartao) {
            $ql .= ' AND m.cadeia_id IN (SELECT cadeia_id FROM fin_movimentacao WHERE cadeia_id = m.cadeia_id AND operadora_cartao_id = :operadoraCartaoId)';
            $params['operadoraCartaoId'] = $operadoraCartao->getId();
        }

        $rsm = new ResultSetMapping ();
        $qry = $this->getEntityManager()->createNativeQuery($ql, $rsm);
        $qry->setParameters($params);
        $qry->getSQL();
        $qry->getParameters();
        $rsm->addScalarResult('valor_total', 'valor_total');
        $r = $qry->getResult();
        if ($r) {
            return (float)$r[0]['valor_total'];
        } else {
            return null;
        }
    }

    /**
     * @param Carteira $carteira
     * @param DateTime $dtIni
     * @param DateTime $dtFim
     * @return array
     * @throws ViewException
     */
    public function findTotaisExtratoCartoes(Carteira $carteira, \DateTime $dtIni, \DateTime $dtFim)
    {

        try {
            /** @var Connection $conn */
            $conn = $this->getEntityManager()->getConnection();
            $dtIni = $dtIni->format('Y-m-d');
            $dtFim = $dtFim->format('Y-m-d');
            $totalCreditos = $conn->fetchAssoc('SELECT sum(valor_total) as total FROM fin_movimentacao WHERE modo_id = 9 AND dt_pagto BETWEEN :dtIni AND :dtFim AND carteira_id = :carteiraId', ['dtIni' => $dtIni, 'dtFim' => $dtFim, 'carteiraId' => $carteira->getId()]);
            $totalCustoCreditos = $conn->fetchAssoc('SELECT sum(valor_total) as total FROM fin_movimentacao WHERE dt_pagto BETWEEN :dtIni AND :dtFim AND carteira_id = :carteiraId AND categoria_id = 58', ['dtIni' => $dtIni, 'dtFim' => $dtFim, 'carteiraId' => $carteira->getId()]);
            $totalDebitos = $conn->fetchAssoc('SELECT sum(valor_total) as total FROM fin_movimentacao WHERE modo_id = 10 AND dt_pagto BETWEEN :dtIni AND :dtFim AND carteira_id = :carteiraId', ['dtIni' => $dtIni, 'dtFim' => $dtFim, 'carteiraId' => $carteira->getId()]);
            $totalCustoDebitos = $conn->fetchAssoc('SELECT sum(valor_total) as total FROM fin_movimentacao WHERE dt_pagto BETWEEN :dtIni AND :dtFim AND carteira_id = :carteiraId AND categoria_id = 59', ['dtIni' => $dtIni, 'dtFim' => $dtFim, 'carteiraId' => $carteira->getId()]);
            $totalTransfParaConta = $conn->fetchAssoc('SELECT sum(valor_total) as total FROM fin_movimentacao WHERE categoria_id = 7 AND dt_pagto BETWEEN :dtIni AND :dtFim AND carteira_id = :carteiraId', ['dtIni' => $dtIni, 'dtFim' => $dtFim, 'carteiraId' => $carteira->getId()]);

            return [
                'totalCreditos' => $totalCreditos['total'] ?? 0.0,
                'totalCustoCreditos' => $totalCustoCreditos['total'] ?? 0.0,
                'taxaCreditos' => bcdiv($totalCustoCreditos['total'] ?? 0.0, $totalCreditos['total'] ?? 1, 4) * 100.0,
                'totalDebitos' => $totalDebitos['total'] ?? 0.0,
                'totalCustoDebitos' => $totalCustoDebitos['total'] ?? 0.0,
                'taxaDebitos' => bcdiv($totalCustoDebitos['total'] ?? 0.0, $totalDebitos['total'] ?? 1, 4) * 100.0,
                'totalTransfParaConta' => $totalTransfParaConta['total'] ?? 0.0,
                'totalGeral' => $totalCreditos['total'] + $totalDebitos['total'] ?? 0.0,
            ];
        } catch (DBALException | \Throwable $e) {
            throw new ViewException('Erro ao calcular totais para extrato de cartão');
        }


    }


}
