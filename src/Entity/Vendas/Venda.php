<?php

namespace App\Entity\Vendas;

use App\Entity\CRM\Cliente;
use App\Entity\RH\Funcionario;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
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
     * @ORM\Column(name="pv", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $pv = null;

    /**
     *
     * @ORM\Column(name="dt_venda", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtVenda = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CRM\Cliente")
     * @ORM\JoinColumn(name="plano_pagto_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Cliente
     */
    public ?Cliente $cliente = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Vendas\PlanoPagto")
     * @ORM\JoinColumn(name="plano_pagto_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|PlanoPagto
     */
    public ?PlanoPagto $planoPagto = null;

    /**
     *
     * @ORM\Column(name="sub_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $subTotal = null;

    /**
     *
     * @ORM\Column(name="desconto_especial", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     *
     * @Groups("entity")
     */
    public ?float $descontoEspecial = null;

    /**
     *
     * @ORM\Column(name="historico_desconto", type="string", nullable=true, length=2000)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $historicoDesconto = null;

    /**
     *
     * @ORM\Column(name="desconto_plano", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $descontoPlano = null;

    /**
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $valorTotal = null;

    /**
     *
     * @ORM\Column(name="status", type="string", nullable=false, length=30)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $status = null;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $obs = null;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;

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
    