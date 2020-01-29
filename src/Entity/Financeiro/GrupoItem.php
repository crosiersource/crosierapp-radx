<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Entidade que representa um 'item de um Grupo de Movimentações' (como a fatura
 * de um mês do cartão de crédito, por exemplo).
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\GrupoItemRepository")
 * @ORM\Table(name="fin_grupo_item")
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoItem implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Grupo", inversedBy="itens")
     * @ORM\JoinColumn(name="grupo_pai_id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(1)
     *
     * @var Grupo|null
     */
    private $pai;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * Movimentações desta carteira não poderão ter suas datas alteradas para antes desta.
     *
     * @ORM\Column(name="dt_vencto", type="date", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtVencto;

    /**
     * Para efeitos de navegação.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Financeiro\GrupoItem")
     * @ORM\JoinColumn(name="anterior_id", referencedColumnName="id")
     * @Groups("entity")
     * @MaxDepth(1)
     *
     * @var GrupoItem|null
     */
    private $anterior;

    /**
     * Para efeitos de navegação.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Financeiro\GrupoItem")
     * @ORM\JoinColumn(name="proximo_id", referencedColumnName="id")
     * @Groups("entity")
     * @MaxDepth(1)
     *
     * @var GrupoItem|null
     */
    private $proximo;

    /**
     * Utilizado para informar o limite disponível.
     *
     * @ORM\Column(name="valor_informado", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     *
     */
    private $valorInformado;

    /**
     *
     * @ORM\OneToMany(targetEntity="Movimentacao", mappedBy="grupoItem")
     *
     * @var Movimentacao[]|ArrayCollection|null
     */
    private $movimentacoes;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_pagante_id", nullable=true)
     * @Groups("entity")
     *
     * @var Carteira|null
     */
    private $carteiraPagante;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Movimentacao")
     * @ORM\JoinColumn(name="movimentacao_pagante_id", nullable=true)
     * @Groups("entity")
     *
     * @var Movimentacao|null
     */
    private $movimentacaoPagante;

    /**
     *
     * @ORM\Column(name="fechado", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $fechado = false;

    /**
     */
    public function __construct()
    {
        $this->movimentacoes = new ArrayCollection();
    }

    /**
     * @return Grupo|null
     */
    public function getPai(): ?Grupo
    {
        return $this->pai;
    }

    /**
     * @param Grupo|null $pai
     * @return GrupoItem
     */
    public function setPai(?Grupo $pai): GrupoItem
    {
        $this->pai = $pai;
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
     * @return GrupoItem
     */
    public function setDescricao(?string $descricao): GrupoItem
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtVencto(): ?\DateTime
    {
        return $this->dtVencto;
    }

    /**
     * @param \DateTime|null $dtVencto
     * @return GrupoItem
     */
    public function setDtVencto(?\DateTime $dtVencto): GrupoItem
    {
        $this->dtVencto = $dtVencto;
        return $this;
    }

    /**
     * @return GrupoItem|null
     */
    public function getAnterior(): ?GrupoItem
    {
        return $this->anterior;
    }

    /**
     * @param GrupoItem|null $anterior
     * @return GrupoItem
     */
    public function setAnterior(?GrupoItem $anterior): GrupoItem
    {
        $this->anterior = $anterior;
        return $this;
    }

    /**
     * @return GrupoItem|null
     */
    public function getProximo(): ?GrupoItem
    {
        return $this->proximo;
    }

    /**
     * @param GrupoItem|null $proximo
     * @return GrupoItem
     */
    public function setProximo(?GrupoItem $proximo): GrupoItem
    {
        $this->proximo = $proximo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorInformado(): ?float
    {
        return $this->valorInformado;
    }

    /**
     * @param float|null $valorInformado
     * @return GrupoItem
     */
    public function setValorInformado(?float $valorInformado): GrupoItem
    {
        $this->valorInformado = $valorInformado;
        return $this;
    }

    /**
     * @return Movimentacao[]|ArrayCollection|null
     */
    public function getMovimentacoes()
    {
        return $this->movimentacoes;
    }

    /**
     * @param Movimentacao[]|ArrayCollection|null $movimentacoes
     * @return GrupoItem
     */
    public function setMovimentacoes($movimentacoes)
    {
        $this->movimentacoes = $movimentacoes;
        return $this;
    }

    /**
     * @return Carteira|null
     */
    public function getCarteiraPagante(): ?Carteira
    {
        return $this->carteiraPagante;
    }

    /**
     * @param Carteira|null $carteiraPagante
     * @return GrupoItem
     */
    public function setCarteiraPagante(?Carteira $carteiraPagante): GrupoItem
    {
        $this->carteiraPagante = $carteiraPagante;
        return $this;
    }

    /**
     * @return Movimentacao|null
     */
    public function getMovimentacaoPagante(): ?Movimentacao
    {
        return $this->movimentacaoPagante;
    }

    /**
     * @param Movimentacao|null $movimentacaoPagante
     * @return GrupoItem
     */
    public function setMovimentacaoPagante(?Movimentacao $movimentacaoPagante): GrupoItem
    {
        $this->movimentacaoPagante = $movimentacaoPagante;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFechado(): ?bool
    {
        return $this->fechado;
    }

    /**
     * @param bool|null $fechado
     * @return GrupoItem
     */
    public function setFechado(?bool $fechado): GrupoItem
    {
        $this->fechado = $fechado;
        return $this;
    }

    /**
     * Método auxiliar para cálculo.
     *
     * @return number
     */
    public function getValorLanctos()
    {
        if ($this->getMovimentacoes() && count($this->getMovimentacoes()) > 0) {
            $bdValor = 0.0;
            foreach ($this->getMovimentacoes() as $m) {
                if (strpos($m->getCategoria()->getCodigo(), 0) === '1') {
                    $bdValor += $m->getValorTotal();
                } else {
                    $bdValor -= $m->getValorTotal();
                }
            }
            return abs($bdValor);
        }
        return 0.0;

    }

    /**
     * Método auxiliar para view.
     *
     * @return number
     */
    public function getDiferenca()
    {
        return $this->getValorLanctos() - $this->getValorInformado();
    }
}
