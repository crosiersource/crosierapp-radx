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
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto", inversedBy="composicoes")
     * @ORM\JoinColumn(name="produto_pai_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Produto
     */
    public ?Produto $produtoPai = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_filho_id", nullable=false)
     *
     * @Groups("entity")
     *
     * @var null|Produto
     */
    public ?Produto $produtoFilho = null;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     * @var null|integer
     */
    public ?int $ordem = null;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtde = null;

    /**
     *
     * @ORM\Column(name="preco_composicao", type="decimal", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    public ?string $precoComposicao = null;

    /**
     * produtoFilho.jsonData.qtde_atual * produtoFilho.jsonData.preco_tabela
     *
     * @var null|float
     */
    private ?float $totalAtual = null;

    /**
     * qtde * precoComposicao
     *
     * @var null|float
     */
    private ?float $totalComposicao = null;

    /**
     * @return float|null
     */
    public function getTotalAtual(): ?float
    {
        $this->totalAtual = bcmul($this->produtoFilho->jsonData['qtde_estoque_total'] ?? 0.0, $this->produtoFilho->jsonData['preco_tabela'], 2);
        return $this->totalAtual;
    }

    /**
     * @return float|null
     */
    public function getTotalComposicao(): ?float
    {
        $this->totalComposicao = bcmul($this->qtde ?? 0.0, $this->precoComposicao ?? 0.0, 2);
        return $this->totalComposicao;
    }


}