<?php

namespace App\EntityHandler;

use App\Entity\Cargo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * EntityHandler para a entidade Cargo.
 *
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class CargoEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return Cargo::class;
    }
}