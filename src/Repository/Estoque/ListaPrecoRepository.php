<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\ListaPreco;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ListaPrecoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ListaPreco::class;
    }

}
