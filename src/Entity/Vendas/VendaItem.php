<?php

namespace App\Entity\Vendas;

use App\Entity\Estoque\Produto;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\vendas\Venda", inversedBy="itens")
     * @ORM\JoinColumn(name="venda_id", nullable=false)     *
     *
     * @var null|Venda
     */
    public ?Venda $venda = null;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    public ?int $ordem = null;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $qtde = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_id", nullable=true)
     * @Groups("entity")
     *
     * @var null|Produto
     */
    public ?Produto $produto = null;

    /**
     *
     * @ORM\Column(name="preco_venda", type="decimal", nullable=false, precision=19, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    public ?float $precoVenda = null;

    /**
     * @ORM\Column(name="total", type="decimal", nullable=false, precision=19, scale=2)
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