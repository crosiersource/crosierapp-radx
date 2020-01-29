<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Grupo.
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Grupo::class;
    }
}
