<?php

namespace App\EntityHandler\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\Base\FornecedorEndereco;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class FornecedorEnderecoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return FornecedorEndereco::class;
    }
}