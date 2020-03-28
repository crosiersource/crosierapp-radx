<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\PedidoCompraItemRepository")
 * @ORM\Table(name="est_pedidocompra_item")
 *
 * @author Carlos Eduardo Pauluk
 */
class PedidoCompraItem implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\PedidoCompra", inversedBy="itens")
     * @ORM\JoinColumn(name="pedidocompra_id")
     *
     * @var null|PedidoCompra
     */
    public ?PedidoCompra $pedidoCompra = null;

    /**
     *
     * @ORM\Column(name="ordem", type="integer")
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $ordem = null;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtde = null;

    /**
     *
     * @ORM\Column(name="referencia", type="string")
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $referencia = null;

    /**
     *
     * @ORM\Column(name="descricao", type="string")
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $descricao = null;

    /**
     *
     * @ORM\Column(name="preco_custo", type="decimal", precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $precoCusto = null;

    /**
     *
     * @ORM\Column(name="desconto", type="decimal", precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $desconto = null;

    /**
     * @ORM\Column(name="total", type="decimal", precision=19, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $total = null;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;


}