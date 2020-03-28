<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\PedidoCompra;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class PedidoCompraEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return PedidoCompra::class;
    }
}