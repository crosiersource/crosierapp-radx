<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class ModoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ModoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return Modo::class;
    }
}