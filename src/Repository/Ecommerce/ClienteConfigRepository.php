<?php

namespace App\Repository\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use App\Entity\Ecommerce\ClienteConfig;

/**
 * @author Carlos Eduardo Pauluk
 */
class ClienteConfigRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ClienteConfig::class;
    }
}
