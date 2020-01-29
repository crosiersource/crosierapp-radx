<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Banco;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class BancoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class BancoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return Banco::class;
    }
}