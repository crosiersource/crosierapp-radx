<?php

namespace App\Business\Estoque;


use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoAtributo;
use App\Repository\Estoque\ProdutoAtributoRepository;
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProdutoBusiness
 * @package App\Business\Estoque
 */
class ProdutoBusiness extends BaseBusiness
{


    /** @var EntityManagerInterface */
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Produto $produto
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fillQtdeEmEstoqueComposicao(Produto $produto)
    {
        $menorQtdeDisponivel = null;
        if ($produto->getComposicoes()) {

            $qtdeTotal = 0.0;
            $valorTotal = 0.0;

            /** @var ProdutoAtributoRepository $repoProdutoAtributo */
            $repoProdutoAtributo = $this->doctrine->getRepository(ProdutoAtributo::class);
            foreach ($produto->getComposicoes() as $composicao) {

                // 8f25a3e6-cf93-4111-be2b-a46dedc30107	SALDO ESTOQUE TOTAL
                /** @var ProdutoAtributo $produtoAtributo */
                $produtoAtributo = $repoProdutoAtributo->findByAtributoUUID($composicao->getProdutoFilho(), '8f25a3e6-cf93-4111-be2b-a46dedc30107');
                if ($produtoAtributo && $produtoAtributo->getValor()) {
                    $composicao->setQtdeEmEstoque($produtoAtributo->getValor());
                } else {
                    $composicao->setQtdeEmEstoque(0.0);
                }
                $qtdeTotal += $composicao->getQtde();
                $valorTotal += $composicao->getTotalComposicao();


                $qtdeDisponivel = $composicao->getQtdeEmEstoque() >= $composicao->getQtde() ? $composicao->getQtde() : 0;
                $menorQtdeDisponivel = $menorQtdeDisponivel !== null && $menorQtdeDisponivel < $qtdeDisponivel ? $menorQtdeDisponivel : $qtdeDisponivel;

            }
            $produto->composicaoQtdeTotal = $qtdeTotal;
            $produto->composicaoValorTotal = $valorTotal;
            $produto->composicaoEstoqueDisponivel = $menorQtdeDisponivel;
        }
    }


}