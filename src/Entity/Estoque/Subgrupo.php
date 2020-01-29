<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\SubgrupoRepository")
 * @ORM\Table(name="est_subgrupo")
 *
 * @author Carlos Eduardo Pauluk
 */
class Subgrupo implements EntityId
{

    use EntityIdTrait;

    /**
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $UUID;

    /**
     *
     * @ORM\Column(name="codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nome;

    /**
     * Transient.
     * @Groups("entity")
     * @var string|null
     */
    private $descricaoMontada;

    /**
     * Redundante: apenas para auxiliar acesso.
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Depto")
     * @ORM\JoinColumn(name="depto_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $depto Depto
     */
    private $depto;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoDepto;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeDepto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Grupo")
     * @ORM\JoinColumn(name="grupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $grupo Grupo
     */
    private $grupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="grupo_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoGrupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="grupo_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeGrupo;

    /**
     * @return string|null
     */
    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    /**
     * @param string|null $UUID
     * @return Subgrupo
     */
    public function setUUID(?string $UUID): Subgrupo
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    /**
     * @param string|null $codigo
     * @return Subgrupo
     */
    public function setCodigo(?string $codigo): Subgrupo
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param string|null $nome
     * @return Subgrupo
     */
    public function setNome(?string $nome): Subgrupo
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescricaoMontada(): ?string
    {
        $this->descricaoMontada = $this->codigo . ' - ' . $this->nome;
        return $this->descricaoMontada;
    }

    /**
     * @param string|null $descricaoMontada
     * @return Subgrupo
     */
    public function setDescricaoMontada(?string $descricaoMontada): Subgrupo
    {
        $this->descricaoMontada = $descricaoMontada;
        return $this;
    }

    /**
     * @return Depto
     */
    public function getDepto(): Depto
    {
        return $this->depto;
    }

    /**
     * @param Depto $depto
     * @return Subgrupo
     */
    public function setDepto(Depto $depto): Subgrupo
    {
        $this->depto = $depto;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoDepto(): ?string
    {
        return $this->codigoDepto;
    }

    /**
     * @param string|null $codigoDepto
     * @return Subgrupo
     */
    public function setCodigoDepto(?string $codigoDepto): Subgrupo
    {
        $this->codigoDepto = $codigoDepto;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeDepto(): ?string
    {
        return $this->nomeDepto;
    }

    /**
     * @param string|null $nomeDepto
     * @return Subgrupo
     */
    public function setNomeDepto(?string $nomeDepto): Subgrupo
    {
        $this->nomeDepto = $nomeDepto;
        return $this;
    }

    /**
     * @return Grupo
     */
    public function getGrupo(): Grupo
    {
        return $this->grupo;
    }

    /**
     * @param Grupo $grupo
     * @return Subgrupo
     */
    public function setGrupo(Grupo $grupo): Subgrupo
    {
        $this->grupo = $grupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoGrupo(): ?string
    {
        return $this->codigoGrupo;
    }

    /**
     * @param string|null $codigoGrupo
     * @return Subgrupo
     */
    public function setCodigoGrupo(?string $codigoGrupo): Subgrupo
    {
        $this->codigoGrupo = $codigoGrupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeGrupo(): ?string
    {
        return $this->nomeGrupo;
    }

    /**
     * @param string|null $nomeGrupo
     * @return Subgrupo
     */
    public function setNomeGrupo(?string $nomeGrupo): Subgrupo
    {
        $this->nomeGrupo = $nomeGrupo;
        return $this;
    }


}