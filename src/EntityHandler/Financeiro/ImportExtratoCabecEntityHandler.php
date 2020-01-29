<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\ImportExtratoCabec;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class ImportExtratoCabec
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ImportExtratoCabecEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return ImportExtratoCabec::class;
    }
}