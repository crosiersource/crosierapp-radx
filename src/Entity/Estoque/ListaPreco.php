<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ListaPrecoRepository")
 * @ORM\Table(name="est_atributo")
 *
 * @author Carlos Eduardo Pauluk
 */
class ListaPreco implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     *
     * @ORM\Column(name="dt_vigencia_ini", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtVigenciaIni;

    /**
     *
     * @ORM\Column(name="dt_vigencia_fim", type="datetime", nullable=true)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtVigenciaFim;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Atributo")
     * @ORM\JoinTable(name="est_produto_preco_atributo",
     *      joinColumns={@ORM\JoinColumn(name="lista_preco_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="atributo_id", referencedColumnName="id")}
     * )
     * @var null|Atributo[]|array|Collection
     * @Groups("entity")
     */
    private $atributos;

// ...

    public function __construct()
    {
        $this->atributos = new ArrayCollection();
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
     * @return ListaPreco
     */
    public function setDescricao(?string $descricao): ListaPreco
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtVigenciaIni(): ?\DateTime
    {
        return $this->dtVigenciaIni;
    }

    /**
     * @param \DateTime|null $dtVigenciaIni
     * @return ListaPreco
     */
    public function setDtVigenciaIni(?\DateTime $dtVigenciaIni): ListaPreco
    {
        $this->dtVigenciaIni = $dtVigenciaIni;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtVigenciaFim(): ?\DateTime
    {
        return $this->dtVigenciaFim;
    }

    /**
     * @param \DateTime|null $dtVigenciaFim
     * @return ListaPreco
     */
    public function setDtVigenciaFim(?\DateTime $dtVigenciaFim): ListaPreco
    {
        $this->dtVigenciaFim = $dtVigenciaFim;
        return $this;
    }

    /**
     * @return Atributo[]|array|Collection|null
     */
    public function getAtributos()
    {
        return $this->atributos;
    }

    /**
     * @param Atributo[]|array|Collection|null $atributos
     * @return ListaPreco
     */
    public function setAtributos($atributos)
    {
        $this->atributos = $atributos;
        return $this;
    }


}