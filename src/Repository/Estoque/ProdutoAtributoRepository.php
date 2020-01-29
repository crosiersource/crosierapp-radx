<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoAtributo;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoAtributoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return ProdutoAtributo::class;
    }

    /**
     * @param Produto $produto
     * @param string $uuid
     * @return ProdutoAtributo|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByAtributoUUID(Produto $produto, string $uuid): ?ProdutoAtributo
    {
        $r = $this->getEntityManager()->getConnection()->fetchAssoc(
            'SELECT pa.id FROM est_produto_atributo pa JOIN est_atributo a ON pa.atributo_id = a.id WHERE pa.produto_id = :produto_id AND a.uuid = :uuid',
            ['produto_id' => $produto->getId(), 'uuid' => $uuid]
        );
        if ($r['id'] ?? false) {
            return $this->find($r['id']);
        }
        return null;
    }


}
