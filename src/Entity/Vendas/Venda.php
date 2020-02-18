<?php

namespace App\Entity\Vendas;


use App\Entity\RH\Funcionario;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Vendas\VendaRepository")
 * @ORM\Table(name="ven_venda")
 *
 * @author Carlos Eduardo Pauluk
 */
class Venda implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\Column(name="desconto_especial", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     *
     * @Groups("entity")
     */
    public ?float $descontoEspecial;

    /**
     *
     * @ORM\Column(name="desconto_plano", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $descontoPlano;

    /**
     *
     * @ORM\Column(name="dt_venda", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtVenda;

    /**
     *
     * @ORM\Column(name="historicoDesconto", type="string", nullable=true, length=2000)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $historicoDesconto;

    /**
     *
     * @ORM\Column(name="mesano", type="string", nullable=false, length=6)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $mesano;

    /**
     *
     * @ORM\Column(name="pv", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $pv;

    /**
     *
     * @ORM\Column(name="sub_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $subTotal;

    /**
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $valorTotal;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Vendas\PlanoPagto")
     * @ORM\JoinColumn(name="plano_pagto_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|PlanoPagto
     */
    public ?PlanoPagto $planoPagto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RH\Funcionario")
     * @ORM\JoinColumn(name="vendedor_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Funcionario
     */
    public ?Funcionario $vendedor;

    /**
     *
     * @ORM\Column(name="deletado", type="boolean", nullable=true)
     * @Groups("entity")
     *
     * @var null|boolean
     */
    public ?bool $deletado;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Vendas\TipoVenda")
     * @ORM\JoinColumn(name="tipo_venda_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|TipoVenda
     */
    public ?TipoVenda $tipoVenda;

    /**
     * @ORM\Column(name="cliente_id", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $cliente;

    /**
     *
     * @ORM\Column(name="status", type="string", nullable=false, length=30)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $status;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $obs;

    /**
     *
     * @var null|VendaItem[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="VendaItem",
     *      cascade={"persist"},
     *      mappedBy="venda",
     *      orphanRemoval=true)
     * @ORM\OrderBy({"ordem" = "ASC"})
     * @Groups("entity")
     */
    public $itens;


    public function __construct()
    {
        $this->itens = new ArrayCollection();
    }


    public function addItem(?VendaItem $i): void
    {
        if (!$this->itens->contains($i)) {
            $this->itens->add($i);
        }
    }
}
    