<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="nome", type="string", nullable=false)
     * @var null|string
     */
    private $nome;

    /**
     *
     * @ORM\Column(name="nome_fantasia", type="string", nullable=true)
     * @var null|string
     */
    private $nomeFantasia;

    /**
     *
     * @ORM\Column(name="categoria", type="string", nullable=true)
     * @var null|string
     */
    private $categoria;

    /**
     * CPF ou CNPJ.
     *
     * @ORM\Column(name="documento", type="string", nullable=true)
     * @var null|string
     */
    private $documento;

    /**
     *
     * @ORM\Column(name="inscricao_estadual", type="string", nullable=true)
     * @var null|string
     */
    private $inscricaoEstadual;

    /**
     *
     * @ORM\Column(name="codigo", type="string", nullable=false)
     * @var null|string
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true)
     * @var null|string
     */
    private $obs;

    /**
     * @return string|null
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param string|null $nome
     * @return Fornecedor
     */
    public function setNome(?string $nome): Fornecedor
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeFantasia(): ?string
    {
        return $this->nomeFantasia;
    }

    /**
     * @param string|null $nomeFantasia
     * @return Fornecedor
     */
    public function setNomeFantasia(?string $nomeFantasia): Fornecedor
    {
        $this->nomeFantasia = $nomeFantasia;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    /**
     * @param string|null $categoria
     * @return Fornecedor
     */
    public function setCategoria(?string $categoria): Fornecedor
    {
        $this->categoria = $categoria;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    /**
     * @param string|null $documento
     * @return Fornecedor
     */
    public function setDocumento(?string $documento): Fornecedor
    {
        $this->documento = $documento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInscricaoEstadual(): ?string
    {
        return $this->inscricaoEstadual;
    }

    /**
     * @param string|null $inscricaoEstadual
     * @return Fornecedor
     */
    public function setInscricaoEstadual(?string $inscricaoEstadual): Fornecedor
    {
        $this->inscricaoEstadual = $inscricaoEstadual;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    /**
     * @param string|null $codigo
     * @return Fornecedor
     */
    public function setCodigo(?string $codigo): Fornecedor
    {
        $this->codigo = $codigo;
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
     * @return Fornecedor
     */
    public function setObs(?string $obs): Fornecedor
    {
        $this->obs = $obs;
        return $this;
    }


}