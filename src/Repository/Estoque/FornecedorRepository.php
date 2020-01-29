<?php

namespace App\Repository\Estoque;

use App\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class FornecedorRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Fornecedor::class;
    }


}
