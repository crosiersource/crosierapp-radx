<?php

namespace App\Entity\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\NotaFiscalItemRepository")
 * @ORM\Table(name="fis_nf_item")
 */
class NotaFiscalItem implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal", inversedBy="itens")
     * @ORM\JoinColumn(name="nota_fiscal_id", nullable=true)
     *
     * @var $notaFiscal null|NotaFiscal
     */
    private $notaFiscal;


    /**
     *
     * @ORM\Column(name="cfop", type="string", nullable=false, length=20)
     * @var null|string
     */
    private $cfop;

    /**
     *
     * @ORM\Column(name="codigo", type="string", nullable=false, length=50)
     * @var null|string
     */
    private $codigo;


    /**
     *
     * @ORM\Column(name="ean", type="string", nullable=true, length=50)
     * @var null|string
     */
    private $ean;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=2000)
     * @var null|string
     */
    private $descricao;

    /**
     *
     * @ORM\Column(name="csosn", type="integer", nullable=true)
     * @var null|int
     */
    private $csosn;

    /**
     *
     * @ORM\Column(name="ncm", type="string", nullable=false, length=20)
     * @var null|string
     */
    private $ncm;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=false)
     * @var null|int
     */
    private $ordem;

    /**
     *
     * @ORM\Column(name="qtde", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $qtde;

    /**
     *
     * @ORM\Column(name="unidade", type="string", nullable=false, length=50)
     * @var null|string
     */
    private $unidade;

    /**
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $valorTotal;

    /**
     *
     * @ORM\Column(name="valor_unit", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $valorUnit;

    /**
     *
     * @ORM\Column(name="valor_desconto", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $valorDesconto;

    /**
     *
     * @ORM\Column(name="sub_total", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $subTotal;

    /**
     *
     * @ORM\Column(name="icms_valor", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $icmsValor;

    /**
     *
     * @ORM\Column(name="icms_valor_bc", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $icmsValorBc;

    /**
     *
     * @ORM\Column(name="icms", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $icmsAliquota;

    /**
     *
     * @ORM\Column(name="ncm_existente", type="boolean", nullable=true)
     * @var null|bool
     */
    private $ncmExistente;

    /**
     * @return null|string
     */
    public function getCfop(): ?string
    {
        return $this->cfop;
    }

    /**
     * @param null|string $cfop
     * @return NotaFiscalItem
     */
    public function setCfop(?string $cfop): NotaFiscalItem
    {
        $this->cfop = $cfop;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    /**
     * @param null|string $codigo
     * @return NotaFiscalItem
     */
    public function setCodigo(?string $codigo): NotaFiscalItem
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @param string|null $ean
     * @return NotaFiscalItem
     */
    public function setEan(?string $ean): NotaFiscalItem
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return NotaFiscalItem
     */
    public function setDescricao(?string $descricao): NotaFiscalItem
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCsosn(): ?int
    {
        return $this->csosn;
    }

    /**
     * @param int|null $csosn
     * @return NotaFiscalItem
     */
    public function setCsosn(?int $csosn): NotaFiscalItem
    {
        $this->csosn = $csosn;
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
     * @return NotaFiscalItem
     */
    public function setNcm(?string $ncm): NotaFiscalItem
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
     * @return NotaFiscalItem
     */
    public function setOrdem(?int $ordem): NotaFiscalItem
    {
        $this->ordem = $ordem;
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
     * @return NotaFiscalItem
     */
    public function setQtde(?float $qtde): NotaFiscalItem
    {
        $this->qtde = $qtde;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUnidade(): ?string
    {
        return $this->unidade;
    }

    /**
     * @param null|string $unidade
     * @return NotaFiscalItem
     */
    public function setUnidade(?string $unidade): NotaFiscalItem
    {
        $this->unidade = $unidade;
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
     * @return NotaFiscalItem
     */
    public function setValorTotal(?float $valorTotal): NotaFiscalItem
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorUnit(): ?float
    {
        return $this->valorUnit;
    }

    /**
     * @param float|null $valorUnit
     * @return NotaFiscalItem
     */
    public function setValorUnit(?float $valorUnit): NotaFiscalItem
    {
        $this->valorUnit = $valorUnit;
        return $this;
    }

    /**
     * @return NotaFiscal|null
     */
    public function getNotaFiscal(): ?NotaFiscal
    {
        return $this->notaFiscal;
    }

    /**
     * @param NotaFiscal|null $notaFiscal
     * @return NotaFiscalItem
     */
    public function setNotaFiscal(?NotaFiscal $notaFiscal): NotaFiscalItem
    {
        $this->notaFiscal = $notaFiscal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorDesconto(): ?float
    {
        return $this->valorDesconto;
    }

    /**
     * @param float|null $valorDesconto
     * @return NotaFiscalItem
     */
    public function setValorDesconto(?float $valorDesconto): NotaFiscalItem
    {
        $this->valorDesconto = $valorDesconto;
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
     * @return NotaFiscalItem
     */
    public function setSubTotal(?float $subTotal): NotaFiscalItem
    {
        $this->subTotal = $subTotal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getIcmsValor(): ?float
    {
        return $this->icmsValor;
    }

    /**
     * @param float|null $icmsValor
     * @return NotaFiscalItem
     */
    public function setIcmsValor(?float $icmsValor): NotaFiscalItem
    {
        $this->icmsValor = $icmsValor;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getIcmsValorBc(): ?float
    {
        return $this->icmsValorBc;
    }

    /**
     * @param float|null $icmsValorBc
     * @return NotaFiscalItem
     */
    public function setIcmsValorBc(?float $icmsValorBc): NotaFiscalItem
    {
        $this->icmsValorBc = $icmsValorBc;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getIcmsAliquota(): ?float
    {
        return $this->icmsAliquota;
    }

    /**
     * @param float|null $icmsAliquota
     * @return NotaFiscalItem
     */
    public function setIcmsAliquota(?float $icmsAliquota): NotaFiscalItem
    {
        $this->icmsAliquota = $icmsAliquota;
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
     * @return NotaFiscalItem
     */
    public function setNcmExistente(?bool $ncmExistente): NotaFiscalItem
    {
        $this->ncmExistente = $ncmExistente;
        return $this;
    }

    public function calculaTotais(): void
    {
        if ($this->getQtde() === null || $this->getValorUnit() === null) {
            return;
        }

        $this->valorDesconto = $this->valorDesconto ?? 0.0;
        $this->subTotal = $this->getQtde() * $this->getValorUnit();
        $this->valorTotal = $this->subTotal; // - $this->valorDesconto;
    }
}