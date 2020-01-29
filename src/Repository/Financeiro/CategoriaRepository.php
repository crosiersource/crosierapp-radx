<?php

namespace App\Repository\Financeiro;

use App\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Banco.
 *
 * @author Carlos Eduardo Pauluk
 */
class CategoriaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Categoria::class;
    }

    public function buildTreeList()
    {
        $sql = "SELECT id, codigo, concat(rpad('', 2*(length(codigo)-1),'.'), codigo, ' - ',  descricao) as descricaoMontada FROM fin_categoria ORDER BY codigo_ord";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }


}
