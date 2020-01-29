<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoPrecoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return ProdutoPreco::class;
    }
}