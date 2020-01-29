<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\RegistroConferencia;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class RegistroConferenciaEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegistroConferenciaEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return RegistroConferencia::class;
    }
}