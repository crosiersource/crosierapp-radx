<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Depto;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class DeptoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Depto::class;
    }

    public function beforeSave(/** @var Depto $depto */ $depto)
    {
        if (!$depto->getUUID()) {
            $depto->setUUID(StringUtils::guidv4());
        }
    }

}