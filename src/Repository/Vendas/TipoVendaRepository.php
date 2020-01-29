<?php

namespace App\Repository\Vendas;

use App\Entity\Vendas\TipoVenda;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade TipoVenda.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class TipoVendaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return TipoVenda::class;
    }
}
