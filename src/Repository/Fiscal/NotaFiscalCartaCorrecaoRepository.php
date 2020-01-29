<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscalCartaCorrecao;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade NotaFiscalCartaCorrecao.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalCartaCorrecaoRepository extends FilterRepository
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return NotaFiscalCartaCorrecao::class;
    }


}
