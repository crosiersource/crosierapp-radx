<?php

namespace App\EntityHandler\Vendas;

use App\Entity\Vendas\Venda;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class VendaEntityHandler
 * @package App\EntityHandler\Vendas
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Venda::class;
    }
}