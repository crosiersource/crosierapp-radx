<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade NotaFiscalHistorico.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalHistoricoRepository extends FilterRepository
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return NotaFiscal::class;
    }
}
