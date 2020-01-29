<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Grupo::class;
    }

    public function beforeSave(/** @var Grupo $grupo */ $grupo)
    {
        if (!$grupo->getUUID()) {
            $grupo->setUUID(StringUtils::guidv4());
        }
        $grupo->setCodigoDepto($grupo->getDepto()->getCodigo());
        $grupo->setNomeDepto($grupo->getDepto()->getNome());
    }


}