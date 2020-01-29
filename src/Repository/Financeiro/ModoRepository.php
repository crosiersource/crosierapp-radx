<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Modo.
 *
 * @author Carlos Eduardo Pauluk
 */
class ModoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Modo::class;
    }
}
