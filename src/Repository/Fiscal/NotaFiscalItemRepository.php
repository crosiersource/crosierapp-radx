<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscalItem;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade NotaFiscalItem.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalItemRepository extends FilterRepository
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return NotaFiscalItem::class;
    }


}
