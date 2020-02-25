<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ProdutoRepository")
 * @ORM\Table(name="est_produto")
 *
 * @author Carlos Eduardo Pauluk
 */
class Produto implements EntityId
{

    use EntityIdTrait;

    /**
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    public ?string $UUID = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Depto")
     * @ORM\JoinColumn(name="depto_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $depto null|Depto
     */
    public ?Depto $depto = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Grupo")
     * @ORM\JoinColumn(name="grupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $grupo null|Grupo
     */
    public ?Grupo $grupo = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Subgrupo")
     * @ORM\JoinColumn(name="subgrupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $subgrupo null|Subgrupo
     */
    public ?Subgrupo $subgrupo = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Fornecedor")
     * @ORM\JoinColumn(name="fornecedor_id", nullable=false)
     *
     * @var $fornecedor null|Fornecedor
     */
    public ?Fornecedor $fornecedor = null;

    /**
     *
     * @ORM\Column(name="nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $nome = null;

    /**
     * ATIVO,INATIVO
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $status = null;

    /**
     * S,N
     *
     * @ORM\Column(name="composicao", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    public ?string $composicao = 'N';

    /**
     *
     * @ORM\OneToMany(targetEntity="ProdutoImagem", mappedBy="produto", cascade={"all"}, orphanRemoval=true)
     * @var ProdutoImagem[]|ArrayCollection|null
     * @ORM\OrderBy({"ordem" = "ASC"})
     *
     */
    public $imagens;

    /**
     *
     * @ORM\OneToMany(targetEntity="ProdutoComposicao", mappedBy="produtoPai", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var ProdutoComposicao[]|ArrayCollection|null
     * @ORM\OrderBy({"ordem" = "ASC"})
     *
     */
    public $composicoes;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;

    public function __construct()
    {
        $this->imagens = new ArrayCollection();
        $this->composicoes = new ArrayCollection();
    }


    /**
     * @return ProdutoImagem[]|ArrayCollection|null
     */
    public function getImagens()
    {
        return $this->imagens;
    }

    /**
     * @param ProdutoImagem[]|ArrayCollection|null $imagens
     * @return Produto
     */
    public function setImagens($imagens): Produto
    {
        $this->imagens = $imagens;
        return $this;
    }


    /**
     * @return ProdutoComposicao[]|ArrayCollection|null
     */
    public function getComposicoes()
    {
        return $this->composicoes;
    }

    /**
     * @param ProdutoComposicao[]|ArrayCollection|null $composicoes
     * @return Produto
     */
    public function setComposicoes($composicoes): Produto
    {
        $this->composicoes = $composicoes;
        return $this;
    }


}