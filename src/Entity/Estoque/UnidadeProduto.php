<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\UnidadeProdutoRepository")
 * @ORM\Table(name="est_unidade_produto")
 *
 * @author Carlos Eduardo Pauluk
 */
class UnidadeProduto implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=100)
     *
     * @var null|string
     */
    private $descricao;

    /**
     *
     * @ORM\Column(name="fator", type="integer", nullable=false)
     *
     * @var null|integer
     */
    private $fator;

    /**
     *
     * @ORM\Column(name="label", type="string", nullable=false, length=5)
     *
     * @var null|string
     */
    private $label;

    /**
     *
     * @ORM\Column(name="casas_decimais", type="integer", nullable=false)
     *
     * @var null|integer
     */
    private $casasDecimais;

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return UnidadeProduto
     */
    public function setDescricao(?string $descricao): UnidadeProduto
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFator(): ?int
    {
        return $this->fator;
    }

    /**
     * @param int|null $fator
     * @return UnidadeProduto
     */
    public function setFator(?int $fator): UnidadeProduto
    {
        $this->fator = $fator;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param null|string $label
     * @return UnidadeProduto
     */
    public function setLabel(?string $label): UnidadeProduto
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCasasDecimais(): ?int
    {
        return $this->casasDecimais;
    }

    /**
     * @param int|null $casasDecimais
     * @return UnidadeProduto
     */
    public function setCasasDecimais(?int $casasDecimais): UnidadeProduto
    {
        $this->casasDecimais = $casasDecimais;
        return $this;
    }


}