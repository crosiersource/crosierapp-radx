<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\RegraImportacaoLinha;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class RegraImportacaoLinhaEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegraImportacaoLinhaEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return RegraImportacaoLinha::class;
    }
}