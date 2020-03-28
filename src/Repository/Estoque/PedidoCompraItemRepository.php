<?php

namespace App\Repository\Estoque;

use App\Entity\Estoque\PedidoCompraItem;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class PedidoCompraItemRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return PedidoCompraItem::class;
    }
}
