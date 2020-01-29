<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\DeptoRepository")
 * @ORM\Table(name="est_depto")
 *
 * @author Carlos Eduardo Pauluk
 */
class Depto implements EntityId
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
     * @var Grupo[]|ArrayCollection|null
     *
     * @ORM\OneToMany(
     *      targetEntity="Grupo",
     *      mappedBy="depto",
     *      orphanRemoval=true
     * )
     */
    private $grupos;


    public function __construct()
    {
        $this->grupos = new ArrayCollection();
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
     * @return Depto
     */
    public function setUUID(?string $UUID): Depto
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
     * @return Depto
     */
    public function setCodigo(?string $codigo): Depto
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
     * @return Depto
     */
    public function setNome(?string $nome): Depto
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
     * @return Grupo[]|ArrayCollection|null
     */
    public function getGrupos()
    {
        return $this->grupos;
    }

    /**
     * @param Grupo[]|ArrayCollection|null $grupos
     * @return Depto
     */
    public function setGrupos($grupos)
    {
        $this->grupos = $grupos;
        return $this;
    }



}