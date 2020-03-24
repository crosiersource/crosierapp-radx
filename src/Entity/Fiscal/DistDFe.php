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
     * @ORM\Column(name="documento", type="string")
     * @var null|string
     * @Groups("entity")
     */
    public ?string $documento = null;

    /**
     *
     * @ORM\Column(name="tipo_distdfe", type="string")
     * @var null|string
     * @Groups("entity")
     */
    public ?string $tipoDistDFe = null;

    /**
     * Se é referente a um DF próprio.
     *
     * @ORM\Column(name="proprio", type="boolean")
     * @var null|bool
     * @Groups("entity")
     */
    public ?bool $proprio = null;

    /**
     *
     * @ORM\Column(name="chnfe", type="string", length=44)
     * @var null|string
     * @Groups("entity")
     */
    public ?string $chave = null;

    /**
     *
     * @ORM\Column(name="tp_evento", type="integer")
     * @var null|int
     * @Groups("entity")
     */
    public ?int $tpEvento = null;

    /**
     *
     * @ORM\Column(name="nseq_evento", type="integer")
     * @var null|int
     * @Groups("entity")
     */
    public ?int $nSeqEvento = null;

    /**
     *
     * @ORM\Column(name="nsu", type="bigint", nullable=false)
     * @var null|int
     * @Groups("entity")
     */
    public ?int $nsu = null;

    /**
     *
     * @ORM\Column(name="xml", type="string")
     * @var null|string
     *
     * @NotUppercase()
     */
    public ?string $xml = null;

    /**
     *
     * @ORM\Column(name="status", length=255, type="string")
     * @var null|string
     * @Groups("entity")
     * @NotUppercase()
     */
    public ?string $status = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal")
     * @ORM\JoinColumn(name="nota_fiscal_id")
     *
     * @var $notaFiscal null|NotaFiscal
     */
    public ?NotaFiscal $notaFiscal = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscalEvento")
     * @ORM\JoinColumn(name="nota_fiscal_evento_id")
     *
     * @var $notaFiscalEvento null|NotaFiscalEvento
     */
    public ?NotaFiscalEvento $notaFiscalEvento = null;


    /**
     * @return \SimpleXMLElement|null
     */
    public function getXMLDecoded(): ?\SimpleXMLElement
    {
        if ($this->xml && $this->xml !== 'Nenhum documento localizado') {
            $xmlUnzip = gzdecode(base64_decode($this->xml));
            return simplexml_load_string($xmlUnzip);
        }
        return null;
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