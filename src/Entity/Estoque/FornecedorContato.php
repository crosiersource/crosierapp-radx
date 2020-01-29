<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade Fornecedor.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\FornecedorContatoRepository")
 * @ORM\Table(name="est_fornecedor_contato")
 * @author Carlos Eduardo Pauluk
 */
class FornecedorContato implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Fornecedor")
     * @ORM\JoinColumn(nullable=false)
     * @var Fornecedor|null
     */
    private $fornecedor;

    /**
     *
     * @ORM\Column(name="tipo", type="string", nullable=true, length=50)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $tipo;

    /**
     *
     * @ORM\Column(name="valor", type="string", nullable=true, length=100)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $valor;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $obs;


    /**
     * @return Fornecedor|null
     */
    public function getFornecedor(): ?Fornecedor
    {
        return $this->fornecedor;
    }

    /**
     * @param Fornecedor|null $fornecedor
     */
    public function setFornecedor(?Fornecedor $fornecedor): void
    {
        $this->fornecedor = $fornecedor;
    }

    /**
     * @return null|string
     */
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    /**
     * @param null|string $tipo
     */
    public function setTipo(?string $tipo): void
    {
        $this->tipo = $tipo;
    }

    /**
     * @return null|string
     */
    public function getValor(): ?string
    {
        return $this->valor;
    }

    /**
     * @param null|string $valor
     */
    public function setValor(?string $valor): void
    {
        $this->valor = $valor;
    }

    /**
     * @return null|string
     */
    public function getObs(): ?string
    {
        return $this->obs;
    }

    /**
     * @param null|string $obs
     */
    public function setObs(?string $obs): void
    {
        $this->obs = $obs;
    }

}

