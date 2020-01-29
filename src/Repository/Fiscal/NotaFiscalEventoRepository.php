<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscalEvento;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade NotaFiscalEvento.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalEventoRepository extends FilterRepository
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return NotaFiscalEvento::class;
    }


}
