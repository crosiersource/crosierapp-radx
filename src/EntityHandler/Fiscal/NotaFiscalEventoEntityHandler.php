<?php

namespace App\EntityHandler\Fiscal;

use App\Entity\Fiscal\NotaFiscalEvento;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class NotaFiscalEventoEntityHandler
 *
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalEventoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return NotaFiscalEvento::class;
    }
}