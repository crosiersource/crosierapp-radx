<?php

namespace App\Repository\Estoque;

use App\Entity\Estoque\DepreciacaoPreco;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class DepreciacaoPrecoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return DepreciacaoPreco::class;
    }

    /**
     * @param int $prazo
     * @return float|null
     */
    public function findDepreciacaoByPrazo(int $prazo): ?float
    {

        try {
            $dql = 'SELECT dp FROM App\Entity\Estoque\DepreciacaoPreco dp WHERE dp.prazoIni <= :prazo AND dp.prazoFim >= :prazo';
            $qry = $this->getEntityManager()->createQuery($dql);
            $qry->setParameter('prazo', $prazo);
            $dp = $qry->getSingleResult();
            /** @var DepreciacaoPreco */
            if ($dp instanceof DepreciacaoPreco) {
                return $dp->getPorcentagem();
            }
            return null;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao consultar - findDepreciacaoByPrazo');
        }
    }
}
