<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\GrupoAtributo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoAtributoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return GrupoAtributo::class;
    }

    public function beforeSave(/** @var GrupoAtributo $grupoAtributo */ $grupoAtributo)
    {
        if (!$grupoAtributo->getUUID()) {
            $grupoAtributo->setUUID(StringUtils::guidv4());
        }
    }


}