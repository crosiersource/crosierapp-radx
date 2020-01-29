<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Financeiro\Carteira;

/**
 * Repository para a entidade OperadoraCartao.
 *
 * @author Carlos Eduardo Pauluk
 */
class OperadoraCartaoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return OperadoraCartao::class;
    }

    public function handleFrombyFilters(QueryBuilder $qb)
    {
        return $qb->from($this->getEntityClass(), 'e')
            ->join(Carteira::class, 'c', 'WITH', 'e.carteira = c');
    }
}
    