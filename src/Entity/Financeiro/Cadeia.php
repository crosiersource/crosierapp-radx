<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Entidade 'Cadeia de Movimentações'.
 *
 * Movimentações podem ser dependentes umas das outras, formando uma cadeia de entradas e saídas entre carteiras.
 *
 * @ORM\Entity()
 * @ORM\Table(name="fin_cadeia")
 *
 * @author Carlos Eduardo Pauluk
 */
class Cadeia implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * Se for vinculante, ao deletar uma movimentação da cadeia todas deverão são deletadas (ver trigger trg_ad_delete_cadeia).
     *
     * @ORM\Column(name="vinculante", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $vinculante = false;

    /**
     *
     * Se for fechada, não é possível incluir outras movimentações na cadeia.
     *
     * @ORM\Column(name="fechada", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $fechada = false;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Movimentacao",
     *      mappedBy="cadeia",
     *      orphanRemoval=true
     * )
     *
     * @var Movimentacao[]|ArrayCollection|null
     */
    private $movimentacoes;


    public function __construct()
    {
        $this->movimentacoes = new ArrayCollection();
    }

    /**
     * @return bool|null
     */
    public function getVinculante(): ?bool
    {
        return $this->vinculante;
    }

    /**
     * @param bool|null $vinculante
     * @return Cadeia
     */
    public function setVinculante(?bool $vinculante): Cadeia
    {
        $this->vinculante = $vinculante;
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
     * @return Cadeia
     */
    public function setFechada(?bool $fechada): Cadeia
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
     * @return Cadeia
     */
    public function setMovimentacoes($movimentacoes): Cadeia
    {
        $this->movimentacoes = $movimentacoes;
        return $this;
    }


}

