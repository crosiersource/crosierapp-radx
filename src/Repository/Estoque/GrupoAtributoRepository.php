<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\GrupoAtributo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class GrupoAtributoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return GrupoAtributo::class;
    }

}
