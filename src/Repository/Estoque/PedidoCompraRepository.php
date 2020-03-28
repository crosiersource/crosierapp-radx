<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\PedidoCompra;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class PedidoCompraRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return PedidoCompra::class;
    }

}
