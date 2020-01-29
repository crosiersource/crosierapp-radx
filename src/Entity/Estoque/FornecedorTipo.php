<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\FornecedorTipoRepository")
 * @ORM\Table(name="est_fornecedor_tipo")
 *
 * @author Carlos Eduardo Pauluk
 */
class FornecedorTipo implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=100)
     * @var null|string
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
     * @return FornecedorTipo
     */
    public function setDescricao(?string $descricao): FornecedorTipo
    {
        $this->descricao = $descricao;
        return $this;
    }


}