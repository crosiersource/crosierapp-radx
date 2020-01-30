<?php

namespace App\Repository\RH;

use App\Entity\RH\Funcionario;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Funcionario.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class FuncionarioRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Funcionario::class;
    }

    public function findByCodigo($codigo)
    {
        $ql = "SELECT f FROM App\Entity\RH\Funcionario f JOIN App\Entity\RH\FuncionarioCargo fc WHERE fc.funcionario = f AND f.codigo = :codigo AND fc.atual = TRUE";
        $query = $this->getEntityManager()->createQuery($ql);
        $query->setParameters(array(
            'codigo' => $codigo
        ));

        $results = $query->getResult();

        if (count($results) > 1) {
            throw new \Exception('Mais de um funcionario encontrado para [' . $codigo . '] com atual = true');
        }

        return count($results) == 1 ? $results[0] : null;
    }


}
