<?php

namespace App\EntityHandler\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use App\Entity\Ecommerce\MercadoLivreItem;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivreItemEntityHandler extends EntityHandler
{


    /**
     * @param MercadoLivreItem $mercadoLivreItem
     */
    public function beforeSave($mercadoLivreItem)
    {
        if (!$mercadoLivreItem->UUID) {
            $mercadoLivreItem->UUID = StringUtils::guidv4();
        }
    }

    public function getEntityClass(): string
    {
        return MercadoLivreItem::class;
    }

}
