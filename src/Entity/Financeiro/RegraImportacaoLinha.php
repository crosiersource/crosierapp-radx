<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade Regra de Importação de Linha.
 * Configura uma regra para setar corretamente a Movimentação ao importar uma linha de extrato.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\RegraImportacaoLinhaRepository")
 * @ORM\Table(name="fin_regra_import_linha")
 *
 * @author Carlos Eduardo Pauluk
 */
class RegraImportacaoLinha implements EntityId
{

    use EntityIdTrait;

    /**
     * Em casos especiais (como na utilização de named groups) posso usar uma regex em java.
     *
     * @ORM\Column(name="regra_regex_java", type="string", nullable=false, length=500)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $regraRegexJava;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\TipoLancto")
     * @ORM\JoinColumn(name="tipo_lancto_id", nullable=true)
     * @Groups("entity")
     *
     * @var TipoLancto|null
     */
    private $tipoLancto;

    /**
     *
     * @ORM\Column(name="status", type="string", nullable=false, length=50)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $status;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_id", nullable=true)
     * @Groups("entity")
     *
     * @var Carteira|null
     */
    private $carteira;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_destino_id", nullable=true)
     * @Groups("entity")
     *
     * @var Carteira|null
     */
    private $carteiraDestino;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\CentroCusto")
     * @ORM\JoinColumn(name="centrocusto_id", nullable=true)
     * @Groups("entity")
     *
     * @var CentroCusto|null
     */
    private $centroCusto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Modo")
     * @ORM\JoinColumn(name="modo_id", nullable=true)
     * @Groups("entity")
     *
     * @var Modo|null
     */
    private $modo;

    /**
     *
     * @NotUppercase()
     * @ORM\Column(name="padrao_descricao", type="string", nullable=false, length=500)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $padraoDescricao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Categoria")
     * @ORM\JoinColumn(name="categoria_id", nullable=true)
     * @Groups("entity")
     *
     * @var Categoria|null
     */
    private $categoria;

    /**
     * Para poder aplicar a regra somente se for positivo (1), negativo (-1) ou ambos (0)
     *
     * @ORM\Column(name="sinal_valor", type="integer", nullable=false)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $sinalValor;

    // ---------------------------------------------------------------------------------------
    // ---------- CAMPOS PARA "CHEQUE"

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Banco")
     * @ORM\JoinColumn(name="cheque_banco_id", nullable=true)
     * @Groups("entity")
     *
     * @var Banco|null
     */
    private $chequeBanco;

    /**
     * Código da agência (sem o dígito verificador).
     *
     * @ORM\Column(name="cheque_agencia", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $chequeAgencia;

    /**
     * Número da conta no banco (não segue um padrão).
     *
     * @ORM\Column(name="cheque_conta", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $chequeConta;

    /**
     * Número da conta no banco (não segue um padrão).
     *
     * @ORM\Column(name="cheque_num_cheque", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $chequeNumCheque;

    /**
     * @return null|string
     */
    public function getRegraRegexJava(): ?string
    {
        return $this->regraRegexJava;
    }

    /**
     * @param null|string $regraRegexJava
     * @return RegraImportacaoLinha
     */
    public function setRegraRegexJava(?string $regraRegexJava): RegraImportacaoLinha
    {
        $this->regraRegexJava = $regraRegexJava;
        return $this;
    }

    /**
     * @return TipoLancto|null
     */
    public function getTipoLancto(): ?TipoLancto
    {
        return $this->tipoLancto;
    }

    /**
     * @param TipoLancto|null $tipoLancto
     * @return RegraImportacaoLinha
     */
    public function setTipoLancto(?TipoLancto $tipoLancto): RegraImportacaoLinha
    {
        $this->tipoLancto = $tipoLancto;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param null|string $status
     * @return RegraImportacaoLinha
     */
    public function setStatus(?string $status): RegraImportacaoLinha
    {
        $this->status = $status;
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
     * @return RegraImportacaoLinha
     */
    public function setCarteira(?Carteira $carteira): RegraImportacaoLinha
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @return Carteira|null
     */
    public function getCarteiraDestino(): ?Carteira
    {
        return $this->carteiraDestino;
    }

    /**
     * @param Carteira|null $carteiraDestino
     * @return RegraImportacaoLinha
     */
    public function setCarteiraDestino(?Carteira $carteiraDestino): RegraImportacaoLinha
    {
        $this->carteiraDestino = $carteiraDestino;
        return $this;
    }

    /**
     * @return CentroCusto|null
     */
    public function getCentroCusto(): ?CentroCusto
    {
        return $this->centroCusto;
    }

    /**
     * @param CentroCusto|null $centroCusto
     * @return RegraImportacaoLinha
     */
    public function setCentroCusto(?CentroCusto $centroCusto): RegraImportacaoLinha
    {
        $this->centroCusto = $centroCusto;
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
     * @return RegraImportacaoLinha
     */
    public function setModo(?Modo $modo): RegraImportacaoLinha
    {
        $this->modo = $modo;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPadraoDescricao(): ?string
    {
        return $this->padraoDescricao;
    }

    /**
     * @param null|string $padraoDescricao
     * @return RegraImportacaoLinha
     */
    public function setPadraoDescricao(?string $padraoDescricao): RegraImportacaoLinha
    {
        $this->padraoDescricao = $padraoDescricao;
        return $this;
    }

    /**
     * @return Categoria|null
     */
    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    /**
     * @param Categoria|null $categoria
     * @return RegraImportacaoLinha
     */
    public function setCategoria(?Categoria $categoria): RegraImportacaoLinha
    {
        $this->categoria = $categoria;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSinalValor(): ?int
    {
        return $this->sinalValor;
    }

    /**
     * @param int|null $sinalValor
     * @return RegraImportacaoLinha
     */
    public function setSinalValor(?int $sinalValor): RegraImportacaoLinha
    {
        $this->sinalValor = $sinalValor;
        return $this;
    }

    /**
     * @return Banco|null
     */
    public function getChequeBanco(): ?Banco
    {
        return $this->chequeBanco;
    }

    /**
     * @param Banco|null $chequeBanco
     * @return RegraImportacaoLinha
     */
    public function setChequeBanco(?Banco $chequeBanco): RegraImportacaoLinha
    {
        $this->chequeBanco = $chequeBanco;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getChequeAgencia(): ?string
    {
        return $this->chequeAgencia;
    }

    /**
     * @param null|string $chequeAgencia
     * @return RegraImportacaoLinha
     */
    public function setChequeAgencia(?string $chequeAgencia): RegraImportacaoLinha
    {
        $this->chequeAgencia = $chequeAgencia;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getChequeConta(): ?string
    {
        return $this->chequeConta;
    }

    /**
     * @param null|string $chequeConta
     * @return RegraImportacaoLinha
     */
    public function setChequeConta(?string $chequeConta): RegraImportacaoLinha
    {
        $this->chequeConta = $chequeConta;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getChequeNumCheque(): ?string
    {
        return $this->chequeNumCheque;
    }

    /**
     * @param null|string $chequeNumCheque
     * @return RegraImportacaoLinha
     */
    public function setChequeNumCheque(?string $chequeNumCheque): RegraImportacaoLinha
    {
        $this->chequeNumCheque = $chequeNumCheque;
        return $this;
    }


    public function getSinalValorLabel()
    {
        switch ($this->sinalValor) {
            case 0:
                return 'Ambos';
            case 1:
                return 'Positivo';
            case -1:
                return 'Negativo';
            default:
                return null;
        }
    }


}
