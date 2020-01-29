<?php

namespace App\Repository\Estoque;

use App\Entity\Estoque\ProdutoSaldo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoSaldoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ProdutoSaldo::class;
    }
}
