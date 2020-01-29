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
     * Transient.
     *
     * @var string|null
     */
    private $descricaoMontada;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=3000)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $obs;

    /**
     *
     * @ORM\Column(name="url", type="string", nullable=true, length=2000)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $url;

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
     * @param int|null $codigo
     * @return TipoLancto
     */
    public function setCodigo(?int $codigo): TipoLancto
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
     * @return TipoLancto
     */
    public function setDescricao(?string $descricao): TipoLancto
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->getCodigo(true) . ' - ' . $this->getDescricao();
    }

    /**
     * @param null|string $descricaoMontada
     * @return TipoLancto
     */
    public function setDescricaoMontada(?string $descricaoMontada): TipoLancto
    {
        $this->descricaoMontada = $descricaoMontada;
        return $this;
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
     * @return TipoLancto
     */
    public function setObs(?string $obs): TipoLancto
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     * @return TipoLancto
     */
    public function setUrl(?string $url): TipoLancto
    {
        $this->url = $url;
        return $this;
    }


}
