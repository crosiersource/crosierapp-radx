<?php

namespace App\Repository\Estoque;


use CrosierSource\CrosierLibBaseBundle\Entity\Base\FornecedorContato;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class FornecedorContatoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return FornecedorContato::class;
    }

}
