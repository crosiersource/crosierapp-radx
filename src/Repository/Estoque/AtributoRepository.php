<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Atributo;
use App\Entity\Estoque\Produto;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class AtributoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Atributo::class;
    }

    public function fillSubatributos(Atributo $atributo)
    {
        $atributo->setSubatributos($this->findBy(['paiUUID' => $atributo->getUUID()], ['ordem' => 'ASC']));
    }

    /**
     * @param Produto $produto
     * @return mixed
     */
    public function getAtributosNotInProduto(?Produto $produto)
    {
        if ($produto) {
            $qry = $this->getEntityManager()
                ->createQuery('SELECT a FROM App\Entity\Estoque\Atributo a JOIN App\Entity\Estoque\Produto p WHERE p.id = :produtoId AND a.primaria = \'S\' AND a.id NOT IN (:idsAtributosProduto) ORDER BY a.label');
            $idsAtributosProduto = [];
            foreach ($produto->getAtributos() as $atributoProduto) {
                $idsAtributosProduto[] = $atributoProduto->getAtributo()->getId();
            }
            $qry->setParameter('idsAtributosProduto', $idsAtributosProduto);
            $qry->setParameter('produtoId', $produto->getId());
            return $qry->getResult();
        }
        return $this->findBy(['primaria' => 'S'], ['label' => 'ASC']);

    }

}
