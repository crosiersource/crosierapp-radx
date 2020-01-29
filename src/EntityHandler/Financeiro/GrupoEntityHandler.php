<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class GrupoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return Grupo::class;
    }


}