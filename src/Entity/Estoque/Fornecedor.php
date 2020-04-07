<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\FornecedorRepository")
 * @ORM\Table(name="est_fornecedor")
 *
 * @author Carlos Eduardo Pauluk
 */
class Fornecedor implements EntityId
{

    use EntityIdTrait;


    /**
     *
     * @ORM\Column(name="nome", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $nome = null;

    /**
     *
     * @ORM\Column(name="nome_fantasia", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $nomeFantasia = null;

    /**
     * CPF ou CNPJ.
     *
     * @ORM\Column(name="documento", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $documento = null;

    /**
     *
     * @ORM\Column(name="inscricao_estadual", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $inscricaoEstadual = null;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;


}