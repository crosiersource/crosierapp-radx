<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Entidade 'Carteira'.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\CarteiraRepository")
 * @ORM\Table(name="fin_carteira")
 *
 * @author Carlos Eduardo Pauluk
 */
class Carteira implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * Movimentações desta carteira não poderão ter suas datas alteradas para antes desta.
     *
     * @ORM\Column(name="dt_consolidado", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtConsolidado;

    /**
     * Uma Carteira concreta é aquela em que podem ser efetuados créditos e
     * débitos, como uma conta corrente ou um caixa.
     * Um Grupo de Movimentação só pode estar vinculado à uma Carteira concreta.
     * Uma movimentação que contenha um grupo de movimentação, precisa ter sua
     * carteira igual a carteira do grupo de movimentação.
     *
     *
     * @ORM\Column(name="concreta", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $concreta = false;

    /**
     * Informa se esta carteira pode conter movimentações com status ABERTA.
     *
     * @ORM\Column(name="abertas", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $abertas = false;

    /**
     * Informa se esta carteira é um caixa (ex.: caixa a vista, caixa a prazo).
     *
     * @ORM\Column(name="caixa", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $caixa = false;

    /**
     * Informa se esta carteira possui talão de cheques.
     *
     * @ORM\Column(name="cheque", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $cheque = false;

    /**
     * No caso da Carteira ser uma conta de banco, informa qual.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Banco")
     * @ORM\JoinColumn(nullable=true)
     * @Groups("entity")
     *
     * @var Banco|null
     */
    private $banco;

    /**
     * Código da agência (sem o dígito verificador).
     *
     * @ORM\Column(name="agencia", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $agencia;

    /**
     * Número da conta no banco (não segue um padrão).
     *
     * @ORM\Column(name="conta", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $conta;

    /**
     * Utilizado para informar o limite disponível.
     *
     * @ORM\Column(name="limite", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $limite;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\OperadoraCartao")
     * @ORM\JoinColumn(name="operadora_cartao_id", nullable=true)
     *
     * @Groups("entity")
     * @MaxDepth(1)
     *
     * @var OperadoraCartao|null
     */
    private $operadoraCartao;

    /**
     * Transient.
     *
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricaoMontada;


    /**
     * Informa se esta carteira está atualmente em utilização.
     *
     * @ORM\Column(name="atual", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $atual = false;


    public function getCodigo(bool $format = false)
    {
        if ($format) {
            return str_pad($this->codigo, 3, '0', STR_PAD_LEFT);
        }

        return $this->codigo;
    }


    /**
     * @param int|null $codigo
     * @return Carteira
     */
    public function setCodigo(?int $codigo): Carteira
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
     * @return string
     */
    public function getDescricaoMontada(): string
    {
        $this->descricaoMontada = $this->getCodigo(true) . ' - ' . $this->getDescricao();
        return $this->descricaoMontada;
    }

    /**
     * @param null|string $descricao
     * @return Carteira
     */
    public function setDescricao(?string $descricao): Carteira
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtConsolidado(): ?\DateTime
    {
        return $this->dtConsolidado;
    }

    /**
     * @param \DateTime|null $dtConsolidado
     * @return Carteira
     */
    public function setDtConsolidado(?\DateTime $dtConsolidado): Carteira
    {
        $this->dtConsolidado = $dtConsolidado;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getConcreta(): ?bool
    {
        return $this->concreta;
    }

    /**
     * @param bool|null $concreta
     * @return Carteira
     */
    public function setConcreta(?bool $concreta): Carteira
    {
        $this->concreta = $concreta;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAbertas(): ?bool
    {
        return $this->abertas;
    }

    /**
     * @param bool|null $abertas
     * @return Carteira
     */
    public function setAbertas(?bool $abertas): Carteira
    {
        $this->abertas = $abertas;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCaixa(): ?bool
    {
        return $this->caixa;
    }

    /**
     * @param bool|null $caixa
     * @return Carteira
     */
    public function setCaixa(?bool $caixa): Carteira
    {
        $this->caixa = $caixa;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCheque(): ?bool
    {
        return $this->cheque;
    }

    /**
     * @param bool|null $cheque
     * @return Carteira
     */
    public function setCheque(?bool $cheque): Carteira
    {
        $this->cheque = $cheque;
        return $this;
    }

    /**
     * @return Banco|null
     */
    public function getBanco(): ?Banco
    {
        return $this->banco;
    }

    /**
     * @param Banco|null $banco
     * @return Carteira
     */
    public function setBanco(?Banco $banco): Carteira
    {
        $this->banco = $banco;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAgencia(): ?string
    {
        return $this->agencia;
    }

    /**
     * @param null|string $agencia
     * @return Carteira
     */
    public function setAgencia(?string $agencia): Carteira
    {
        $this->agencia = $agencia;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getConta(): ?string
    {
        return $this->conta;
    }

    /**
     * @param null|string $conta
     * @return Carteira
     */
    public function setConta(?string $conta): Carteira
    {
        $this->conta = $conta;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLimite(): ?float
    {
        return $this->limite;
    }

    /**
     * @param float|null $limite
     * @return Carteira
     */
    public function setLimite(?float $limite): Carteira
    {
        $this->limite = $limite;
        return $this;
    }

    /**
     * @return OperadoraCartao|null
     */
    public function getOperadoraCartao(): ?OperadoraCartao
    {
        return $this->operadoraCartao;
    }

    /**
     * @param OperadoraCartao|null $operadoraCartao
     * @return Carteira
     */
    public function setOperadoraCartao(?OperadoraCartao $operadoraCartao): Carteira
    {
        $this->operadoraCartao = $operadoraCartao;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAtual(): ?bool
    {
        return $this->atual;
    }

    /**
     * @param bool|null $atual
     * @return Carteira
     */
    public function setAtual(?bool $atual): Carteira
    {
        $this->atual = $atual;
        return $this;
    }


}
