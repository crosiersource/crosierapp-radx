<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\AtributoRepository")
 * @ORM\Table(name="est_atributo")
 *
 * @author Carlos Eduardo Pauluk
 */
class Atributo implements EntityId
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
     * @ORM\Column(name="descricao", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     *
     * @ORM\Column(name="tipo", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $tipo;

    /**
     *
     * @ORM\Column(name="config", type="string", nullable=false)
     * @Groups("entity")
     * @NotUppercase()
     *
     * @var string|null
     */
    private $config;

    /**
     *
     * @ORM\Column(name="prefixo", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $prefixo;

    /**
     *
     * @ORM\Column(name="sufixo", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $sufixo;

    /**
     * @ORM\Column(name="atributo_pai_uuid", type="string", nullable=true, length=36)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $paiUUID;

    /**
     * @ORM\Column(name="primaria", type="string", nullable=true, length=1)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $primaria = 'S';

    /**
     * @ORM\Column(name="editavel", type="string", nullable=true, length=1)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $editavel = 'S';

    /**
     * @ORM\Column(name="visivel", type="string", nullable=true, length=1)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $visivel = 'S';

    /**
     * A ser respeitada quando esta opção faz parte de uma lista.
     *
     * @ORM\Column(name="ordem", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var integer|null
     */
    private $ordem;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $obs;

    /**
     * Transient.
     * Sem OneToMany pois a relação é feita com UUID.
     *
     * @var array|null
     */
    private $subatributos;

    /**
     * @return string|null
     */
    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    /**
     * @param string|null $UUID
     * @return Atributo
     */
    public function setUUID(?string $UUID): Atributo
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
     * @return Atributo
     */
    public function setLabel(?string $label): Atributo
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
     * @return Atributo
     */
    public function setDescricao(?string $descricao): Atributo
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    /**
     * @param string|null $tipo
     * @return Atributo
     */
    public function setTipo(?string $tipo): Atributo
    {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfig(): ?string
    {
        return $this->config;
    }

    /**
     * @param string|null $config
     * @return Atributo
     */
    public function setConfig(?string $config): Atributo
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefixo(): ?string
    {
        return $this->prefixo;
    }

    /**
     * @param string|null $prefixo
     * @return Atributo
     */
    public function setPrefixo(?string $prefixo): Atributo
    {
        $this->prefixo = $prefixo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSufixo(): ?string
    {
        return $this->sufixo;
    }

    /**
     * @param string|null $sufixo
     * @return Atributo
     */
    public function setSufixo(?string $sufixo): Atributo
    {
        $this->sufixo = $sufixo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaiUUID(): ?string
    {
        return $this->paiUUID;
    }

    /**
     * @param string|null $paiUUID
     * @return Atributo
     */
    public function setPaiUUID(?string $paiUUID): Atributo
    {
        $this->paiUUID = $paiUUID;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrimaria(): ?string
    {
        return $this->primaria;
    }

    /**
     * @param string|null $primaria
     * @return Atributo
     */
    public function setPrimaria(?string $primaria): Atributo
    {
        $this->primaria = $primaria;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEditavel(): ?string
    {
        return $this->editavel;
    }

    /**
     * @param string|null $editavel
     * @return Atributo
     */
    public function setEditavel(?string $editavel): Atributo
    {
        $this->editavel = $editavel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVisivel(): ?string
    {
        return $this->visivel;
    }

    /**
     * @param string|null $visivel
     * @return Atributo
     */
    public function setVisivel(?string $visivel): Atributo
    {
        $this->visivel = $visivel;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdem(): ?int
    {
        return $this->ordem;
    }

    /**
     * @param int|null $ordem
     * @return Atributo
     */
    public function setOrdem(?int $ordem): Atributo
    {
        $this->ordem = $ordem;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObs(): ?string
    {
        return $this->obs;
    }

    /**
     * @param string|null $obs
     * @return Atributo
     */
    public function setObs(?string $obs): Atributo
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getSubatributos(): ?array
    {
        return $this->subatributos;
    }

    /**
     * @param array|null $subatributos
     * @return Atributo
     */
    public function setSubatributos(?array $subatributos): Atributo
    {
        $this->subatributos = $subatributos;
        return $this;
    }


}