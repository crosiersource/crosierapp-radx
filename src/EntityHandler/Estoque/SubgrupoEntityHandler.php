<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class SubgrupoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Subgrupo::class;
    }

    public function beforeSave(/** @var Subgrupo $subgrupo */ $subgrupo)
    {
        if (!$subgrupo->getUUID()) {
            $subgrupo->setUUID(StringUtils::guidv4());
        }
        $subgrupo->setDepto($subgrupo->getGrupo()->getDepto());
        $subgrupo->setCodigoDepto($subgrupo->getGrupo()->getDepto()->getCodigo());
        $subgrupo->setNomeDepto($subgrupo->getGrupo()->getDepto()->getNome());
        $subgrupo->setCodigoGrupo($subgrupo->getGrupo()->getCodigo());
        $subgrupo->setNomeGrupo($subgrupo->getGrupo()->getNome());
    }


}