<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ProdutoPrecoRepository")
 * @ORM\Table(name="est_produto_preco")
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoPreco implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto", inversedBy="precos")
     * @ORM\JoinColumn(name="produto_id", nullable=false)
     *
     * @var null|Produto
     */
    private $produto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\ListaPreco", inversedBy="precos")
     * @ORM\JoinColumn(name="produto_id", nullable=false)
     *
     * @var null|Produto
     */
    private $listaPreco;

    /**
     *
     * @ORM\Column(name="coeficiente", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $coeficiente;

    /**
     *
     * @ORM\Column(name="custo_operacional", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $custoOperacional;

    /**
     *
     * @ORM\Column(name="dt_custo", type="date", nullable=false)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    private $dtCusto;

    /**
     *
     * @ORM\Column(name="dt_preco_venda", type="date", nullable=false)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    private $dtPrecoVenda;

    /**
     *
     * @ORM\Column(name="margem", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $margem;

    /**
     *
     * @ORM\Column(name="prazo", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $prazo;

    /**
     *
     * @ORM\Column(name="preco_custo", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoCusto;

    /**
     *
     * @ORM\Column(name="preco_prazo", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoPrazo;

    /**
     *
     * @ORM\Column(name="preco_promo", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoPromo;

    /**
     *
     * @ORM\Column(name="preco_vista", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $precoVista;

    /**
     *
     * @ORM\Column(name="custo_financeiro", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $custoFinanceiro;

    /**
     *
     * @ORM\Column(name="mesano", type="date", nullable=true)
     * @Groups("entity")
     *
     * @var null|\DateTime
     */
    private $mesano;

    /**
     * @return float|null
     */
    public function getCoeficiente(): ?float
    {
        return $this->coeficiente;
    }

    /**
     * @param float|null $coeficiente
     * @return ProdutoPreco
     */
    public function setCoeficiente(?float $coeficiente): ProdutoPreco
    {
        $this->coeficiente = $coeficiente;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCustoOperacional(): ?float
    {
        return $this->custoOperacional;
    }

    /**
     * @param float|null $custoOperacional
     * @return ProdutoPreco
     */
    public function setCustoOperacional(?float $custoOperacional): ProdutoPreco
    {
        $this->custoOperacional = $custoOperacional;
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
     * @return ProdutoPreco
     */
    public function setDtCusto(?\DateTime $dtCusto): ProdutoPreco
    {
        $this->dtCusto = $dtCusto;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtPrecoVenda(): ?\DateTime
    {
        return $this->dtPrecoVenda;
    }

    /**
     * @param \DateTime|null $dtPrecoVenda
     * @return ProdutoPreco
     */
    public function setDtPrecoVenda(?\DateTime $dtPrecoVenda): ProdutoPreco
    {
        $this->dtPrecoVenda = $dtPrecoVenda;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMargem(): ?float
    {
        return $this->margem;
    }

    /**
     * @param float|null $margem
     * @return ProdutoPreco
     */
    public function setMargem(?float $margem): ProdutoPreco
    {
        $this->margem = $margem;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrazo(): ?int
    {
        return $this->prazo;
    }

    /**
     * @param int|null $prazo
     * @return ProdutoPreco
     */
    public function setPrazo(?int $prazo): ProdutoPreco
    {
        $this->prazo = $prazo;
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
     * @return ProdutoPreco
     */
    public function setPrecoCusto(?float $precoCusto): ProdutoPreco
    {
        $this->precoCusto = $precoCusto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrecoPrazo(): ?float
    {
        return $this->precoPrazo;
    }

    /**
     * @param float|null $precoPrazo
     * @return ProdutoPreco
     */
    public function setPrecoPrazo(?float $precoPrazo): ProdutoPreco
    {
        $this->precoPrazo = $precoPrazo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrecoPromo(): ?float
    {
        return $this->precoPromo;
    }

    /**
     * @param float|null $precoPromo
     * @return ProdutoPreco
     */
    public function setPrecoPromo(?float $precoPromo): ProdutoPreco
    {
        $this->precoPromo = $precoPromo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrecoVista(): ?float
    {
        return $this->precoVista;
    }

    /**
     * @param float|null $precoVista
     * @return ProdutoPreco
     */
    public function setPrecoVista(?float $precoVista): ProdutoPreco
    {
        $this->precoVista = $precoVista;
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
     * @return ProdutoPreco
     */
    public function setProduto(?Produto $produto): ProdutoPreco
    {
        $this->produto = $produto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCustoFinanceiro(): ?float
    {
        return $this->custoFinanceiro;
    }

    /**
     * @param float|null $custoFinanceiro
     * @return ProdutoPreco
     */
    public function setCustoFinanceiro(?float $custoFinanceiro): ProdutoPreco
    {
        $this->custoFinanceiro = $custoFinanceiro;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getMesano(): ?\DateTime
    {
        return $this->mesano;
    }

    /**
     * @param \DateTime|null $mesano
     * @return ProdutoPreco
     */
    public function setMesano(?\DateTime $mesano): ProdutoPreco
    {
        $this->mesano = $mesano;
        return $this;
    }


}