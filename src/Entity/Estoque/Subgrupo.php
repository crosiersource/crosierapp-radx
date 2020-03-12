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
     * Transient.
     * @Groups("entity")
     * @var string|null
     */
    public ?string $descricaoMontada = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Grupo")
     * @ORM\JoinColumn(name="grupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $grupo Grupo
     */
    public ?Grupo $grupo = null;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;

    /**
     * @return string|null
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->codigo . ' - ' . $this->nome;
    }


}