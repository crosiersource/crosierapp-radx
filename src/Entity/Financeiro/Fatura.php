<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Entidade 'Fatura'.
 *
 * Agrupa diversas movimentações que são pagas com referência a um documento fiscal.
 *
 * @ORM\Entity()
 * @ORM\Table(name="fin_fatura")
 *
 * @author Carlos Eduardo Pauluk
 */
class Fatura implements EntityId
{
    use EntityIdTrait;


    /**
     * Documento Fiscal.
     *
     * @ORM\Column(name="fis_documento_id", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $fisDocumentoId;

    /**
     *
     * Se for fechada, não é possível incluir outras movimentações na cadeia.
     *
     * @ORM\Column(name="fechada", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $fechada = false;

    /**
     *
     * @var Movimentacao[]|ArrayCollection|null
     *
     * @ORM\OneToMany(
     *      targetEntity="Movimentacao",
     *      mappedBy="cadeia",
     *      orphanRemoval=true
     * )
     */
    private $movimentacoes;

    public function __construct()
    {
        $this->movimentacoes = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getFisDocumentoId(): ?int
    {
        return $this->fisDocumentoId;
    }

    /**
     * @param int|null $fisDocumentoId
     * @return Fatura
     */
    public function setFisDocumentoId(?int $fisDocumentoId): Fatura
    {
        $this->fisDocumentoId = $fisDocumentoId;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFechada(): ?bool
    {
        return $this->fechada;
    }

    /**
     * @param bool|null $fechada
     * @return Fatura
     */
    public function setFechada(?bool $fechada): Fatura
    {
        $this->fechada = $fechada;
        return $this;
    }

    /**
     * @return Movimentacao[]|ArrayCollection|null
     */
    public function getMovimentacoes()
    {
        return $this->movimentacoes;
    }

    /**
     * @param Movimentacao[]|ArrayCollection|null $movimentacoes
     * @return Fatura
     */
    public function setMovimentacoes($movimentacoes): Fatura
    {
        $this->movimentacoes = $movimentacoes;
        return $this;
    }


}

