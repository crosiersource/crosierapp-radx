<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Entidade 'Movimentação'.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\MovimentacaoRepository")
 * @ORM\Table(name="fin_movimentacao")
 *
 * @author Carlos Eduardo Pauluk
 */
class Movimentacao implements EntityId
{

    use EntityIdTrait;

    /**
     * Utilizado, por exemplo, na importação (para tratar duplicidades).
     *
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     *
     * @var string|null
     */
    private $UUID;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Fatura")
     * @ORM\JoinColumn(name="fatura_id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var Fatura|null
     */
    private $fatura;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Modo")
     * @ORM\JoinColumn(name="modo_id", nullable=false)
     * @Groups("entity")
     *
     * @var Modo|null
     */
    private $modo;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Banco")
     * @ORM\JoinColumn(name="documento_banco_id", nullable=true)
     * @Groups("entity")
     *
     * @var Banco|null
     */
    private $documentoBanco;

    /**
     *
     * @ORM\Column(name="documento_num", type="string", nullable=true, length=200)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $documentoNum;


    /**
     * Quem paga esta movimentação.
     *
     * @ORM\Column(name="pessoa_sacado_id", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $sacado;

    /**
     * Para não precisar ir sempre consultar via API, principalmente em listagens.
     *
     * @ORM\Column(name="pessoa_sacado_info", type="string", nullable=true, length=400)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $sacadoInfo;

    /**
     * Quem recebe esta movimentação.
     *
     * @ORM\Column(name="pessoa_cedente_id", type="bigint", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $cedente;

    /**
     * Para não precisar ir sempre consultar via API, principalmente em listagens.
     *
     * @ORM\Column(name="pessoa_cedente_info", type="string", nullable=true, length=400)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $cedenteInfo;

    /**
     *
     * @ORM\Column(name="fis_nf_id", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $notaFiscalId;

    /**
     *
     * @ORM\Column(name="quitado", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $quitado = false;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\TipoLancto")
     * @ORM\JoinColumn(name="tipo_lancto_id", nullable=false)
     * @Groups("entity")
     *
     * @var TipoLancto|null
     */
    private $tipoLancto;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var Carteira|null
     */
    private $carteira;


    /**
     * Carteira informada em casos de TRANSF_PROPRIA.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Carteira")
     * @ORM\JoinColumn(name="carteira_destino_id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var Carteira|null
     */
    private $carteiraDestino;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Categoria")
     * @ORM\JoinColumn(name="categoria_id", nullable=false)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var Categoria|null
     */
    private $categoria;


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\CentroCusto")
     * @ORM\JoinColumn(name="centrocusto_id", nullable=false)
     * @Groups("entity")
     *
     * @var CentroCusto|null
     */
    private $centroCusto;


    /**
     * Caso seja uma movimentação agrupada em um Grupo de Movimentação (item).
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\GrupoItem", inversedBy="movimentacoes")
     * @ORM\JoinColumn(name="grupo_item_id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var GrupoItem|null
     */
    private $grupoItem;


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
     * @ORM\Column(name="descricao", type="string", nullable=false, length=500)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $descricao;

    /**
     * Transient.
     * @Groups("entity")
     * @var string|null
     */
    private $descricaoMontada;


    // ---------------------------------------------------------------------------------------
    // ---------- DATAS

    /**
     * Data em que a movimentação efetivamente aconteceu.
     *
     * @ORM\Column(name="dt_moviment", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtMoviment;

    /**
     * Data prevista para pagamento.
     *
     * @ORM\Column(name="dt_vencto", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtVencto;

    /**
     * Data prevista (postergando para dia útil) para pagamento.
     *
     * @ORM\Column(name="dt_vencto_efetiva", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtVenctoEfetiva;

    /**
     * Data em que a movimentação foi paga.
     *
     * @ORM\Column(name="dt_pagto", type="datetime", nullable=true)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtPagto;

    /**
     * Se dtPagto != null ? dtPagto : dtVencto.
     *
     * @ORM\Column(name="dt_util", type="datetime", nullable=false)
     * @Groups("entity")
     *
     * @var \DateTime|null
     */
    private $dtUtil;


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
     *
     * @ORM\Column(name="cheque_num_cheque", type="string", nullable=true, length=30)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $chequeNumCheque;

    // ---------------------------------------------------------------------------------------
    // ---------- CAMPOS PARA MOVIMENTAÇÃO DE CARTÃO

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\OperadoraCartao")
     * @ORM\JoinColumn(name="operadora_cartao_id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var OperadoraCartao|null
     */
    private $operadoraCartao;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\BandeiraCartao")
     * @ORM\JoinColumn(name="bandeira_cartao_id", nullable=true)
     * @Groups("entity")
     *
     * @var BandeiraCartao|null
     */
    private $bandeiraCartao;

    /**
     *
     * @ORM\Column(name="plano_pagto_cartao", type="string", nullable=true, length=50)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $planoPagtoCartao;

    // ---------------------------------------------------------------------------------------
    // ---------- CAMPOS PARA "RECORRÊNCIA"

    /**
     *
     * @ORM\Column(name="recorrente", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $recorrente = false;

    /**
     *
     * @ORM\Column(name="recorr_frequencia", type="string", nullable=true, length=50)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $recorrFrequencia;

    /**
     *
     * @ORM\Column(name="recorr_tipo_repet", type="string", nullable=true, length=50)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $recorrTipoRepet;

    /**
     * Utilizar 32 para marcar o último dia do mês.
     *
     * @ORM\Column(name="recorr_dia", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $recorrDia;

    /**
     * Utilizado para marcar a variação em relação ao dia em que seria o vencimento.
     * Exemplo: dia=32 (último dia do mês) + variacao=-2 >>> 2 dias antes do último dia do mês
     *
     *
     * @ORM\Column(name="recorr_variacao", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $recorrVariacao;

    // ---------------------------------------------------------------------------------------
    // ---------- CAMPOS PARA VALORES

    /**
     * Valor bruto da movimentação.
     *
     * @ORM\Column(name="valor", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $valor;

    /**
     * Possíveis descontos (sempre negativo).
     *
     * @ORM\Column(name="descontos", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $descontos;

    /**
     * Possíveis acréscimos (sempre positivo).
     *
     * @ORM\Column(name="acrescimos", type="decimal", nullable=true, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $acrescimos;

    /**
     * Valor total informado no campo e que é salvo no banco (pode divergir da
     * conta por algum motivo).
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @Groups("entity")
     *
     * @var float|null
     */
    private $valorTotal;

    // ---------------------------------------------------------------------------------------


    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeiro\Cadeia")
     * @ORM\JoinColumn(name="cadeia_id", referencedColumnName="id", nullable=true)
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @var Cadeia|null
     */
    private $cadeia;

    /**
     *
     * @ORM\Column(name="parcelamento", type="boolean", nullable=false)
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $parcelamento = false;

    /**
     * Caso a movimentação faça parte de uma cadeia, informa em qual posição.
     * Também é utilizado para armazenar o número da parcela.
     *
     * @ORM\Column(name="cadeia_ordem", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $cadeiaOrdem;

    /**
     * Informa o total de movimentações na cadeia. Campo apenas auxiliar.
     * Obs.: não pode nunca ser 1.
     *
     * @ORM\Column(name="cadeia_qtde", type="integer", nullable=true)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $cadeiaQtde;

    /**
     *
     * @ORM\Column(name="obs", type="string", nullable=true, length=5000)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $obs;

    /**
     * @return null|string
     */
    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    /**
     * @param null|string $UUID
     * @return Movimentacao
     */
    public function setUUID(?string $UUID): Movimentacao
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @return Fatura|null
     */
    public function getFatura(): ?Fatura
    {
        return $this->fatura;
    }

    /**
     * @param Fatura|null $fatura
     * @return Movimentacao
     */
    public function setFatura(?Fatura $fatura): Movimentacao
    {
        $this->fatura = $fatura;
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
     * @return Movimentacao
     */
    public function setModo(?Modo $modo): Movimentacao
    {
        $this->modo = $modo;
        return $this;
    }

    /**
     * @return Banco|null
     */
    public function getDocumentoBanco(): ?Banco
    {
        return $this->documentoBanco;
    }

    /**
     * @param Banco|null $documentoBanco
     * @return Movimentacao
     */
    public function setDocumentoBanco(?Banco $documentoBanco): Movimentacao
    {
        $this->documentoBanco = $documentoBanco;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentoNum(): ?string
    {
        return $this->documentoNum;
    }

    /**
     * @param null|string $documentoNum
     * @return Movimentacao
     */
    public function setDocumentoNum(?string $documentoNum): Movimentacao
    {
        $this->documentoNum = $documentoNum;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSacado(): ?int
    {
        return $this->sacado;
    }

    /**
     * @param int|null $sacado
     * @return Movimentacao
     */
    public function setSacado(?int $sacado): Movimentacao
    {
        $this->sacado = $sacado;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSacadoInfo(): ?string
    {
        return $this->sacadoInfo;
    }

    /**
     * @param null|string $sacadoInfo
     * @return Movimentacao
     */
    public function setSacadoInfo(?string $sacadoInfo): Movimentacao
    {
        $this->sacadoInfo = $sacadoInfo;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCedente(): ?int
    {
        return $this->cedente;
    }

    /**
     * @param int|null $cedente
     * @return Movimentacao
     */
    public function setCedente(?int $cedente): Movimentacao
    {
        $this->cedente = $cedente;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCedenteInfo(): ?string
    {
        return $this->cedenteInfo;
    }

    /**
     * @param null|string $cedenteInfo
     * @return Movimentacao
     */
    public function setCedenteInfo(?string $cedenteInfo): Movimentacao
    {
        $this->cedenteInfo = $cedenteInfo;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotaFiscalId(): ?int
    {
        return $this->notaFiscalId;
    }

    /**
     * @param int|null $notaFiscalId
     * @return Movimentacao
     */
    public function setNotaFiscalId(?int $notaFiscalId): Movimentacao
    {
        $this->notaFiscalId = $notaFiscalId;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getQuitado(): ?bool
    {
        return $this->quitado;
    }

    /**
     * @param bool|null $quitado
     * @return Movimentacao
     */
    public function setQuitado(?bool $quitado): Movimentacao
    {
        $this->quitado = $quitado;
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
     * @return Movimentacao
     */
    public function setTipoLancto(?TipoLancto $tipoLancto): Movimentacao
    {
        $this->tipoLancto = $tipoLancto;
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
     * @return Movimentacao
     */
    public function setCarteira(?Carteira $carteira): Movimentacao
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
     * @return Movimentacao
     */
    public function setCarteiraDestino(?Carteira $carteiraDestino): Movimentacao
    {
        $this->carteiraDestino = $carteiraDestino;
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
     * @return Movimentacao
     */
    public function setCategoria(?Categoria $categoria): Movimentacao
    {
        $this->categoria = $categoria;
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
     * @return Movimentacao
     */
    public function setCentroCusto(?CentroCusto $centroCusto): Movimentacao
    {
        $this->centroCusto = $centroCusto;
        return $this;
    }

    /**
     * @return GrupoItem|null
     */
    public function getGrupoItem(): ?GrupoItem
    {
        return $this->grupoItem;
    }

    /**
     * @param GrupoItem|null $grupoItem
     * @return Movimentacao
     */
    public function setGrupoItem(?GrupoItem $grupoItem): Movimentacao
    {
        $this->grupoItem = $grupoItem;
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
     * @return Movimentacao
     */
    public function setStatus(?string $status): Movimentacao
    {
        $this->status = $status;
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
     * @return Movimentacao
     */
    public function setDescricao(?string $descricao): Movimentacao
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtMoviment(): ?\DateTime
    {
        return $this->dtMoviment;
    }

    /**
     * @param \DateTime|null $dtMoviment
     * @return Movimentacao
     */
    public function setDtMoviment(?\DateTime $dtMoviment): Movimentacao
    {
        $this->dtMoviment = $dtMoviment;
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
     * @return Movimentacao
     */
    public function setDtVencto(?\DateTime $dtVencto): Movimentacao
    {
        $this->dtVencto = $dtVencto;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtVenctoEfetiva(): ?\DateTime
    {
        return $this->dtVenctoEfetiva;
    }

    /**
     * @param \DateTime|null $dtVenctoEfetiva
     * @return Movimentacao
     */
    public function setDtVenctoEfetiva(?\DateTime $dtVenctoEfetiva): Movimentacao
    {
        $this->dtVenctoEfetiva = $dtVenctoEfetiva;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtPagto(): ?\DateTime
    {
        return $this->dtPagto;
    }

    /**
     * @param \DateTime|null $dtPagto
     * @return Movimentacao
     */
    public function setDtPagto(?\DateTime $dtPagto): Movimentacao
    {
        $this->dtPagto = $dtPagto;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtUtil(): ?\DateTime
    {
        return $this->dtUtil;
    }

    /**
     * @param \DateTime|null $dtUtil
     * @return Movimentacao
     */
    public function setDtUtil(?\DateTime $dtUtil): Movimentacao
    {
        $this->dtUtil = $dtUtil;
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
     * @return Movimentacao
     */
    public function setChequeBanco(?Banco $chequeBanco): Movimentacao
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
     * @return Movimentacao
     */
    public function setChequeAgencia(?string $chequeAgencia): Movimentacao
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
     * @return Movimentacao
     */
    public function setChequeConta(?string $chequeConta): Movimentacao
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
     * @return Movimentacao
     */
    public function setChequeNumCheque(?string $chequeNumCheque): Movimentacao
    {
        $this->chequeNumCheque = $chequeNumCheque;
        return $this;
    }

    /**
     * @return OperadoraCartao|null
     */
    public function getOperadoraCartao(): ?OperadoraCartao
    {
        return $this->operadoraCartao;
    }

    /**
     * @param OperadoraCartao|null $operadoraCartao
     * @return Movimentacao
     */
    public function setOperadoraCartao(?OperadoraCartao $operadoraCartao): Movimentacao
    {
        $this->operadoraCartao = $operadoraCartao;
        return $this;
    }

    /**
     * @return BandeiraCartao|null
     */
    public function getBandeiraCartao(): ?BandeiraCartao
    {
        return $this->bandeiraCartao;
    }

    /**
     * @param BandeiraCartao|null $bandeiraCartao
     * @return Movimentacao
     */
    public function setBandeiraCartao(?BandeiraCartao $bandeiraCartao): Movimentacao
    {
        $this->bandeiraCartao = $bandeiraCartao;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlanoPagtoCartao(): ?string
    {
        return $this->planoPagtoCartao;
    }

    /**
     * @param null|string $planoPagtoCartao
     * @return Movimentacao
     */
    public function setPlanoPagtoCartao(?string $planoPagtoCartao): Movimentacao
    {
        $this->planoPagtoCartao = $planoPagtoCartao;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getRecorrente(): ?bool
    {
        return $this->recorrente;
    }

    /**
     * @param bool|null $recorrente
     * @return Movimentacao
     */
    public function setRecorrente(?bool $recorrente): Movimentacao
    {
        $this->recorrente = $recorrente;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRecorrFrequencia(): ?string
    {
        return $this->recorrFrequencia;
    }

    /**
     * @param null|string $recorrFrequencia
     * @return Movimentacao
     */
    public function setRecorrFrequencia(?string $recorrFrequencia): Movimentacao
    {
        $this->recorrFrequencia = $recorrFrequencia;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRecorrTipoRepet(): ?string
    {
        return $this->recorrTipoRepet;
    }

    /**
     * @param null|string $recorrTipoRepet
     * @return Movimentacao
     */
    public function setRecorrTipoRepet(?string $recorrTipoRepet): Movimentacao
    {
        $this->recorrTipoRepet = $recorrTipoRepet;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecorrDia(): ?int
    {
        return $this->recorrDia;
    }

    /**
     * @param int|null $recorrDia
     * @return Movimentacao
     */
    public function setRecorrDia(?int $recorrDia): Movimentacao
    {
        $this->recorrDia = $recorrDia;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecorrVariacao(): ?int
    {
        return $this->recorrVariacao;
    }

    /**
     * @param int|null $recorrVariacao
     * @return Movimentacao
     */
    public function setRecorrVariacao(?int $recorrVariacao): Movimentacao
    {
        $this->recorrVariacao = $recorrVariacao;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValor(): ?float
    {
        return $this->valor;
    }

    /**
     * @param float|null $valor
     * @return Movimentacao
     */
    public function setValor(?float $valor): Movimentacao
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getDescontos(): ?float
    {
        return $this->descontos;
    }

    /**
     * @param float|null $descontos
     * @return Movimentacao
     */
    public function setDescontos(?float $descontos): Movimentacao
    {
        $this->descontos = $descontos;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAcrescimos(): ?float
    {
        return $this->acrescimos;
    }

    /**
     * @param float|null $acrescimos
     * @return Movimentacao
     */
    public function setAcrescimos(?float $acrescimos): Movimentacao
    {
        $this->acrescimos = $acrescimos;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorTotal(): ?float
    {
        return $this->valorTotal;
    }

    /**
     * @param float|null $valorTotal
     * @return Movimentacao
     */
    public function setValorTotal(?float $valorTotal): Movimentacao
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    /**
     * @return Cadeia|null
     */
    public function getCadeia(): ?Cadeia
    {
        return $this->cadeia;
    }

    /**
     * @param Cadeia|null $cadeia
     * @return Movimentacao
     */
    public function setCadeia(?Cadeia $cadeia): Movimentacao
    {
        $this->cadeia = $cadeia;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getParcelamento(): ?bool
    {
        return $this->parcelamento;
    }

    /**
     * @param bool|null $parcelamento
     * @return Movimentacao
     */
    public function setParcelamento(?bool $parcelamento): Movimentacao
    {
        $this->parcelamento = $parcelamento;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCadeiaOrdem(): ?int
    {
        return $this->cadeiaOrdem;
    }


    /**
     * @param int|null $cadeiaOrdem
     * @return Movimentacao
     */
    public function setCadeiaOrdem(?int $cadeiaOrdem): Movimentacao
    {
        $this->cadeiaOrdem = $cadeiaOrdem;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCadeiaQtde(): ?int
    {
        return $this->cadeiaQtde;
    }

    /**
     * @param int|null $cadeiaQtde
     * @return Movimentacao
     */
    public function setCadeiaQtde(?int $cadeiaQtde): Movimentacao
    {
        $this->cadeiaQtde = $cadeiaQtde;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getObs(): ?string
    {
        return $this->obs;
    }

    /**
     * @param null|string $obs
     * @return Movimentacao
     */
    public function setObs(?string $obs): Movimentacao
    {
        $this->obs = $obs;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescricaoMontada(): string
    {
        $sufixo = '';

        if ($this->getCadeia() && $this->getParcelamento()) {
            $qtdeParcelas = $this->getCadeia()->getMovimentacoes()->count();
            $zerosfill = strlen('' . $qtdeParcelas);
            $zerosfill = $zerosfill < 2 ? 2 : $zerosfill;
            $sufixo .= ' (' . str_pad($this->getCadeiaOrdem(), $zerosfill, '0', STR_PAD_LEFT) . '/' . str_pad($qtdeParcelas, $zerosfill, '0', STR_PAD_LEFT) . ')';
        }

        if ($this->getDocumentoNum()) {
            $sufixo .= ' (Doc: ' . $this->getDocumentoNum() . ')';
        }

        if ($this->getChequeNumCheque()) {
            $nomeBanco = '';
            if ($this->getChequeBanco()) {
                $nomeBanco = $this->getChequeBanco()->getNome() . ' - ';
            }
            $sufixo .= '<br /> (CHQ: ' . $nomeBanco . 'nº ' . $this->getChequeNumCheque() . ')';
        }

        if ($this->getBandeiraCartao()) {
            $sufixo .= ' (Bandeira: ' . $this->getBandeiraCartao()->getDescricao() . ')';
        }

        if ($this->getOperadoraCartao()) {
            $sufixo .= ' (Operadora: ' . $this->getOperadoraCartao()->getDescricao() . ')';
        }

        if ($this->getGrupoItem()) {
            $sufixo .= ' (' . $this->getGrupoItem()->getDescricao() . ')';
        }

        return $this->getDescricao() . $sufixo;
    }


    /**
     * Calcula e seta o valor total.
     */
    public function calcValorTotal(): void
    {
        $valorTotal = $this->getValor() + $this->getDescontos() + $this->getAcrescimos();
        $this->setValorTotal($valorTotal);
    }


    /**
     * Retorna as outras movimentações que fazem parte da mesma cadeia desta.
     *
     * @return array
     */
    public function getOutrasMovimentacoesDaCadeia(): array
    {
        $outrasMovs = [];
        if ($this->getCadeia()) {
            foreach ($this->getCadeia()->getMovimentacoes() as $outraMov) {
                if ($outraMov->getId() !== $this->getId()) {
                    $outrasMovs[] = $outraMov;
                }
            }
        }
        return $outrasMovs;
    }

    /**
     * @return bool
     * @Groups("entity")
     */
    public function isTransferenciaEntreCarteiras(): bool
    {
        return
            $this->getCadeia() &&
            $this->getCadeia()->getMovimentacoes() &&
            $this->getCadeia()->getMovimentacoes()->count() === 2 &&
            $this->getCategoria() &&
            in_array($this->getCategoria()->getCodigo(), [199, 299], true);
    }

    /**
     * @return bool
     * @Groups("entity")
     */
    public function isTransferenciaEntradaCaixa(): bool
    {
        return
            $this->getCadeia() &&
            $this->getCadeia()->getMovimentacoes() &&
            $this->getCadeia()->getMovimentacoes()->count() === 3 &&
            $this->getCategoria() &&
            in_array($this->getCategoria()->getCodigo(), [101, 102, 199, 299], true);
    }

    /**
     * @return bool
     * @Groups("entity")
     */
    public function isUltimaNaCadeia(): bool
    {
        return
            $this->getCadeia() &&
            $this->getCadeia()->getMovimentacoes() &&
            $this->getCadeia()->getMovimentacoes()->count() === $this->getCadeiaOrdem();
    }

    /**
     * Nos casos das movimentações entre carteiras 1.99 ou 2.99...
     * @Groups("entity")
     * @MaxDepth(2)
     *
     * @return null|Movimentacao
     */
    public function getMovimentacaoOposta(): ?Movimentacao
    {
        if ($this->isTransferenciaEntreCarteiras()) {
            return $this->getOutrasMovimentacoesDaCadeia()[0];
        }
        return null;
    }
}

