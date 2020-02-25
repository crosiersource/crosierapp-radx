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
        if (!$subgrupo->UUID) {
            $subgrupo->UUID = StringUtils::guidv4();
        }
        $subgrupo->jsonData['depto_id'] = $subgrupo->grupo->depto->getId();
        $subgrupo->jsonData['depto_codigo'] = $subgrupo->grupo->depto->codigo;
        $subgrupo->jsonData['depto_nome'] = $subgrupo->grupo->depto->nome;
        $subgrupo->jsonData['grupo_codigo'] = $subgrupo->grupo->codigo;
        $subgrupo->jsonData['grupo_nome'] = $subgrupo->grupo->nome;
    }


}