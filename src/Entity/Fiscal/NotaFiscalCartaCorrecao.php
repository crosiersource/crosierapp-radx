<?php

namespace App\Entity\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\NotaFiscalCartaCorrecaoRepository")
 * @ORM\Table(name="fis_nf_cartacorrecao")
 */
class NotaFiscalCartaCorrecao implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal", inversedBy="itens")
     *
     * @var $notaFiscal null|NotaFiscal
     */
    private $notaFiscal;

    /**
     *
     * @ORM\Column(name="carta_correcao", type="string", nullable=true)
     * @var null|string
     */
    private $cartaCorrecao;

    /**
     *
     * @ORM\Column(name="seq", type="integer", nullable=true)
     * @var null|int
     */
    private $seq;


    /**
     *
     * @ORM\Column(name="dt_carta_correcao", type="datetime", nullable=false)
     * @var null|\DateTime
     */
    private $dtCartaCorrecao;

    /**
     *
     * @ORM\Column(name="msg_retorno", type="string", nullable=true)
     * @var null|string
     */
    private $msgRetorno;


    /**
     * @return null|string
     */
    public function getCartaCorrecao(): ?string
    {
        return $this->cartaCorrecao;
    }

    /**
     * @param null|string $cartaCorrecao
     * @return NotaFiscalCartaCorrecao
     */
    public function setCartaCorrecao(?string $cartaCorrecao): NotaFiscalCartaCorrecao
    {
        $this->cartaCorrecao = $cartaCorrecao;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSeq(): ?int
    {
        return $this->seq;
    }

    /**
     * @param int|null $seq
     * @return NotaFiscalCartaCorrecao
     */
    public function setSeq(?int $seq): NotaFiscalCartaCorrecao
    {
        $this->seq = $seq;
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
     * @return NotaFiscalCartaCorrecao
     */
    public function setNotaFiscal(?NotaFiscal $notaFiscal): NotaFiscalCartaCorrecao
    {
        $this->notaFiscal = $notaFiscal;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtCartaCorrecao(): ?\DateTime
    {
        return $this->dtCartaCorrecao;
    }

    /**
     * @param \DateTime|null $dtCartaCorrecao
     * @return NotaFiscalCartaCorrecao
     */
    public function setDtCartaCorrecao(?\DateTime $dtCartaCorrecao): NotaFiscalCartaCorrecao
    {
        $this->dtCartaCorrecao = $dtCartaCorrecao;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMsgRetorno(): ?string
    {
        return $this->msgRetorno;
    }

    /**
     * @param string|null $msgRetorno
     * @return NotaFiscalCartaCorrecao
     */
    public function setMsgRetorno(?string $msgRetorno): NotaFiscalCartaCorrecao
    {
        $this->msgRetorno = $msgRetorno;
        return $this;
    }


}