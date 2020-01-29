<?php

namespace App\EntityHandler\Financeiro;

use App\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class OperadoraCartaoEntityHandler
 *
 * @package App\EntityHandler\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class OperadoraCartaoEntityHandler extends EntityHandler
{


    public function getEntityClass()
    {
        return OperadoraCartao::class;
    }
}