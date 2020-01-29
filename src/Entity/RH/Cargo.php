<?php

namespace App\Entity\RH;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\RH\CargoRepository")
 * @ORM\Table(name="rh_cargo")
 */
class Cargo implements EntityId
{

    use EntityIdTrait;

    /**
     * @var string|null
     * @ORM\Column(name="descricao", type="string", nullable=false, length=200)
     * @Groups("entity")
     */
    private $descricao;

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return Cargo
     */
    public function setDescricao(?string $descricao): Cargo
    {
        $this->descricao = $descricao;
        return $this;
    }


}
    