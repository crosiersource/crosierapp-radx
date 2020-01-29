<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\RegistroConferencia;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade RegistroConferencia.
 *
 * @author Carlos Eduardo Pauluk
 */
class RegistroConferenciaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return RegistroConferencia::class;
    }
}
            