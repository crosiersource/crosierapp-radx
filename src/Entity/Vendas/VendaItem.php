<?php

namespace App\Entity\Vendas;

use App\Entity\Estoque\Produto;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Vendas\VendaItemRepository")
 * @ORM\Table(name="ven_venda_item")
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaItem implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="alteracao_preco", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var null|boolean
     */
    public ?bool $alteracaoPreco;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=5000)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $obs;

    /**
     *
     * @ORM\Column(name="preco_venda", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $precoVenda;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtde;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\vendas\Venda", inversedBy="itens")
     * @ORM\JoinColumn(name="venda_id", nullable=false)     *
     *
     * @var null|Venda
     */
    public ?Venda $venda;

    /**
     *
     * @ORM\Column(name="nc_descricao", type="string", nullable=true, length=200)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $ncDescricao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_id", nullable=true)
     * @Groups("entity")
     *
     * @var null|Produto
     */
    public ?Produto $produto;

    /**
     *
     * @ORM\Column(name="nc_reduzido", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $ncReduzido;

    /**
     *
     * @ORM\Column(name="nc_grade_tamanho", type="string", nullable=true, length=200)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $ncGradeTamanho;

    /**
     *
     * @ORM\Column(name="ncm", type="string", nullable=true, length=20)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $ncm;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $ordem;

    /**
     *
     * @ORM\Column(name="ncm_existente", type="boolean", nullable=true)
     * @Groups("entity")
     *
     * @var null|boolean
     */
    public ?bool $ncmExistente;

    /**
     *
     * @ORM\Column(name="dt_custo", type="datetime", nullable=true)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtCusto;

    /**
     *
     * @ORM\Column(name="preco_custo", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $precoCusto;


    /**
     * Transient.
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $totalItem;


}