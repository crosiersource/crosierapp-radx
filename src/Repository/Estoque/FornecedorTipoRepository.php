<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\FornecedorTipo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class FornecedorTipoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return FornecedorTipo::class;
    }
}
