<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade Bandeira de Cartão.
 * Ex.: MASTER MAESTRO, MASTER, VISA ELECTRON, VISA, etc.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\BandeiraCartaoRepository")
 * @ORM\Table(name="fin_bandeira_cartao")
 *
 * @author Carlos Eduardo Pauluk
 */
class BandeiraCartao implements EntityId
{
    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Assert\NotBlank()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Modo")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("entity")
     *
     * @var Modo|null
     */
    private $modo;

    /**
     * Para marcar diferentes nomes que podem ser utilizados para definir uma bandeira (ex.: MAESTRO ou MASTER MAESTRO ou M MAESTRO).
     *
     * @ORM\Column(name="labels", type="string", nullable=false, length=2000)
     * @Assert\NotBlank()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $labels;

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return BandeiraCartao
     */
    public function setDescricao(?string $descricao): BandeiraCartao
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return Modo|null
     */
    public function getModo(): ?Modo
    {
        return $this->modo;
    }

    /**
     * @param Modo|null $modo
     * @return BandeiraCartao
     */
    public function setModo(?Modo $modo): BandeiraCartao
    {
        $this->modo = $modo;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLabels(): ?string
    {
        return $this->labels;
    }

    /**
     * @param null|string $labels
     * @return BandeiraCartao
     */
    public function setLabels(?string $labels): BandeiraCartao
    {
        $this->labels = $labels;
        return $this;
    }


}
