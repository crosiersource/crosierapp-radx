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
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Depto")
     * @ORM\JoinColumn(name="depto_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $depto Depto
     */
    public ?Depto $depto = null;

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
    public $subgrupos;

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
        $this->subgrupos = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->codigo . ' - ' . $this->nome;
    }


}