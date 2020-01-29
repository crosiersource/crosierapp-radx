<?php

namespace App\Entity\Estoque;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Estoque\ProdutoAtributoRepository")
 * @ORM\Table(name="est_produto_atributo")
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoAtributo implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Produto")
     * @ORM\JoinColumn(name="produto_id", nullable=false)
     *
     * @var null|Produto
     */
    private $produto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Estoque\Atributo")
     * @ORM\JoinColumn(name="atributo_id", nullable=false)
     * @Groups("entity")
     *
     * @var null|Atributo
     */
    private $atributo;

    /**
     *
     * @ORM\Column(name="aba", type="string", nullable=true)
     * @NotUppercase()
     * @Groups("entity")
     * @var null|string
     */
    private $aba;

    /**
     *
     * @ORM\Column(name="grupo", type="string", nullable=true)
     * @NotUppercase()
     * @Groups("entity")
     * @var null|string
     */
    private $grupo;

    /**
     *
     * @ORM\Column(name="ordem", type="integer", nullable=true)
     * @Groups("entity")
     * @var null|integer
     */
    private $ordem;

    /**
     *
     * @ORM\Column(name="quantif", type="string", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    private $quantif;

    /**
     *
     * @ORM\Column(name="precif", type="string", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    private $precif;

    /**
     * Informa se este campo entrará na conta para totalizar os 100% do prenchimento.
     *
     * @ORM\Column(name="soma_preench", type="string", nullable=false)
     * @Groups("entity")
     * @var null|string
     */
    private $somaPreench;

    /**
     *
     * @ORM\Column(name="valor", type="string", nullable=false)
     * @NotUppercase()
     * @Groups("entity")
     * @var null|string
     */
    private $valor;

    /**
     * @return Produto|null
     */
    public function getProduto(): ?Produto
    {
        return $this->produto;
    }

    /**
     * @param Produto|null $produto
     * @return ProdutoAtributo
     */
    public function setProduto(?Produto $produto): ProdutoAtributo
    {
        $this->produto = $produto;
        return $this;
    }

    /**
     * @return Atributo|null
     */
    public function getAtributo(): ?Atributo
    {
        return $this->atributo;
    }

    /**
     * @param Atributo|null $atributo
     * @return ProdutoAtributo
     */
    public function setAtributo(?Atributo $atributo): ProdutoAtributo
    {
        $this->atributo = $atributo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAba(): ?string
    {
        return $this->aba;
    }

    /**
     * @param string|null $aba
     * @return ProdutoAtributo
     */
    public function setAba(?string $aba): ProdutoAtributo
    {
        $this->aba = $aba;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGrupo(): ?string
    {
        return $this->grupo;
    }

    /**
     * @param string|null $grupo
     * @return ProdutoAtributo
     */
    public function setGrupo(?string $grupo): ProdutoAtributo
    {
        $this->grupo = $grupo;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdem(): ?int
    {
        return $this->ordem;
    }

    /**
     * @param int|null $ordem
     * @return ProdutoAtributo
     */
    public function setOrdem(?int $ordem): ProdutoAtributo
    {
        $this->ordem = $ordem;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuantif(): ?string
    {
        return $this->quantif;
    }

    /**
     * @param string|null $quantif
     * @return ProdutoAtributo
     */
    public function setQuantif(?string $quantif): ProdutoAtributo
    {
        $this->quantif = $quantif;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrecif(): ?string
    {
        return $this->precif;
    }

    /**
     * @param string|null $precif
     * @return ProdutoAtributo
     */
    public function setPrecif(?string $precif): ProdutoAtributo
    {
        $this->precif = $precif;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSomaPreench(): ?string
    {
        return $this->somaPreench;
    }

    /**
     * @param string|null $somaPreench
     * @return ProdutoAtributo
     */
    public function setSomaPreench(?string $somaPreench): ProdutoAtributo
    {
        $this->somaPreench = $somaPreench;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValor(): ?string
    {
        return $this->valor;
    }

    /**
     * @param string|null $valor
     * @return ProdutoAtributo
     */
    public function setValor(?string $valor): ProdutoAtributo
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param string|null $valor
     * @return ProdutoAtributo
     */
    public function setValorParsing(?string $valor): ProdutoAtributo
    {
        $this->valor = $this->paraValor($valor);
        return $this;
    }


    /**
     * @param string $valor
     * @return \DateTime|false|float|int|mixed|string|null
     */
    public function paraValor($valor)
    {
        switch ($this->getAtributo()->getTipo()) {
            case 'DECIMAL1':
            case 'DECIMAL2':
            case 'DECIMAL3':
            case 'DECIMAL4':
            case 'DECIMAL5':
                return DecimalUtils::parseStr($valor);
            case 'COMPO':
                return implode('|', $valor);
            case 'TAGS':
                return implode(',', $valor);
            default:
                return $valor;
        }
    }


}