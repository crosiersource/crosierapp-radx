<?php

namespace App\EntityHandler\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use App\Entity\Ecommerce\TrayVenda;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayVendaEntityHandler extends EntityHandler
{


    /**
     * @param TrayVenda $trayVenda
     */
    public function beforeSave($trayVenda)
    {
        if (!$trayVenda->UUID) {
            $trayVenda->UUID = StringUtils::guidv4();
        }
    }

    public function getEntityClass(): string
    {
        return TrayVenda::class;
    }

}
