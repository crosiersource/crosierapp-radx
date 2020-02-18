<?php

namespace App\Business\Estoque;


use App\Entity\Estoque\Produto;
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
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
        if ($produto->getComposicoes()) {

            $qtdeTotal = 0.0;
            $valorTotal = 0.0;

            foreach ($produto->getComposicoes() as $composicao) {

                $composicao->qtdeEmEstoque = $composicao->produtoFilho->jsonData['qtde_estoque_atual'] ?? 0.0;
                $qtdeTotal += $composicao->getQtde();
                $valorTotal += $composicao->getTotalComposicao();

                $qtdeDisponivel = $composicao->qtdeEmEstoque >= $composicao->qtde ? $composicao->qtde : 0;
                $menorQtdeDisponivel = $menorQtdeDisponivel !== null && $menorQtdeDisponivel < $qtdeDisponivel ? $menorQtdeDisponivel : $qtdeDisponivel;

            }
            // dinâmicos...
            $produto->composicaoQtdeTotal = $qtdeTotal;
            $produto->composicaoValorTotal = $valorTotal;
            $produto->composicaoEstoqueDisponivel = $menorQtdeDisponivel;
        }
    }


}