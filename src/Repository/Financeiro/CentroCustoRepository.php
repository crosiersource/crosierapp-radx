<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade CentroCusto.
 *
 * @author Carlos Eduardo Pauluk
 */
class CentroCustoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return CentroCusto::class;
    }
}
