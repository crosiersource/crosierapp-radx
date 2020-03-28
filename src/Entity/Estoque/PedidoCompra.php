<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\PedidoCompraRepository")
 * @ORM\Table(name="est_pedidocompra")
 *
 * @author Carlos Eduardo Pauluk
 */
class PedidoCompra implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="dt_emissao", type="datetime")
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtEmissao = null;

    /**
     *
     * @ORM\Column(name="dt_prev_entrega", type="datetime")
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtPrevEntrega = null;

    /**
     *
     * @ORM\Column(name="prazos_pagto", type="string")
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $prazosPagto = null;

    /**
     *
     * @ORM\Column(name="responsavel", type="string")
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $responsavel = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Fornecedor")
     * @ORM\JoinColumn(name="fornecedor_id")
     * @Groups("entity")
     *
     * @var null|Fornecedor
     */
    public ?Fornecedor $fornecedor = null;

    /**
     *
     * @ORM\Column(name="subtotal", type="decimal", precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $subtotal = null;

    /**
     *
     * @ORM\Column(name="desconto", type="decimal", precision=15, scale=2)
     * @var null|float
     *
     * @Groups("entity")
     */
    public ?float $desconto = null;

    /**
     *
     * @ORM\Column(name="total", type="decimal", precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $total = null;

    /**
     * 'INICIADO'
     * 'ENVIADO'
     * 'ENTREGUE PARCIAL'
     * 'FINALIZADO'
     * 'CANCELADO'
     *
     * @ORM\Column(name="status", type="string")
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $status = 'INICIADO';

    /**
     *
     * @ORM\Column(name="obs", type="string")
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
     * @var null|PedidoCompraItem[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="PedidoCompraItem",
     *      cascade={"persist"},
     *      mappedBy="pedidoCompra",
     *      orphanRemoval=true)
     * @ORM\OrderBy({"ordem" = "ASC"})
     * @Groups("entity")
     */
    public $itens;


    public function __construct()
    {
        $this->itens = new ArrayCollection();
    }

    public function addItem(?PedidoCompraItem $item): void
    {
        if (!$this->itens->contains($item)) {
            $this->itens->add($item);
        }
    }
}
    