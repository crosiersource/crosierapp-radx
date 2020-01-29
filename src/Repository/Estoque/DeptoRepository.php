<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Depto;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class DeptoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Depto::class;
    }

}
