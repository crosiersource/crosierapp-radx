<?php

namespace App\Entity\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade Nota Fiscal.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\DistDFeRepository")
 * @ORM\Table(name="fis_distdfe")
 *
 * @author Carlos Eduardo Pauluk
 */
class DistDFe implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="tipo_distdfe", type="string", nullable=true)
     * @var null|string
     * @Groups("entity")
     */
    private $tipoDistDFe;

    /**
     * Se é referente a um DF próprio.
     *
     * @ORM\Column(name="proprio", type="boolean", nullable=true)
     * @var null|bool
     * @Groups("entity")
     */
    private $proprio;

    /**
     *
     * @ORM\Column(name="chnfe", type="string", nullable=true, length=44)
     * @var null|string
     * @Groups("entity")
     */
    private $chave;

    /**
     *
     * @ORM\Column(name="tp_evento", type="integer", nullable=true)
     * @var null|int
     * @Groups("entity")
     */
    private $tpEvento;

    /**
     *
     * @ORM\Column(name="nseq_evento", type="integer", nullable=true)
     * @var null|int
     * @Groups("entity")
     */
    private $nSeqEvento;

    /**
     *
     * @ORM\Column(name="nsu", type="bigint", nullable=false)
     * @var null|int
     * @Groups("entity")
     */
    private $nsu;

    /**
     *
     * @ORM\Column(name="xml", type="string", nullable=true)
     * @var null|string
     *
     * @NotUppercase()
     */
    private $xml;

    /**
     *
     * @ORM\Column(name="status", length=255, type="string", nullable=true)
     * @var null|string
     * @Groups("entity")
     * @NotUppercase()
     */
    private $status;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal")
     * @ORM\JoinColumn(name="nota_fiscal_id", nullable=true)
     *
     * @var $notaFiscal null|NotaFiscal
     */
    private $notaFiscal;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscalEvento")
     * @ORM\JoinColumn(name="nota_fiscal_evento_id", nullable=true)
     *
     * @var $notaFiscalEvento null|NotaFiscalEvento
     */
    private $notaFiscalEvento;


    /**
     * @return string|null
     */
    public function getTipoDistDFe(): ?string
    {
        return $this->tipoDistDFe;
    }

    /**
     * @param string|null $tipoDistDFe
     * @return DistDFe
     */
    public function setTipoDistDFe(?string $tipoDistDFe): DistDFe
    {
        $this->tipoDistDFe = $tipoDistDFe;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getProprio(): ?bool
    {
        return $this->proprio;
    }

    /**
     * @param bool|null $proprio
     * @return DistDFe
     */
    public function setProprio(?bool $proprio): DistDFe
    {
        $this->proprio = $proprio;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getChave(): ?string
    {
        return $this->chave;
    }

    /**
     * @param string|null $chave
     * @return DistDFe
     */
    public function setChave(?string $chave): DistDFe
    {
        $this->chave = $chave;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTpEvento(): ?int
    {
        return $this->tpEvento;
    }

    /**
     * @param int|null $tpEvento
     * @return DistDFe
     */
    public function setTpEvento(?int $tpEvento): DistDFe
    {
        $this->tpEvento = $tpEvento;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNSeqEvento(): ?int
    {
        return $this->nSeqEvento;
    }

    /**
     * @param int|null $nSeqEvento
     * @return DistDFe
     */
    public function setNSeqEvento(?int $nSeqEvento): DistDFe
    {
        $this->nSeqEvento = $nSeqEvento;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNsu(): ?int
    {
        return $this->nsu;
    }

    /**
     * @param int|null $nsu
     * @return DistDFe
     */
    public function setNsu(?int $nsu): DistDFe
    {
        $this->nsu = $nsu;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return DistDFe
     */
    public function setStatus(?string $status): DistDFe
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \SimpleXMLElement|null
     */
    public function getXMLDecoded(): ?\SimpleXMLElement
    {
        if ($this->getXml() && $this->getXml() !== 'Nenhum documento localizado') {
            $xmlUnzip = gzdecode(base64_decode($this->getXml()));
            return simplexml_load_string($xmlUnzip);
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getXml(): ?string
    {
        return $this->xml;
    }

    /**
     * @param string|null $xml
     * @return DistDFe
     */
    public function setXml(?string $xml): DistDFe
    {
        $this->xml = $xml;
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
     * @return DistDFe
     */
    public function setNotaFiscal(?NotaFiscal $notaFiscal): DistDFe
    {
        $this->notaFiscal = $notaFiscal;
        return $this;
    }

    /**
     * @return NotaFiscalEvento|null
     */
    public function getNotaFiscalEvento(): ?NotaFiscalEvento
    {
        return $this->notaFiscalEvento;
    }

    /**
     * @param NotaFiscalEvento|null $notaFiscalEvento
     * @return DistDFe
     */
    public function setNotaFiscalEvento(?NotaFiscalEvento $notaFiscalEvento): DistDFe
    {
        $this->notaFiscalEvento = $notaFiscalEvento;
        return $this;
    }

    /**
     * Transient.
     * Para não precisar retornar toda a notaFiscal como JSON para o list.
     *
     * @Groups("entity")
     */
    public function getNotaFiscalId(): ?int
    {
        return isset($this->notaFiscal) && $this->notaFiscal->getId() ? $this->notaFiscal->getId() : null;
    }

    /**
     * Transient.
     * Para não precisar retornar toda o notaFiscalEvento como JSON para o list.
     *
     * @Groups("entity")
     */
    public function getEventoId(): ?int
    {
        return isset($this->notaFiscalEvento) && $this->notaFiscalEvento->getId() ? $this->notaFiscalEvento->getId() : null;
    }


}