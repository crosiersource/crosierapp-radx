<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade Tipo de Lançamento.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\TipoLanctoRepository")
 * @ORM\Table(name="fin_tipo_lancto")
 *
 * @author Carlos Eduardo Pauluk
 */
class TipoLancto implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var int|null
     */
    public ?int $codigo;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $descricao;

    /**
     *
     * @ORM\Column(name="icon", type="string", nullable=false, length=40)
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $icon;

    /**
     * Transient.
     *
     * @var string|null
     */
    public ?string $descricaoMontada;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $obs;

    /**
     *
     * @ORM\Column(name="url", type="string", nullable=true, length=2000)
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $url;

    /**
     * @param bool|null $format
     * @return int|string|null
     */
    public function getCodigo(?bool $format = false)
    {
        if ($format) {
            return str_pad($this->codigo, 2, '0', STR_PAD_LEFT);
        }
        return $this->codigo;
    }

    /**
     * @return null|string
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->getCodigo(true) . ' - ' . $this->descricao;
    }



}
