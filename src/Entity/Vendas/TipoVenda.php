<?php

namespace App\Entity\Vendas;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Vendas\TipoVendaRepository")
 * @ORM\Table(name="ven_tipo_venda")
 *
 * @author Carlos Eduardo Pauluk
 */
class TipoVenda implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=100)
     * @Groups("entity")
     *
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
     * @return TipoVenda
     */
    public function setDescricao(?string $descricao): TipoVenda
    {
        $this->descricao = $descricao;
        return $this;
    }


}
    