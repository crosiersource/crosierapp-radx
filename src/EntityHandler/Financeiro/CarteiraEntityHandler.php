<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class CarteiraEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CarteiraEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return Carteira::class;
    }
}