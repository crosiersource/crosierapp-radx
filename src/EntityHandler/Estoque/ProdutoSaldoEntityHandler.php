<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\ProdutoSaldo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoSaldoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return ProdutoSaldo::class;
    }
}