<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\ProdutoComposicao;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoComposicaoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ProdutoComposicao::class;
    }

}
