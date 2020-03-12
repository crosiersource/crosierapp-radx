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
    public ?string $UUID = null;

    /**
     *
     * @ORM\Column(name="codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $codigo = null;

    /**
     *
     * @ORM\Column(name="nome", type="string", nullable=false)
     * @Groups("entity")
     * @NotUppercase()
     *
     * @var string|null
     */
    public ?string $nome = null;

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
    public $grupos;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;


    public function __construct()
    {
        $this->grupos = new ArrayCollection();
    }


    /**
     * @return string|null
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->codigo . ' - ' . $this->nome;
    }


}