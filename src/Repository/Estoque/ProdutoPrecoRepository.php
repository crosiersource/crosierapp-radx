<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoPrecoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ProdutoPreco::class;
    }

    public function findPrecoEmDataVenda(Produto $produto, $dtVenda): ?ProdutoPreco
    {
        $ql = "SELECT pp FROM App\Entity\Estoque\ProdutoPreco pp JOIN App\Entity\Estoque\Produto p WHERE pp.produto = p AND p = :produto AND pp.dtPrecoVenda <= :dtVenda ORDER BY pp.dtPrecoVenda DESC";
        $query = $this->getEntityManager()->createQuery($ql);
        $query->setParameters(array(
            'produto' => $produto,
            'dtVenda' => $dtVenda
        ));
        $query->setMaxResults(1);
        $results = $query->getResult();
        return count($results) >= 1 ? $results[0] : null;
    }
}
