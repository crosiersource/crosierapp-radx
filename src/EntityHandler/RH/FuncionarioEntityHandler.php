<?php

namespace App\EntityHandler\RH;

use App\Entity\RH\Funcionario;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * EntityHandler para a entidade Funcionario.
 *
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class FuncionarioEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return Funcionario::class;
    }
}