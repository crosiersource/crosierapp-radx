<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade 'Centro de Custo'.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\CentroCustoRepository")
 * @ORM\Table(name="fin_centrocusto")
 *
 * @author Carlos Eduardo Pauluk
 */
class CentroCusto implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * @return int|null
     */
    public function getCodigo(): ?int
    {
        return $this->codigo;
    }

    /**
     * @param int|null $codigo
     * @return CentroCusto
     */
    public function setCodigo(?int $codigo): CentroCusto
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return CentroCusto
     */
    public function setDescricao(?string $descricao): CentroCusto
    {
        $this->descricao = $descricao;
        return $this;
    }


    public function getDescricaoMontada(): string
    {
        return str_pad($this->codigo, 2, '0', STR_PAD_LEFT) . ' - ' . $this->descricao;
    }

}
