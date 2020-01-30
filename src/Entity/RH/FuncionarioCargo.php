<?php

namespace App\Entity\RH;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\RH\FuncionarioCargoRepository")
 * @ORM\Table(name="rh_funcionario_cargo")
 */
class FuncionarioCargo implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="atual", type="boolean", nullable=false)
     *
     * @var null|bool
     */
    private $atual;

    /**
     *
     * @ORM\Column(name="comissao", type="decimal", nullable=false, precision=15, scale=2)
     *
     * @var null|float
     */
    private $comissao;

    /**
     *
     * @ORM\Column(name="dt_fim", type="datetime", nullable=true)
     *
     * @var null|\DateTime
     */
    private $dtFim;

    /**
     *
     * @ORM\Column(name="dt_inicio", type="datetime", nullable=false)
     *
     * @var null|\DateTime
     */
    private $dtInicio;

    /**
     *
     * @ORM\Column(name="salario", type="decimal", nullable=false, precision=15, scale=2)
     *
     * @var null|float
     */
    private $salario;

    /**
     *
     * @ORM\Column(name="salario_piso", type="decimal", nullable=false, precision=15, scale=2)
     *
     * @var null|float
     */
    private $salarioPiso;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RH\Cargo")
     * @ORM\JoinColumn(name="cargo_id", nullable=false)
     *
     * @var $cargo null|Cargo
     */
    private $cargo;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RH\Funcionario", inversedBy="cargos")
     * @ORM\JoinColumn(name="funcionario_id", nullable=false)
     *
     * @var $funcionario null|Funcionario
     */
    private $funcionario;

    /**
     * @return bool|null
     */
    public function getAtual(): ?bool
    {
        return $this->atual;
    }

    /**
     * @param bool|null $atual
     * @return FuncionarioCargo
     */
    public function setAtual(?bool $atual): FuncionarioCargo
    {
        $this->atual = $atual;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getComissao(): ?float
    {
        return $this->comissao;
    }

    /**
     * @param float|null $comissao
     * @return FuncionarioCargo
     */
    public function setComissao(?float $comissao): FuncionarioCargo
    {
        $this->comissao = $comissao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtFim(): ?\DateTime
    {
        return $this->dtFim;
    }

    /**
     * @param \DateTime|null $dtFim
     * @return FuncionarioCargo
     */
    public function setDtFim(?\DateTime $dtFim): FuncionarioCargo
    {
        $this->dtFim = $dtFim;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtInicio(): ?\DateTime
    {
        return $this->dtInicio;
    }

    /**
     * @param \DateTime|null $dtInicio
     * @return FuncionarioCargo
     */
    public function setDtInicio(?\DateTime $dtInicio): FuncionarioCargo
    {
        $this->dtInicio = $dtInicio;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSalario(): ?float
    {
        return $this->salario;
    }

    /**
     * @param float|null $salario
     * @return FuncionarioCargo
     */
    public function setSalario(?float $salario): FuncionarioCargo
    {
        $this->salario = $salario;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSalarioPiso(): ?float
    {
        return $this->salarioPiso;
    }

    /**
     * @param float|null $salarioPiso
     * @return FuncionarioCargo
     */
    public function setSalarioPiso(?float $salarioPiso): FuncionarioCargo
    {
        $this->salarioPiso = $salarioPiso;
        return $this;
    }

    /**
     * @return Cargo|null
     */
    public function getCargo(): ?Cargo
    {
        return $this->cargo;
    }

    /**
     * @param Cargo|null $cargo
     * @return FuncionarioCargo
     */
    public function setCargo(?Cargo $cargo): FuncionarioCargo
    {
        $this->cargo = $cargo;
        return $this;
    }

    /**
     * @return Funcionario|null
     */
    public function getFuncionario(): ?Funcionario
    {
        return $this->funcionario;
    }

    /**
     * @param Funcionario|null $funcionario
     * @return FuncionarioCargo
     */
    public function setFuncionario(?Funcionario $funcionario): FuncionarioCargo
    {
        $this->funcionario = $funcionario;
        return $this;
    }


}
