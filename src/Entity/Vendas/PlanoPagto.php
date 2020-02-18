<?php

namespace App\Entity\Vendas;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Vendas\PlanoPagtoRepository")
 * @ORM\Table(name="ven_plano_pagto")
 *
 * @author Carlos Eduardo Pauluk
 */
class PlanoPagto implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo", type="string", nullable=false, length=255)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $codigo;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=255)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $descricao;


}