<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\FornecedorTipo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class FornecedorTipoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return FornecedorTipo::class;
    }
}