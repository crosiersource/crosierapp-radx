<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class CategoriaEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CategoriaEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return Categoria::class;
    }
}