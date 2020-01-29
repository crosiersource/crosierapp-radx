<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * @author Carlos Eduardo Pauluk
 */
class FornecedorEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Fornecedor::class;
    }
}