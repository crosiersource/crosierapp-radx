<?php

namespace App\Repository\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use App\Entity\Ecommerce\MercadoLivreItem;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivreItemRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return MercadoLivreItem::class;
    }
}
