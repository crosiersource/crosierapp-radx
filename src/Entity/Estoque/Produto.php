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
    private $UUID;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Depto")
     * @ORM\JoinColumn(name="depto_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $depto null|Depto
     */
    private $depto;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoDepto;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="depto_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeDepto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Grupo")
     * @ORM\JoinColumn(name="grupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $grupo null|Grupo
     */
    private $grupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="grupo_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoGrupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="grupo_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeGrupo;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Subgrupo")
     * @ORM\JoinColumn(name="subgrupo_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(1)
     * @var $subgrupo null|Subgrupo
     */
    private $subgrupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="subgrupo_codigo", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoSubgrupo;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="subgrupo_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeSubgrupo;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Fornecedor")
     * @ORM\JoinColumn(name="fornecedor_id", nullable=false)
     *
     * @var $fornecedor null|Fornecedor
     */
    private $fornecedor;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="fornecedor_nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nomeFornecedor;

    /**
     * Redundante: apenas para auxiliar acesso.
     *
     * @ORM\Column(name="fornecedor_documento", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $documentoFornecedor;

    /**
     *
     * @ORM\Column(name="nome", type="string", nullable=false)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $nome;

    /**
     *
     * @ORM\Column(name="titulo", type="string", nullable=false)
     * @Groups("entity")
     * @NotUppercase()
     * @var null|string
     */
    private $titulo;

    /**
     *
     * @ORM\Column(name="caracteristicas", type="string", nullable=true)
     * @Groups("entity")
     * @NotUppercase()
     *
     * @var null|string
     */
    private $caracteristicas;

    /**
     *
     * @ORM\Column(name="ean", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $ean;

    /**
     *
     * @ORM\Column(name="referencia", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $referencia;

    /**
     *
     * @ORM\Column(name="ncm", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $ncm;

    /**
     * ATIVO,INATIVO
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $status;

    /**
     * S,N
     *
     * @ORM\Column(name="composicao", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var null|string
     */
    private $composicao = 'N';

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\UnidadeProduto")
     * @ORM\JoinColumn(name="unidade_produto_id", nullable=false)
     * @Groups("entity")
     * @var $unidade null|UnidadeProduto
     */
    private $unidade;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $obs;

    /**
     * Caso este produto tenha sido importado de outro sistema, marca o código original.
     *
     * @ORM\Column(name="codigo_from", type="string", nullable=true)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $codigoFrom;

    /**
     * Porcentagem de preenchimento dos atributos deste produto.
     *
     * @ORM\Column(name="porcent_preench", type="float", nullable=true)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $porcentPreench;

    /**
     *
     * @ORM\OneToMany(targetEntity="ProdutoAtributo", mappedBy="produto", cascade={"all"}, orphanRemoval=true)
     * @var ProdutoAtributo[]|ArrayCollection|null
     * @ORM\OrderBy({"ordem" = "ASC"})
     *
     * @Groups("entity")
     *
     */
    private $atributos;

    /**
     *
     * @ORM\OneToMany(targetEntity="ProdutoImagem", mappedBy="produto", cascade={"all"}, orphanRemoval=true)
     * @var ProdutoImagem[]|ArrayCollection|null
     * @ORM\OrderBy({"ordem" = "ASC"})
     *
     */
    private $imagens;


    /**
     *
     * @ORM\OneToMany(targetEntity="ProdutoComposicao", mappedBy="produtoPai", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var ProdutoComposicao[]|ArrayCollection|null
     * @ORM\OrderBy({"ordem" = "ASC"})
     *
     */
    private $composicoes;

    public function __construct()
    {
        $this->atributos = new ArrayCollection();
        $this->imagens = new ArrayCollection();
        $this->composicoes = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    /**
     * @param string|null $UUID
     * @return Produto
     */
    public function setUUID(?string $UUID): Produto
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @return Depto|null
     */
    public function getDepto(): ?Depto
    {
        return $this->depto;
    }

    /**
     * @param Depto|null $depto
     * @return Produto
     */
    public function setDepto(?Depto $depto): Produto
    {
        $this->depto = $depto;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoDepto(): ?string
    {
        return $this->codigoDepto;
    }

    /**
     * @param string|null $codigoDepto
     * @return Produto
     */
    public function setCodigoDepto(?string $codigoDepto): Produto
    {
        $this->codigoDepto = $codigoDepto;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeDepto(): ?string
    {
        return $this->nomeDepto;
    }

    /**
     * @param string|null $nomeDepto
     * @return Produto
     */
    public function setNomeDepto(?string $nomeDepto): Produto
    {
        $this->nomeDepto = $nomeDepto;
        return $this;
    }

    /**
     * @return Grupo|null
     */
    public function getGrupo(): ?Grupo
    {
        return $this->grupo;
    }

    /**
     * @param Grupo|null $grupo
     * @return Produto
     */
    public function setGrupo(?Grupo $grupo): Produto
    {
        $this->grupo = $grupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoGrupo(): ?string
    {
        return $this->codigoGrupo;
    }

    /**
     * @param string|null $codigoGrupo
     * @return Produto
     */
    public function setCodigoGrupo(?string $codigoGrupo): Produto
    {
        $this->codigoGrupo = $codigoGrupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeGrupo(): ?string
    {
        return $this->nomeGrupo;
    }

    /**
     * @param string|null $nomeGrupo
     * @return Produto
     */
    public function setNomeGrupo(?string $nomeGrupo): Produto
    {
        $this->nomeGrupo = $nomeGrupo;
        return $this;
    }

    /**
     * @return Subgrupo|null
     */
    public function getSubgrupo(): ?Subgrupo
    {
        return $this->subgrupo;
    }

    /**
     * @param Subgrupo|null $subgrupo
     * @return Produto
     */
    public function setSubgrupo(?Subgrupo $subgrupo): Produto
    {
        $this->subgrupo = $subgrupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoSubgrupo(): ?string
    {
        return $this->codigoSubgrupo;
    }

    /**
     * @param string|null $codigoSubgrupo
     * @return Produto
     */
    public function setCodigoSubgrupo(?string $codigoSubgrupo): Produto
    {
        $this->codigoSubgrupo = $codigoSubgrupo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeSubgrupo(): ?string
    {
        return $this->nomeSubgrupo;
    }

    /**
     * @param string|null $nomeSubgrupo
     * @return Produto
     */
    public function setNomeSubgrupo(?string $nomeSubgrupo): Produto
    {
        $this->nomeSubgrupo = $nomeSubgrupo;
        return $this;
    }

    /**
     * @return Fornecedor|null
     */
    public function getFornecedor(): ?Fornecedor
    {
        return $this->fornecedor;
    }

    /**
     * @param Fornecedor|null $fornecedor
     * @return Produto
     */
    public function setFornecedor(?Fornecedor $fornecedor): Produto
    {
        $this->fornecedor = $fornecedor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeFornecedor(): ?string
    {
        return $this->nomeFornecedor;
    }

    /**
     * @param string|null $nomeFornecedor
     * @return Produto
     */
    public function setNomeFornecedor(?string $nomeFornecedor): Produto
    {
        $this->nomeFornecedor = $nomeFornecedor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumentoFornecedor(): ?string
    {
        return $this->documentoFornecedor;
    }

    /**
     * @param string|null $documentoFornecedor
     * @return Produto
     */
    public function setDocumentoFornecedor(?string $documentoFornecedor): Produto
    {
        $this->documentoFornecedor = $documentoFornecedor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param string|null $nome
     * @return Produto
     */
    public function setNome(?string $nome): Produto
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    /**
     * @param string|null $titulo
     * @return Produto
     */
    public function setTitulo(?string $titulo): Produto
    {
        $this->titulo = $titulo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCaracteristicas(): ?string
    {
        return $this->caracteristicas;
    }

    /**
     * @param string|null $caracteristicas
     * @return Produto
     */
    public function setCaracteristicas(?string $caracteristicas): Produto
    {
        $this->caracteristicas = $caracteristicas;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @param string|null $ean
     * @return Produto
     */
    public function setEan(?string $ean): Produto
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferencia(): ?string
    {
        return $this->referencia;
    }

    /**
     * @param string|null $referencia
     * @return Produto
     */
    public function setReferencia(?string $referencia): Produto
    {
        $this->referencia = $referencia;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNcm(): ?string
    {
        return $this->ncm;
    }

    /**
     * @param string|null $ncm
     * @return Produto
     */
    public function setNcm(?string $ncm): Produto
    {
        $this->ncm = $ncm;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Produto
     */
    public function setStatus(?string $status): Produto
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComposicao(): ?string
    {
        return $this->composicao;
    }

    /**
     * @param string|null $composicao
     * @return Produto
     */
    public function setComposicao(?string $composicao): Produto
    {
        $this->composicao = $composicao;
        return $this;
    }

    /**
     * @return UnidadeProduto|null
     */
    public function getUnidade(): ?UnidadeProduto
    {
        return $this->unidade;
    }

    /**
     * @param UnidadeProduto|null $unidade
     * @return Produto
     */
    public function setUnidade(?UnidadeProduto $unidade): Produto
    {
        $this->unidade = $unidade;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObs(): ?string
    {
        return $this->obs;
    }

    /**
     * @param string|null $obs
     * @return Produto
     */
    public function setObs(?string $obs): Produto
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoFrom(): ?string
    {
        return $this->codigoFrom;
    }

    /**
     * @param string|null $codigoFrom
     * @return Produto
     */
    public function setCodigoFrom(?string $codigoFrom): Produto
    {
        $this->codigoFrom = $codigoFrom;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPorcentPreench(): ?float
    {
        return $this->porcentPreench;
    }

    /**
     * @param float|null $porcentPreench
     * @return Produto
     */
    public function setPorcentPreench(?float $porcentPreench): Produto
    {
        $this->porcentPreench = $porcentPreench;
        return $this;
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
     * @param string $uuid
     * @return null|ProdutoAtributo
     */
    public function getAtributoByUUID(string $uuid): ?ProdutoAtributo
    {
        if ($this->getAtributos()) {
            foreach ($this->getAtributos() as $produtoAtributo) {
                if ($produtoAtributo->getAtributo()->getUUID() === $uuid) {
                    return $produtoAtributo;
                }
            }
        }
        return null;
    }

    /**
     * @return ProdutoAtributo[]|ArrayCollection|null
     */
    public function getAtributos()
    {
        return $this->atributos;
    }

    /**
     * @param ProdutoAtributo[]|ArrayCollection|null $atributos
     * @return Produto
     */
    public function setAtributos($atributos): Produto
    {
        $this->atributos = $atributos;
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