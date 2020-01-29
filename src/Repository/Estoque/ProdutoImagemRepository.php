<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\ProdutoImagem;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoImagemRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ProdutoImagem::class;
    }

}
