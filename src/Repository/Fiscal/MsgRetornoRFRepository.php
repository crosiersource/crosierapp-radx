<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\MsgRetornoRF;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade MsgRetornoRF.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class MsgRetornoRFRepository extends FilterRepository
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return MsgRetornoRF::class;
    }
}
