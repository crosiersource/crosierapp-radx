<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class GrupoItemEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoItemEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return GrupoItem::class;
    }
}