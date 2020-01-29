<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\GrupoRepository")
 * @ORM\Table(name="est_grupo")
 *
 * @author Carlos Eduardo Pauluk
 */
class Grupo implements EntityId
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
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Depto")
     * @ORM\JoinColumn(name="depto_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $depto Depto
     */
    private $depto;

    /**
     * Apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoDepto;

    /**
     * Apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeDepto;

    /**
     *
     * @var Subgrupo[]|ArrayCollection|null
     *
     * @ORM\OneToMany(
     *      targetEntity="Subgrupo",
     *      mappedBy="grupo",
     *      orphanRemoval=true
     * )
     */
    private $subgrupos;


    public function __construct()
    {
        $this->subgrupos = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    /**
     * @param string|null $UUID
     * @return Grupo
     */
    public function setUUID(?string $UUID): Grupo
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
     * @return Grupo
     */
    public function setCodigo(?string $codigo): Grupo
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
     * @return Grupo
     */
    public function setNome(?string $nome): Grupo
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
     * @return Grupo
     */
    public function setDescricaoMontada(?string $descricaoMontada): Grupo
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
     * @return Grupo
     */
    public function setDepto(Depto $depto): Grupo
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
     * @return Grupo
     */
    public function setCodigoDepto(?string $codigoDepto): Grupo
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
     * @return Grupo
     */
    public function setNomeDepto(?string $nomeDepto): Grupo
    {
        $this->nomeDepto = $nomeDepto;
        return $this;
    }

    /**
     * @return Grupo[]|ArrayCollection|null
     */
    public function getSubgrupos()
    {
        return $this->subgrupos;
    }

    /**
     * @param Grupo[]|ArrayCollection|null $subgrupos
     * @return Grupo
     */
    public function setSubgrupos($subgrupos)
    {
        $this->subgrupos = $subgrupos;
        return $this;
    }


}