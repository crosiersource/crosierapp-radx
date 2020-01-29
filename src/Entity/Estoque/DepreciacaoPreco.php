<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\DepreciacaoPrecoRepository")
 * @ORM\Table(name="est_depreciacao_preco")
 *
 * @author Carlos Eduardo Pauluk
 */
class DepreciacaoPreco implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="porcentagem", type="decimal", nullable=false)
     * @Groups("entity")
     *
     * @var null|float
     */
    private $porcentagem;

    /**
     *
     * @ORM\Column(name="prazo_fim", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $prazoFim;

    /**
     *
     * @ORM\Column(name="prazo_ini", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var null|integer
     */
    private $prazoIni;


    /**
     * @return float|null
     */
    public function getPorcentagem(): ?float
    {
        return $this->porcentagem;
    }

    /**
     * @param float|null $porcentagem
     * @return DepreciacaoPreco
     */
    public function setPorcentagem(?float $porcentagem): DepreciacaoPreco
    {
        $this->porcentagem = $porcentagem;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrazoFim(): ?int
    {
        return $this->prazoFim;
    }

    /**
     * @param int|null $prazoFim
     * @return DepreciacaoPreco
     */
    public function setPrazoFim(?int $prazoFim): DepreciacaoPreco
    {
        $this->prazoFim = $prazoFim;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrazoIni(): ?int
    {
        return $this->prazoIni;
    }

    /**
     * @param int|null $prazoIni
     * @return DepreciacaoPreco
     */
    public function setPrazoIni(?int $prazoIni): DepreciacaoPreco
    {
        $this->prazoIni = $prazoIni;
        return $this;
    }


}