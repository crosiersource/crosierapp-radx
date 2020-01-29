<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Cadeia;
use App\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class CadeiaEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CadeiaEntityHandler extends EntityHandler
{

    /** @var MovimentacaoEntityHandler */
    private $movimentacaoEntityHandler;

    /**
     * @required
     * @param MovimentacaoEntityHandler $movimentacaoEntityHandler
     */
    public function setMovimentacaoEntityHandler(MovimentacaoEntityHandler $movimentacaoEntityHandler): void
    {
        $this->movimentacaoEntityHandler = $movimentacaoEntityHandler;
    }


    public function getEntityClass()
    {
        return Cadeia::class;
    }

    /**
     *
     * @param Cadeia $cadeia
     * @throws ViewException
     */
    public function deleteCadeiaETodasAsMovimentacoes(Cadeia $cadeia): void
    {
        try {
            $this->doctrine->beginTransaction();
            $movs = $cadeia->getMovimentacoes();
            foreach ($movs as $mov) {
                $this->movimentacaoEntityHandler->delete($mov);
            }
            $this->delete($cadeia);
            $this->doctrine->commit();
        } catch (\Throwable $e) {
            $this->doctrine->rollback();
            $err = $e->getMessage();
            if (isset($mov)) {
                $err .= ' (' . $mov->getDescricao() . ')';
            }
            throw new ViewException($err);
        }
    }

    public function removerCadeiasComApenasUmaMovimentacao(): void
    {
        $rsm = new ResultSetMapping();
        $sql = 'select id, cadeia_id, count(cadeia_id) as qt from fin_movimentacao group by cadeia_id having qt < 2';
        $qry = $this->getDoctrine()->createNativeQuery($sql, $rsm);

        $rsm->addScalarResult('id', 'id');
        $rs = $qry->getResult();
        if ($rs) {
            foreach ($rs as $r) {
                $movimentacao = $this->getDoctrine()->find(Movimentacao::class, $r['id']);
                if ($movimentacao->getCadeia()) {
                    $cadeia = $this->getDoctrine()->find(Cadeia::class, $movimentacao->getCadeia());
                    $movimentacao->setCadeia(null);
                    $this->getDoctrine()->remove($cadeia);
                }
            }
        }
        $this->getDoctrine()->flush();
    }


}