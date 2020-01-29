<?php

namespace App\EntityHandler\Fiscal;

use App\Entity\Fiscal\NotaFiscalVenda;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class NotaFiscalVendaEntityHandler
 * @package App\EntityHandler
 *
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalVendaEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return NotaFiscalVenda::class;
    }
}