<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class GrupoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Grupo::class;
    }

}
