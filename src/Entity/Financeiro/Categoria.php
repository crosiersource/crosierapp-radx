<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\CategoriaRepository")
 * @ORM\Table(name="fin_categoria")
 *
 * @author Carlos Eduardo Pauluk
 */
class Categoria implements EntityId
{
    use EntityIdTrait;

    public const MASK = '0.00.000.000.0000.00000';


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Categoria", inversedBy="subCategs")
     * @ORM\JoinColumn(name="pai_id",nullable=true)
     *
     * @MaxDepth(1)
     *
     * @var Categoria|null
     */
    private $pai;

    /**
     *
     * @ORM\OneToMany(
     *      targetEntity="Categoria",
     *      mappedBy="pai"
     * )
     * @Groups({"private"})
     *
     * @var Categoria[]|ArrayCollection|null
     */
    private $subCategs;

    /**
     *
     * @ORM\Column(name="descricao", type="string", nullable=false, length=200)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * Para os casos onde a movimentação é importada automaticamente, define qual a descrição padrão.
     *
     * @ORM\Column(name="descricao_padrao_moviment", type="string", nullable=false, length=200)
     *
     * @var string|null
     */
    private $descricaoPadraoMoviment;

    /**
     *
     * @ORM\Column(name="codigo", type="bigint", nullable=false)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $codigo;

    /**
     * A fim de relatórios.
     *
     * @ORM\Column(name="totalizavel", type="boolean", nullable=false)
     *
     * @var bool|null
     */
    private $totalizavel = false;

    /**
     * Informa se esta categoria necessita que o CentroCusto seja informado (ou se ele será automático).
     *
     * @ORM\Column(name="centro_custo_dif", type="boolean", nullable=false)
     *
     * @var bool|null
     */
    private $centroCustoDif = false;

    /**
     * Informa quais ROLES possuem acesso as informações (categoria.descricao e movimentacao.descricao).
     * Para mais de uma, informar separado por vírgula.
     *
     * @ORM\Column(name="roles_acess", type="string", nullable=true, length=2000)
     *
     * @var string|null
     */
    private $rolesAcess;

    /**
     *
     * Caso o usuário logado não possua nenhuma das "rolesAcess", então a descrição alternativa deve ser exibida.
     *
     * @ORM\Column(name="descricao_alternativa", type="string", nullable=true, length=200)
     *
     * @var string|null
     */
    private $descricaoAlternativa;

    /**
     * Atalho para não precisar ficar fazendo parse.
     *
     * @ORM\Column(name="codigo_super", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $codigoSuper;

    /**
     * Atalho para não precisar ficar fazendo parse.
     *
     * @ORM\Column(name="codigo_ord", type="bigint", nullable=true)
     * @var int|null
     */
    private $codigoOrd;

    /**
     * Transient.
     *
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricaoMontada;


    /**
     */
    public function __construct()
    {
        $this->subCategs = new ArrayCollection();
    }

    /**
     * @return Categoria|null
     */
    public function getPai(): ?Categoria
    {
        return $this->pai;
    }

    /**
     * @param Categoria|null $pai
     * @return Categoria
     */
    public function setPai(?Categoria $pai): Categoria
    {
        $this->pai = $pai;
        return $this;
    }

    /**
     * @return Categoria[]|ArrayCollection|null
     */
    public function getSubCategs()
    {
        return $this->subCategs;
    }

    /**
     * @param Categoria[]|ArrayCollection|null $subCategs
     * @return Categoria
     */
    public function setSubCategs($subCategs)
    {
        $this->subCategs = $subCategs;
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
     * @return Categoria
     */
    public function setDescricao(?string $descricao): Categoria
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescricaoPadraoMoviment(): ?string
    {
        return $this->descricaoPadraoMoviment;
    }

    /**
     * @param null|string $descricaoPadraoMoviment
     * @return Categoria
     */
    public function setDescricaoPadraoMoviment(?string $descricaoPadraoMoviment): Categoria
    {
        $this->descricaoPadraoMoviment = $descricaoPadraoMoviment;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCodigo(): ?int
    {
        return $this->codigo;
    }

    /**
     * @param int|null $codigo
     * @return Categoria
     */
    public function setCodigo(?int $codigo): Categoria
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTotalizavel(): ?bool
    {
        return $this->totalizavel;
    }

    /**
     * @param bool|null $totalizavel
     * @return Categoria
     */
    public function setTotalizavel(?bool $totalizavel): Categoria
    {
        $this->totalizavel = $totalizavel;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCentroCustoDif(): ?bool
    {
        return $this->centroCustoDif;
    }

    /**
     * @param bool|null $centroCustoDif
     * @return Categoria
     */
    public function setCentroCustoDif(?bool $centroCustoDif): Categoria
    {
        $this->centroCustoDif = $centroCustoDif;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRolesAcess(): ?string
    {
        return $this->rolesAcess;
    }

    /**
     * @param null|string $rolesAcess
     * @return Categoria
     */
    public function setRolesAcess(?string $rolesAcess): Categoria
    {
        $this->rolesAcess = $rolesAcess;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescricaoAlternativa(): ?string
    {
        return $this->descricaoAlternativa;
    }

    /**
     * @param null|string $descricaoAlternativa
     * @return Categoria
     */
    public function setDescricaoAlternativa(?string $descricaoAlternativa): Categoria
    {
        $this->descricaoAlternativa = $descricaoAlternativa;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCodigoSuper(): ?int
    {
        return $this->codigoSuper;
    }

    /**
     * @param int|null $codigoSuper
     * @return Categoria
     */
    public function setCodigoSuper(?int $codigoSuper): Categoria
    {
        $this->codigoSuper = $codigoSuper;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCodigoOrd(): ?int
    {
        return $this->codigoOrd;
    }

    /**
     * @param int|null $codigoOrd
     * @return Categoria
     */
    public function setCodigoOrd(?int $codigoOrd): Categoria
    {
        $this->codigoOrd = $codigoOrd;
        return $this;
    }

    /**
     * Retorna a descrição de uma Categoria no formato codigo + descricao (Ex.:
     * 2.01 - DESPESAS PESSOAIS).
     *
     * @return
     */
    public function getDescricaoMontada(): ?string
    {
        return $this->getCodigoM() . ' - ' . $this->getDescricao();
    }

    /**
     * Retorna a descrição de uma Categoria no formato codigo + descricao (Ex.:
     * 2.01 - DESPESAS PESSOAIS).
     *
     * @return
     */
    public function getDescricaoMontadaTree(): ?string
    {
        return str_pad('', strlen($this->getCodigo()) - 1, '.') . ' ' . $this->getCodigoM() . ' - ' . $this->getDescricao();
    }

    public function getCodigoM(): ?string
    {
        return StringUtils::mascarar($this->getCodigo(), self::MASK);
    }

    /**
     * Retorna somente o último 'bloco' do código.
     */
    public function getCodigoSufixo(): ?string
    {
        if ($this->getCodigo()) {
            if (!$this->getPai()) {
                return $this->getCodigo();
            }
            // else
            // Se tem pai, é o restante do código, removendo a parte do pai:
            return substr($this->getPai()->getCodigoM(), strlen($this->getPai()->getCodigoM()) + 1);

        }
        return null;
    }
}

