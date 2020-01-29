<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ProdutoComposicaoRepository")
 * @ORM\Table(name="est_produto_composicao")
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoComposicao implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_pai_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Produto
     */
    private $produtoPai;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_filho_id", nullable=false)
     *
     * @Groups("entity")
     *
     * @var null|Produto
     */
    private $produtoFilho;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     * @var null|integer
     */
    private $ordem;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $qtde;

    /**
     *
     * @ORM\Column(name="preco_atual", type="decimal", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    private $precoAtual;

    /**
     * Transient.
     * @Groups("entity")
     *
     * @var null|float
     */
    private $qtdeEmEstoque;

    /**
     *
     * @ORM\Column(name="preco_composicao", type="decimal", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    private $precoComposicao;

    /**
     * Transient.
     *
     * @var null|float
     */
    private $totalAtual;

    /**
     * Transient.
     *
     * @var null|float
     */
    private $totalComposicao;

    /**
     * @return Produto|null
     */
    public function getProdutoPai(): ?Produto
    {
        return $this->produtoPai;
    }

    /**
     * @param Produto|null $produtoPai
     * @return ProdutoComposicao
     */
    public function setProdutoPai(?Produto $produtoPai): ProdutoComposicao
    {
        $this->produtoPai = $produtoPai;
        return $this;
    }

    /**
     * @return Produto|null
     */
    public function getProdutoFilho(): ?Produto
    {
        return $this->produtoFilho;
    }

    /**
     * @param Produto|null $produtoFilho
     * @return ProdutoComposicao
     */
    public function setProdutoFilho(?Produto $produtoFilho): ProdutoComposicao
    {
        $this->produtoFilho = $produtoFilho;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdem(): ?int
    {
        return $this->ordem;
    }

    /**
     * @param int|null $ordem
     * @return ProdutoComposicao
     */
    public function setOrdem(?int $ordem): ProdutoComposicao
    {
        $this->ordem = $ordem;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getQtde(): ?float
    {
        return $this->qtde;
    }

    /**
     * @param float|null $qtde
     * @return ProdutoComposicao
     */
    public function setQtde(?float $qtde): ProdutoComposicao
    {
        $this->qtde = $qtde;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrecoAtual(): ?string
    {
        return $this->precoAtual;
    }

    /**
     * @param string|null $precoAtual
     * @return ProdutoComposicao
     */
    public function setPrecoAtual(?string $precoAtual): ProdutoComposicao
    {
        $this->precoAtual = $precoAtual;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getQtdeEmEstoque(): ?float
    {
        return $this->qtdeEmEstoque;
    }

    /**
     * @param float|null $qtdeEmEstoque
     * @return ProdutoComposicao
     */
    public function setQtdeEmEstoque(?float $qtdeEmEstoque): ProdutoComposicao
    {
        $this->qtdeEmEstoque = $qtdeEmEstoque;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrecoComposicao(): ?string
    {
        return $this->precoComposicao;
    }

    /**
     * @param string|null $precoComposicao
     * @return ProdutoComposicao
     */
    public function setPrecoComposicao(?string $precoComposicao): ProdutoComposicao
    {
        $this->precoComposicao = $precoComposicao;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalAtual(): ?float
    {
        $this->totalAtual = bcmul((float)($this->qtde ?? 0.0), (float)($this->precoAtual ?? 0.0), 2);
        return $this->totalAtual;
    }

    /**
     * @param float|null $totalAtual
     * @return ProdutoComposicao
     */
    public function setTotalAtual(?float $totalAtual): ProdutoComposicao
    {
        $this->totalAtual = $totalAtual;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalComposicao(): ?float
    {
        $this->totalComposicao = bcmul((float)($this->qtde ?? 0.0), (float)($this->precoComposicao ?? 0.0), 2);
        return $this->totalComposicao;
    }

    /**
     * @param float|null $totalComposicao
     * @return ProdutoComposicao
     */
    public function setTotalComposicao(?float $totalComposicao): ProdutoComposicao
    {
        $this->totalComposicao = $totalComposicao;
        return $this;
    }


}