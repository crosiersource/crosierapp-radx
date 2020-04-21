<?php

namespace App\EntityHandler\RH;

use App\Entity\RH\Colaborador;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * EntityHandler para a entidade Colaborador.
 *
 * @package App\EntityHandler\RH
 * @author Carlos Eduardo Pauluk
 */
class ColaboradorEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return Colaborador::class;
    }

    public function beforeSave(/** @var Colaborador $colaborador */ $colaborador)
    {
        $colaborador->cpf = preg_replace("/[^0-9]/", "", $colaborador->cpf);
    }


}