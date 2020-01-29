<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use CrosierSource\CrosierLibBaseBundle\Entity\Utils\EnderecoTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade Fornecedor.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\FornecedorEnderecoRepository")
 * @ORM\Table(name="bse_fornecedor_endereco")
 * @author Carlos Eduardo Pauluk
 */
class FornecedorEndereco implements EntityId
{

    use EntityIdTrait;

    use EnderecoTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Fornecedor")
     * @ORM\JoinColumn(name="fornecedor_id", nullable=false)
     * @var Fornecedor|null
     */
    private $fornecedor;

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


}

