<?php

namespace App\EntityHandler\Vendas;

use App\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class VendaItemEntityHandler
 * @package App\EntityHandler\Vendas
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaItemEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return VendaItem::class;
    }
}