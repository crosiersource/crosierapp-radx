<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade para manter registros de conferências mensais.
 *
 * @author Carlos Eduardo Pauluk
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\RegistroConferenciaRepository")
 * @ORM\Table(name="fin_reg_conf")
 */
class RegistroConferencia implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=200)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     *
     * @ORM\Column(name="dt_registro", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \Datetime|null
     */
    private $dtRegistro;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_id", nullable=true)
     * @Groups("entity")
     *
     * @var Carteira|null
     */
    private $carteira;

    /**
     *
     * @ORM\Column(name="valor", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $valor;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=5000)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $obs;

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return RegistroConferencia
     */
    public function setDescricao(?string $descricao): RegistroConferencia
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return \Datetime|null
     */
    public function getDtRegistro(): ?\Datetime
    {
        return $this->dtRegistro;
    }

    /**
     * @param \Datetime|null $dtRegistro
     * @return RegistroConferencia
     */
    public function setDtRegistro(?\Datetime $dtRegistro): RegistroConferencia
    {
        $this->dtRegistro = $dtRegistro;
        return $this;
    }

    /**
     * @return Carteira|null
     */
    public function getCarteira(): ?Carteira
    {
        return $this->carteira;
    }

    /**
     * @param Carteira|null $carteira
     * @return RegistroConferencia
     */
    public function setCarteira(?Carteira $carteira): RegistroConferencia
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValor(): ?float
    {
        return $this->valor;
    }

    /**
     * @param float|null $valor
     * @return RegistroConferencia
     */
    public function setValor(?float $valor): RegistroConferencia
    {
        $this->valor = $valor;
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
     * @return RegistroConferencia
     */
    public function setObs(?string $obs): RegistroConferencia
    {
        $this->obs = $obs;
        return $this;
    }


}
