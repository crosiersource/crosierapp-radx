<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade Modo de Movimentação.
 *
 * Informa se a movimentação foi em 'espécie', 'cheque', 'boleto', etc.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\ModoRepository")
 * @ORM\Table(name="fin_modo")
 *
 * @author Carlos Eduardo Pauluk
 */
class Modo implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Range(min=1)
     * @Groups("entity")
     *
     * @var integer|null
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Assert\NotBlank()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * Informa se este modo é aceito para transferências próprias (entre
     * carteiras).
     *
     * @ORM\Column(name="transf_propria", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoDeTransfPropria = false;

    /**
     * Informa se este modo é aceito para transferências próprias (entre
     * carteiras).
     *
     * @ORM\Column(name="moviment_agrup", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoDeMovimentAgrup = false;

    /**
     * Informa se este modo é aceito para transferências próprias (entre
     * carteiras).
     *
     * @ORM\Column(name="modo_cartao", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoDeCartao = false;

    /**
     * Informa se este modo é aceito para transferências próprias (entre
     * carteiras).
     *
     * @ORM\Column(name="modo_cheque", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoDeCheque = false;

    /**
     * Informa se este modo é aceito para transferência/recolhimento de caixas.
     *
     * @ORM\Column(name="transf_caixa", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoDeTransfCaixa = false;

    /**
     *
     * @ORM\Column(name="com_banco_origem", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $modoComBancoOrigem = false;


    /**
     * @param bool $format
     * @return int|null|string
     */
    public function getCodigo($format = false)
    {
        if ($format) {
            return str_pad($this->codigo, 2, "0", STR_PAD_LEFT);
        }
        return $this->codigo;

    }

    /**
     * @param int|null $codigo
     * @return Modo
     */
    public function setCodigo(?int $codigo): Modo
    {
        $this->codigo = $codigo;
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
     * @Groups("entity")
     * @return string
     */
    public function getDescricaoMontada(): string
    {
        return $this->getCodigo(true) . ' - ' . $this->getDescricao();
    }

    /**
     * @param null|string $descricao
     * @return Modo
     */
    public function setDescricao(?string $descricao): Modo
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoDeTransfPropria(): ?bool
    {
        return $this->modoDeTransfPropria;
    }

    /**
     * @param bool|null $modoDeTransfPropria
     * @return Modo
     */
    public function setModoDeTransfPropria(?bool $modoDeTransfPropria): Modo
    {
        $this->modoDeTransfPropria = $modoDeTransfPropria;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoDeMovimentAgrup(): ?bool
    {
        return $this->modoDeMovimentAgrup;
    }

    /**
     * @param bool|null $modoDeMovimentAgrup
     * @return Modo
     */
    public function setModoDeMovimentAgrup(?bool $modoDeMovimentAgrup): Modo
    {
        $this->modoDeMovimentAgrup = $modoDeMovimentAgrup;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoDeCartao(): ?bool
    {
        return $this->modoDeCartao;
    }

    /**
     * @param bool|null $modoDeCartao
     * @return Modo
     */
    public function setModoDeCartao(?bool $modoDeCartao): Modo
    {
        $this->modoDeCartao = $modoDeCartao;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoDeCheque(): ?bool
    {
        return $this->modoDeCheque;
    }

    /**
     * @param bool|null $modoDeCheque
     * @return Modo
     */
    public function setModoDeCheque(?bool $modoDeCheque): Modo
    {
        $this->modoDeCheque = $modoDeCheque;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoDeTransfCaixa(): ?bool
    {
        return $this->modoDeTransfCaixa;
    }

    /**
     * @param bool|null $modoDeTransfCaixa
     * @return Modo
     */
    public function setModoDeTransfCaixa(?bool $modoDeTransfCaixa): Modo
    {
        $this->modoDeTransfCaixa = $modoDeTransfCaixa;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getModoComBancoOrigem(): ?bool
    {
        return $this->modoComBancoOrigem;
    }

    /**
     * @param bool|null $modoComBancoOrigem
     * @return Modo
     */
    public function setModoComBancoOrigem(?bool $modoComBancoOrigem): Modo
    {
        $this->modoComBancoOrigem = $modoComBancoOrigem;
        return $this;
    }


}
