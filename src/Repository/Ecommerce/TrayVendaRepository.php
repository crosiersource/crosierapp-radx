<?php

namespace App\Repository\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use App\Entity\Ecommerce\TrayVenda;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayVendaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return TrayVenda::class;
    }
}
