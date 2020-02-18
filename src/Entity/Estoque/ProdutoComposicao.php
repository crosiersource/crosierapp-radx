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
    public ?Produto $produtoPai;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_filho_id", nullable=false)
     *
     * @Groups("entity")
     *
     * @var null|Produto
     */
    public ?Produto $produtoFilho;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     * @var null|integer
     */
    public ?int $ordem;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtde;

    /**
     *
     * @ORM\Column(name="preco_atual", type="decimal", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    public ?string $precoAtual;

    /**
     * Transient.
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtdeEmEstoque;

    /**
     *
     * @ORM\Column(name="preco_composicao", type="decimal", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    public ?string $precoComposicao;

    /**
     * Transient.
     *
     * @var null|float
     */
    public ?float $totalAtual;

    /**
     * Transient.
     *
     * @var null|float
     */
    public ?float $totalComposicao;


}