<?php

namespace App\Business\Estoque;


use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProdutoBusiness
 * @package App\Business\Estoque
 */
class ProdutoBusiness extends BaseBusiness
{

    /** @var EntityManagerInterface */
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Produto $produto
     */
    public function fillQtdeEmEstoqueComposicao(Produto $produto)
    {
        $menorQtdeDisponivel = null;
        if ($produto->composicao == 'S') {

            $qtdeTotal = 0.0;
            $valorTotal = 0.0;

            foreach ($produto->composicoes as $itemComposicao) {

                $itemComposicao->qtdeEmEstoque = $itemComposicao->produtoFilho->jsonData['qtde_estoque_total'] ?? 0.0;
                $qtdeTotal += $itemComposicao->qtde;
                $valorTotal += $itemComposicao->getTotalComposicao();

                $qtdeDisponivel = $itemComposicao->qtdeEmEstoque >= $itemComposicao->qtde ? bcdiv($itemComposicao->qtdeEmEstoque, $itemComposicao->qtde, 0) : 0;
                $menorQtdeDisponivel = $menorQtdeDisponivel !== null && $menorQtdeDisponivel < $qtdeDisponivel ? $menorQtdeDisponivel : $qtdeDisponivel;

            }
            // dinâmicos...
            $produto->composicaoQtdeTotal = $qtdeTotal;
            $produto->composicaoValorTotal = $valorTotal;
            $produto->composicaoEstoqueDisponivel = $menorQtdeDisponivel;
        }
    }


}