<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\GrupoAtributoRepository")
 * @ORM\Table(name="est_grupo_atributo")
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoAtributo implements EntityId
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
     * @ORM\Column(name="label", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $label;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Estoque\Atributo")
     * @ORM\JoinTable(name="est_atributo_grupo_atributo",
     *      joinColumns={@ORM\JoinColumn(name="grupo_atributo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="atributo_id", referencedColumnName="id")}
     *      )
     * @var Collection
     */
    private $atributos;


    public function __construct()
    {
        $this->atributos = new ArrayCollection();
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
     * @return GrupoAtributo
     */
    public function setUUID(?string $UUID): GrupoAtributo
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return GrupoAtributo
     */
    public function setLabel(?string $label): GrupoAtributo
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param string|null $descricao
     * @return GrupoAtributo
     */
    public function setDescricao(?string $descricao): GrupoAtributo
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAtributos(): Collection
    {
        return $this->atributos;
    }

    /**
     * @param Collection $atributos
     * @return GrupoAtributo
     */
    public function setAtributos(Collection $atributos): GrupoAtributo
    {
        $this->atributos = $atributos;
        return $this;
    }


}