<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class CentroCustoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CentroCustoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return CentroCusto::class;
    }
}