<?php

namespace App\EntityHandler\Fiscal;

use App\Entity\Fiscal\NotaFiscalHistorico;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class NotaFiscalHistoricoEntityHandler
 *
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalHistoricoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return NotaFiscalHistorico::class;
    }
}