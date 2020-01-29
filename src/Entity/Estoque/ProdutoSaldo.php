<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ProdutoSaldoRepository")
 * @ORM\Table(name="est_produto_saldo")
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoSaldo implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $qtde;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto", inversedBy="saldos")
     * @ORM\JoinColumn(name="produto_id", nullable=false)
     *
     * @var null|Produto
     */
    private $produto;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Atributo")
     * @ORM\JoinTable(name="est_produto_preco_atributo",
     *      joinColumns={@ORM\JoinColumn(name="lista_preco_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="atributo_id", referencedColumnName="id")}
     * )
     * @var null|Atributo[]|array|Collection
     * @Groups("entity")
     */
    private $atributos;

// ...

    public function __construct()
    {
        $this->atributos = new ArrayCollection();
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
     * @return ProdutoSaldo
     */
    public function setQtde(?float $qtde): ProdutoSaldo
    {
        $this->qtde = $qtde;
        return $this;
    }

    /**
     * @return Produto|null
     */
    public function getProduto(): ?Produto
    {
        return $this->produto;
    }

    /**
     * @param Produto|null $produto
     * @return ProdutoSaldo
     */
    public function setProduto(?Produto $produto): ProdutoSaldo
    {
        $this->produto = $produto;
        return $this;
    }

    /**
     * @return Atributo[]|array|Collection|null
     */
    public function getAtributos()
    {
        return $this->atributos;
    }

    /**
     * @param Atributo[]|array|Collection|null $atributos
     * @return ProdutoSaldo
     */
    public function setAtributos($atributos)
    {
        $this->atributos = $atributos;
        return $this;
    }


}