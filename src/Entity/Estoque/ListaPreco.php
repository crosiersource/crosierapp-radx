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
 * @ORM\Table(name="est_lista_preco")
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
    public ?string $descricao;

    /**
     *
     * @ORM\Column(name="dt_vigencia_ini", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    public ?\DateTime $dtVigenciaIni;

    /**
     *
     * @ORM\Column(name="dt_vigencia_fim", type="datetime", nullable=true)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    public ?\DateTime $dtVigenciaFim;

    
}