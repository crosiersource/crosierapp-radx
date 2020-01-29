<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class BandeiraCartaoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class BandeiraCartaoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return BandeiraCartao::class;
    }
}