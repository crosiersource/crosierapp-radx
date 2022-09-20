<?php

namespace App\EntityHandler\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use App\Entity\Ecommerce\ClienteConfig;

/**
 * @author Carlos Eduardo Pauluk
 */
class ClienteConfigEntityHandler extends EntityHandler
{


    /**
     * @param ClienteConfig $clienteConfig
     */
    public function beforeSave($clienteConfig)
    {
        if (!$clienteConfig->UUID) {
            $clienteConfig->UUID = StringUtils::guidv4();
        }
    }

    public function getEntityClass(): string
    {
        return ClienteConfig::class;
    }

}
