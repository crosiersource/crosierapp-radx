<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\UnidadeProduto;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade UnidadeProduto.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class UnidadeProdutoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return UnidadeProduto::class;
    }
}
