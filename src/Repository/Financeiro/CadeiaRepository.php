<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\Cadeia;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Cadeia.
 *
 * @author Carlos Eduardo Pauluk
 */
class CadeiaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Cadeia::class;
    }
    

}
