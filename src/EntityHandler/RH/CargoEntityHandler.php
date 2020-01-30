<?php

namespace App\EntityHandler\RH;

use App\Entity\RH\Cargo;
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