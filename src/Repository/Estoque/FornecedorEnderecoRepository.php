<?php

namespace App\Repository\Estoque;


use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class FornecedorEnderecoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return FornecedorEndereco::class;
    }

}
