<?php

namespace App\Entity\Vendas;

use App\Entity\Estoque\Atributo;
use App\Entity\Estoque\Produto;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $alteracaoPreco;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=5000)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $obs;

    /**
     *
     * @ORM\Column(name="preco_venda", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoVenda;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\vendas\Venda", inversedBy="itens")
     * @ORM\JoinColumn(name="venda_id", nullable=false)     *
     *
     * @var null|Venda
     */
    private $venda;

    /**
     *
     * @ORM\Column(name="nc_descricao", type="string", nullable=true, length=200)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $ncDescricao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_id", nullable=true)
     * @Groups("entity")
     *
     * @var null|Produto
     */
    private $produto;

    /**
     *
     * @ORM\Column(name="nc_reduzido", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $ncReduzido;

    /**
     *
     * @ORM\Column(name="nc_grade_tamanho", type="string", nullable=true, length=200)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $ncGradeTamanho;

    /**
     *
     * @ORM\Column(name="ncm", type="string", nullable=true, length=20)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $ncm;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $ordem;

    /**
     *
     * @ORM\Column(name="ncm_existente", type="boolean", nullable=true)
     * @Groups("entity")
     *
     * @var null|boolean
     */
    private $ncmExistente;

    /**
     *
     * @ORM\Column(name="dt_custo", type="datetime", nullable=true)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    private $dtCusto;

    /**
     *
     * @ORM\Column(name="preco_custo", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoCusto;


    /**
     * Transient.
     * @Groups("entity")
     *
     * @var null|float
     */
    private $totalItem;

    /**
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Estoque\Atributo")
     * @ORM\JoinTable(name="ven_venda_item_atributo",
     *      joinColumns={@ORM\JoinColumn(name="venda_item_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="atributo_id", referencedColumnName="id")}
     * )
     * @var null|Atributo[]|array|Collection
     * @Groups("entity")
     */
    private $atributos;


    public function __construct()
    {
        $this->atributos = new ArrayCollection();
    }

    /**
     * @return bool|null
     */
    public function getAlteracaoPreco(): ?bool
    {
        return $this->alteracaoPreco;
    }

    /**
     * @param bool|null $alteracaoPreco
     * @return VendaItem
     */
    public function setAlteracaoPreco(?bool $alteracaoPreco): VendaItem
    {
        $this->alteracaoPreco = $alteracaoPreco;
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
     * @return VendaItem
     */
    public function setObs(?string $obs): VendaItem
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrecoVenda(): ?float
    {
        return $this->precoVenda;
    }

    /**
     * @param float|null $precoVenda
     * @return VendaItem
     */
    public function setPrecoVenda(?float $precoVenda): VendaItem
    {
        $this->precoVenda = $precoVenda;
        return $this;
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
     * @return VendaItem
     */
    public function setQtde(?float $qtde): VendaItem
    {
        $this->qtde = $qtde;
        return $this;
    }

    /**
     * @return Venda|null
     */
    public function getVenda(): ?Venda
    {
        return $this->venda;
    }

    /**
     * @param Venda|null $venda
     * @return VendaItem
     */
    public function setVenda(?Venda $venda): VendaItem
    {
        $this->venda = $venda;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNcDescricao(): ?string
    {
        return $this->ncDescricao;
    }

    /**
     * @param null|string $ncDescricao
     * @return VendaItem
     */
    public function setNcDescricao(?string $ncDescricao): VendaItem
    {
        $this->ncDescricao = $ncDescricao;
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
     * @return VendaItem
     */
    public function setProduto(?Produto $produto): VendaItem
    {
        $this->produto = $produto;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNcReduzido(): ?int
    {
        return $this->ncReduzido;
    }

    /**
     * @param int|null $ncReduzido
     * @return VendaItem
     */
    public function setNcReduzido(?int $ncReduzido): VendaItem
    {
        $this->ncReduzido = $ncReduzido;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNcGradeTamanho(): ?string
    {
        return $this->ncGradeTamanho;
    }

    /**
     * @param null|string $ncGradeTamanho
     * @return VendaItem
     */
    public function setNcGradeTamanho(?string $ncGradeTamanho): VendaItem
    {
        $this->ncGradeTamanho = $ncGradeTamanho;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNcm(): ?string
    {
        return $this->ncm;
    }

    /**
     * @param null|string $ncm
     * @return VendaItem
     */
    public function setNcm(?string $ncm): VendaItem
    {
        $this->ncm = $ncm;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdem(): ?int
    {
        return $this->ordem;
    }

    /**
     * @param int|null $ordem
     * @return VendaItem
     */
    public function setOrdem(?int $ordem): VendaItem
    {
        $this->ordem = $ordem;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNcmExistente(): ?bool
    {
        return $this->ncmExistente;
    }

    /**
     * @param bool|null $ncmExistente
     * @return VendaItem
     */
    public function setNcmExistente(?bool $ncmExistente): VendaItem
    {
        $this->ncmExistente = $ncmExistente;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtCusto(): ?\DateTime
    {
        return $this->dtCusto;
    }

    /**
     * @param \DateTime|null $dtCusto
     * @return VendaItem
     */
    public function setDtCusto(?\DateTime $dtCusto): VendaItem
    {
        $this->dtCusto = $dtCusto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrecoCusto(): ?float
    {
        return $this->precoCusto;
    }

    /**
     * @param float|null $precoCusto
     * @return VendaItem
     */
    public function setPrecoCusto(?float $precoCusto): VendaItem
    {
        $this->precoCusto = $precoCusto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalItem(): ?float
    {
        return bcmul($this->qtde, $this->precoVenda, 2);
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
     * @return VendaItem
     */
    public function setAtributos($atributos)
    {
        $this->atributos = $atributos;
        return $this;
    }


}