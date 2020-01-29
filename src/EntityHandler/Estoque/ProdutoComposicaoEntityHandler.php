<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoComposicao;
use App\Repository\Estoque\ProdutoComposicaoRepository;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Psr\Log\LoggerInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoComposicaoEntityHandler extends EntityHandler
{

    /** @var LoggerInterface */
    private $logger;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getEntityClass(): string
    {
        return ProdutoComposicao::class;
    }

    public function beforeSave(/** @var ProdutoComposicao $produtoComposicao */ $produtoComposicao)
    {

        $composicoes = $produtoComposicao->getProdutoPai()->getComposicoes();

        if (!$produtoComposicao->getId()) {
            foreach ($composicoes as $composicao) {
                if ($composicao->getProdutoFilho()->getId() === $produtoComposicao->getProdutoFilho()->getId()) {
                    throw new ViewException('Item já existente na composição');
                }
            }
        }
        if (!$produtoComposicao->getOrdem()) {
            if ($composicoes) {
                /** @var ProdutoComposicao $ultimo */
                $ultimo = $composicoes[0];
                $produtoComposicao->setOrdem($ultimo ? $ultimo->getOrdem() + 1 : 1);
            } else {
                $produtoComposicao->setOrdem(1);
            }
        }

        $sql = 'SELECT pa.valor FROM est_produto_atributo pa JOIN est_atributo a ON pa.atributo_id = a.id WHERE a.uuid = :uuid AND pa.produto_id = :produtoId';
        $pas = $this->getDoctrine()->getConnection()->fetchAll($sql, ['uuid' => 'c22e79c5-4dfd-4506-b3f5-53473f88bf2f', 'produtoId' => $produtoComposicao->getProdutoFilho()->getId()]);
        if ($pas[0]['valor'] ?? false) {
            $produtoComposicao->setPrecoAtual((float)$pas[0]['valor']);
        } else {
            $produtoComposicao->setPrecoAtual(0.0);
        }
    }

    /**
     * @param array $ids
     * @return array
     * @throws ViewException
     */
    public function salvarOrdens(array $ids): array
    {
        /** @var ProdutoComposicaoRepository $repoComposicao */
        $repoProdutoComposicao = $this->getDoctrine()->getRepository(ProdutoComposicao::class);
        $i = 1;
        $ordens = [];
        foreach ($ids as $id) {
            if (!$id) continue;
            /** @var ProdutoComposicao $produtoComposicao */
            $produtoComposicao = $repoProdutoComposicao->find($id);
            $ordens[$id] = $i;
            $produtoComposicao->setOrdem($i++);
            $this->save($produtoComposicao);
        }
        return $ordens;
    }

    /**
     * @param Produto $produtoPai
     * @throws ViewException
     */
    public function reordenar(Produto $produtoPai): void
    {
        $i = 1;

        /** @var ProdutoComposicaoRepository $repoProduto */
        $repoProduto = $this->getDoctrine()->getRepository(ProdutoComposicao::class);
        $composicoes = $repoProduto->findBy(['produtoPai' => $produtoPai]);

        /** @var ProdutoComposicao $produtoComposicao */
        foreach ($composicoes as $produtoComposicao) {
            $produtoComposicao->setOrdem($i++);
            $this->save($produtoComposicao);
        }
    }


}
