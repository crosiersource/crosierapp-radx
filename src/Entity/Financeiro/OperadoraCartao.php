<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade Operadora de Cartões.
 * Ex.: RDCARD, CIELO, STONE.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\OperadoraCartaoRepository")
 * @ORM\Table(name="fin_operadora_cartao")
 *
 * @author Carlos Eduardo Pauluk
 */
class OperadoraCartao implements EntityId
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
     * Em qual Carteira as movimentações desta Operadora acontecem.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_id", nullable=true)
     * @Groups("entity")
     *
     * @var Carteira|null
     */
    private $carteira;

    /**
     * @return null|string
     */
    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    /**
     * @param null|string $descricao
     * @return OperadoraCartao
     */
    public function setDescricao(?string $descricao): OperadoraCartao
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return Carteira|null
     */
    public function getCarteira(): ?Carteira
    {
        return $this->carteira;
    }

    /**
     * @param Carteira|null $carteira
     * @return OperadoraCartao
     */
    public function setCarteira(?Carteira $carteira): OperadoraCartao
    {
        $this->carteira = $carteira;
        return $this;
    }

}
