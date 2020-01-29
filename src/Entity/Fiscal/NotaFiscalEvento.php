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
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\NotaFiscalEventoRepository")
 * @ORM\Table(name="fis_nf_evento")
 *
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalEvento implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal", inversedBy="eventos")
     * @ORM\JoinColumn(name="nota_fiscal_id", nullable=true)
     *
     * @var $notaFiscal null|NotaFiscal
     */
    private $notaFiscal;

    /**
     *
     * @ORM\Column(name="tp_evento", type="integer", nullable=false)
     * @var null|int
     *
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
     * @ORM\Column(name="desc_evento", type="string", length=200, nullable=false)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $descEvento;

    /**
     *
     * @ORM\Column(name="xml", type="string", nullable=true)
     * @var null|string
     *
     * @NotUppercase()
     */
    private $xml;

    /**
     * @return NotaFiscal|null
     */
    public function getNotaFiscal(): ?NotaFiscal
    {
        return $this->notaFiscal;
    }

    /**
     * @param NotaFiscal|null $notaFiscal
     * @return NotaFiscalEvento
     */
    public function setNotaFiscal(?NotaFiscal $notaFiscal): NotaFiscalEvento
    {
        $this->notaFiscal = $notaFiscal;
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
     * @return NotaFiscalEvento
     */
    public function setTpEvento(?int $tpEvento): NotaFiscalEvento
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
     * @return NotaFiscalEvento
     */
    public function setNSeqEvento(?int $nSeqEvento): NotaFiscalEvento
    {
        $this->nSeqEvento = $nSeqEvento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescEvento(): ?string
    {
        return $this->descEvento;
    }

    /**
     * @param string|null $descEvento
     * @return NotaFiscalEvento
     */
    public function setDescEvento(?string $descEvento): NotaFiscalEvento
    {
        $this->descEvento = $descEvento;
        return $this;
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
     * @return NotaFiscalEvento
     */
    public function setXml(?string $xml): NotaFiscalEvento
    {
        $this->xml = $xml;
        return $this;
    }

    /**
     * @return \SimpleXMLElement|null
     */
    public function getXMLDecoded(): ?\SimpleXMLElement
    {
        if ($this->getXml()) {
            $xmlUnzip = gzdecode(base64_decode($this->getXml()));
            return simplexml_load_string($xmlUnzip);
        }
        return null;
    }


}