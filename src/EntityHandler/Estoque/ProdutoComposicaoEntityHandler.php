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

    /**
     * @param $produtoComposicao
     * @return mixed|void
     * @throws ViewException
     */
    public function beforeSave(/** @var ProdutoComposicao $produtoComposicao */ $produtoComposicao)
    {
        $composicoes = $produtoComposicao->produtoPai->getComposicoes();
        if (!$produtoComposicao->getId()) {
            /** @var ProdutoComposicao $composicao */
            $i=0;
            foreach ($composicoes as $composicao) {
                if ($composicao->produtoFilho->getId() === $produtoComposicao->produtoFilho->getId()) {
                    $i++;
                }
            }
            if ($i > 1) {
                throw new ViewException('Item já existente na composição');
            }
        }
        if (!$produtoComposicao->ordem) {
            if ($composicoes) {
                /** @var ProdutoComposicao $ultimo */
                $ultimo = $composicoes[0];
                $produtoComposicao->ordem = ($ultimo ? $ultimo->ordem + 1 : 1);
            } else {
                $produtoComposicao->ordem = 1;
            }
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
            $produtoComposicao->ordem = $i++;
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
            $produtoComposicao->ordem = $i++;
            $this->save($produtoComposicao);
        }
    }


}
