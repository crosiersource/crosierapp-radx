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
    private $descontoEspecial;

    /**
     *
     * @ORM\Column(name="desconto_plano", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $descontoPlano;

    /**
     *
     * @ORM\Column(name="dt_venda", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    private $dtVenda;

    /**
     *
     * @ORM\Column(name="historicoDesconto", type="string", nullable=true, length=2000)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $historicoDesconto;

    /**
     *
     * @ORM\Column(name="mesano", type="string", nullable=false, length=6)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $mesano;

    /**
     *
     * @ORM\Column(name="pv", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $pv;

    /**
     *
     * @ORM\Column(name="sub_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $subTotal;

    /**
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $valorTotal;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Vendas\PlanoPagto")
     * @ORM\JoinColumn(name="plano_pagto_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|PlanoPagto
     */
    private $planoPagto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RH\Funcionario")
     * @ORM\JoinColumn(name="vendedor_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Funcionario
     */
    private $vendedor;

    /**
     *
     * @ORM\Column(name="deletado", type="boolean", nullable=true)
     * @Groups("entity")
     *
     * @var null|boolean
     */
    private $deletado;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Vendas\TipoVenda")
     * @ORM\JoinColumn(name="tipo_venda_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|TipoVenda
     */
    private $tipoVenda;

    /**
     * @ORM\Column(name="cliente_id", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $cliente;

    /**
     *
     * @ORM\Column(name="status", type="string", nullable=false, length=30)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $status;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $obs;

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
    private $itens;


    public function __construct()
    {
        $this->itens = new ArrayCollection();
    }

    /**
     * @return float|null
     */
    public function getDescontoEspecial(): ?float
    {
        return $this->descontoEspecial;
    }

    /**
     * @param float|null $descontoEspecial
     * @return Venda
     */
    public function setDescontoEspecial(?float $descontoEspecial): Venda
    {
        $this->descontoEspecial = $descontoEspecial;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getDescontoPlano(): ?float
    {
        return $this->descontoPlano;
    }

    /**
     * @param float|null $descontoPlano
     * @return Venda
     */
    public function setDescontoPlano(?float $descontoPlano): Venda
    {
        $this->descontoPlano = $descontoPlano;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtVenda(): ?\DateTime
    {
        return $this->dtVenda;
    }

    /**
     * @param \DateTime|null $dtVenda
     * @return Venda
     */
    public function setDtVenda(?\DateTime $dtVenda): Venda
    {
        $this->dtVenda = $dtVenda;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getHistoricoDesconto(): ?string
    {
        return $this->historicoDesconto;
    }

    /**
     * @param null|string $historicoDesconto
     * @return Venda
     */
    public function setHistoricoDesconto(?string $historicoDesconto): Venda
    {
        $this->historicoDesconto = $historicoDesconto;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMesano(): ?string
    {
        return $this->mesano;
    }

    /**
     * @param null|string $mesano
     * @return Venda
     */
    public function setMesano(?string $mesano): Venda
    {
        $this->mesano = $mesano;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPv(): ?int
    {
        return $this->pv;
    }

    /**
     * @param int|null $pv
     * @return Venda
     */
    public function setPv(?int $pv): Venda
    {
        $this->pv = $pv;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSubTotal(): ?float
    {
        return $this->subTotal;
    }

    /**
     * @param float|null $subTotal
     * @return Venda
     */
    public function setSubTotal(?float $subTotal): Venda
    {
        $this->subTotal = $subTotal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorTotal(): ?float
    {
        return $this->valorTotal;
    }

    /**
     * @param float|null $valorTotal
     * @return Venda
     */
    public function setValorTotal(?float $valorTotal): Venda
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    /**
     * @return PlanoPagto|null
     */
    public function getPlanoPagto(): ?PlanoPagto
    {
        return $this->planoPagto;
    }

    /**
     * @param PlanoPagto|null $planoPagto
     * @return Venda
     */
    public function setPlanoPagto(?PlanoPagto $planoPagto): Venda
    {
        $this->planoPagto = $planoPagto;
        return $this;
    }

    /**
     * @return Funcionario|null
     */
    public function getVendedor(): ?Funcionario
    {
        return $this->vendedor;
    }

    /**
     * @param Funcionario|null $vendedor
     * @return Venda
     */
    public function setVendedor(?Funcionario $vendedor): Venda
    {
        $this->vendedor = $vendedor;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getDeletado(): ?bool
    {
        return $this->deletado;
    }

    /**
     * @param bool|null $deletado
     * @return Venda
     */
    public function setDeletado(?bool $deletado): Venda
    {
        $this->deletado = $deletado;
        return $this;
    }

    /**
     * @return TipoVenda|null
     */
    public function getTipoVenda(): ?TipoVenda
    {
        return $this->tipoVenda;
    }

    /**
     * @param TipoVenda|null $tipoVenda
     * @return Venda
     */
    public function setTipoVenda(?TipoVenda $tipoVenda): Venda
    {
        $this->tipoVenda = $tipoVenda;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCliente(): ?int
    {
        return $this->cliente;
    }

    /**
     * @param int|null $cliente
     * @return Venda
     */
    public function setCliente(?int $cliente): Venda
    {
        $this->cliente = $cliente;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param null|string $status
     * @return Venda
     */
    public function setStatus(?string $status): Venda
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getObs(): ?string
    {
        return $this->obs;
    }

    /**
     * @param null|string $obs
     * @return Venda
     */
    public function setObs(?string $obs): Venda
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return VendaItem[]|ArrayCollection|null
     */
    public function getItens()
    {
        return $this->itens;
    }


    /**
     * @param VendaItem[]|ArrayCollection|null $itens
     * @return Venda
     */
    public function setItens($itens): Venda
    {
        $this->itens = $itens;
        return $this;
    }

    public function addItem(?VendaItem $i): void
    {
        if (!$this->itens->contains($i)) {
            $this->itens->add($i);
        }
    }
}
    