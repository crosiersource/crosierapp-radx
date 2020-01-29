<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade 'Grupo de Movimentações'.
 *
 * Para movimentações que são agrupadas e pagas através de outra movimentação (como Cartão de Crédito, conta em postos, etc).
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\GrupoRepository")
 * @ORM\Table(name="fin_grupo")
 *
 * @author Carlos Eduardo Pauluk
 */
class Grupo implements EntityId
{
    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=40)
     * @Groups("entity")
     */
    private $descricao;

    /**
     * Dia de vencimento no mês.
     *
     * 32 para sempre último (FIXME: meio burro isso).
     *
     * @ORM\Column(name="dia_vencto", type="integer", nullable=false)
     * @Groups("entity")
     */
    private $diaVencto;

    /**
     * Dia a partir do qual as movimentações são consideradas com vencimento
     * para próximo mês.
     *
     * @ORM\Column(name="dia_inicio", type="integer", nullable=false)
     * @Groups("entity")
     */
    private $diaInicioAprox = 1;

    /**
     * Informa se esta carteira pode conter movimentações com status ABERTA.
     * útil principalmente para o relatório de contas a pagar/receber, para não considerar movimentações de outras carteiras.
     *
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     * @Groups("entity")
     */
    private $ativo = true;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_pagante_id", nullable=true)
     *
     * @var Carteira|null
     * @Groups("entity")
     */
    private $carteiraPagantePadrao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Categoria")
     * @ORM\JoinColumn(name="categoria_padrao_id", nullable=true)
     *
     * @var Categoria|null
     * @Groups("entity")
     */
    private $categoriaPadrao;

    /**
     *
     * @var GrupoItem[]|ArrayCollection|null
     *
     * @ORM\OneToMany(
     *      targetEntity="GrupoItem",
     *      mappedBy="pai",
     *      orphanRemoval=true
     * )
     */
    private $itens;


    public function __construct()
    {
        $this->itens = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param mixed $descricao
     * @return Grupo
     */
    public function setDescricao($descricao): Grupo
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiaVencto()
    {
        return $this->diaVencto;
    }

    /**
     * @param mixed $diaVencto
     * @return Grupo
     */
    public function setDiaVencto($diaVencto): Grupo
    {
        $this->diaVencto = $diaVencto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiaInicioAprox()
    {
        return $this->diaInicioAprox;
    }

    /**
     * @param mixed $diaInicioAprox
     * @return Grupo
     */
    public function setDiaInicioAprox($diaInicioAprox): Grupo
    {
        $this->diaInicioAprox = $diaInicioAprox;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAtivo()
    {
        return $this->ativo;
    }

    /**
     * @param mixed $ativo
     * @return Grupo
     */
    public function setAtivo($ativo): Grupo
    {
        $this->ativo = $ativo;
        return $this;
    }

    /**
     * @return Carteira|null
     */
    public function getCarteiraPagantePadrao(): ?Carteira
    {
        return $this->carteiraPagantePadrao;
    }

    /**
     * @param Carteira|null $carteiraPagantePadrao
     * @return Grupo
     */
    public function setCarteiraPagantePadrao(?Carteira $carteiraPagantePadrao): Grupo
    {
        $this->carteiraPagantePadrao = $carteiraPagantePadrao;
        return $this;
    }

    /**
     * @return Categoria|null
     */
    public function getCategoriaPadrao(): ?Categoria
    {
        return $this->categoriaPadrao;
    }

    /**
     * @param Categoria|null $categoriaPadrao
     * @return Grupo
     */
    public function setCategoriaPadrao(?Categoria $categoriaPadrao): Grupo
    {
        $this->categoriaPadrao = $categoriaPadrao;
        return $this;
    }

    /**
     * @return GrupoItem[]|ArrayCollection|null
     */
    public function getItens()
    {
        return $this->itens;
    }

    /**
     * @param GrupoItem[]|ArrayCollection|null $itens
     * @return Grupo
     */
    public function setItens($itens): Grupo
    {
        $this->itens = $itens;
        return $this;
    }


}

