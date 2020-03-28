<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\PedidoCompraItem;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class PedidoCompraItemEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return PedidoCompraItem::class;
    }

    public function beforeSave($item)
    {
        /** @var PedidoCompraItem $item */
        if (!$item->ordem) {
            $ultimaOrdem = 0;
            foreach ($item->pedidoCompra->itens as $item) {
                if ($item->ordem > $ultimaOrdem) {
                    $ultimaOrdem = $item->getOrdem();
                }
            }
            $item->ordem = ($ultimaOrdem + 1);
        }
    }
}