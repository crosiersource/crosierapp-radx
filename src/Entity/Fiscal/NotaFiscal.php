<?php

namespace App\Entity\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade Nota Fiscal.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\NotaFiscalRepository")
 * @ORM\Table(name="fis_nf")
 *
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscal implements EntityId
{

    use EntityIdTrait;

    /**
     * @ORM\Column(name="uuid", type="string", nullable=true, length=32)
     * @var null|string
     * @NotUppercase()
     * @Groups("entity")
     */
    private $uuid;

    /**
     *
     * @ORM\Column(name="ambiente", type="string", nullable=true, length=4)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $ambiente;

    /**
     *
     * Número randômico utilizado na geração do nome do arquivo XML, para poder saber qual foi o nome do último arquivo gerado, evitando duplicidades.
     *
     * @ORM\Column(name="rand_faturam", type="string", nullable=true)
     * @var null|string
     */
    private $randFaturam;

    /**
     * $cNF = rand(10000000, 99999999);
     *
     * @ORM\Column(name="cnf", type="string", nullable=true, length=8)
     * @var null|string
     */
    private $cnf;

    /**
     *
     * @ORM\Column(name="natureza_operacao", type="string", nullable=false, length=60)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $naturezaOperacao;

    /**
     *
     * @ORM\Column(name="finalidade_nf", type="string", nullable=false, length=30)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $finalidadeNf;

    /**
     *
     * @ORM\Column(name="chave_acesso", type="string", nullable=true, length=44)
     * @var null|string
     */
    private $chaveAcesso;

    /**
     *
     * @ORM\Column(name="protocolo_autoriz", type="string", nullable=true, length=255)
     * @var null|string
     */
    private $protocoloAutorizacao;

    /**
     *
     * @ORM\Column(name="dt_protocolo_autoriz", type="datetime", nullable=true)
     * @var null|\DateTime
     */
    private $dtProtocoloAutorizacao;

    /**
     *
     * @ORM\Column(name="dt_emissao", type="datetime", nullable=true)
     * @var null|\DateTime
     *
     * @Groups("entity")
     */
    private $dtEmissao;

    /**
     *
     * @ORM\Column(name="dt_saient", type="datetime", nullable=true)
     * @var null|\DateTime
     *
     * @Groups("entity")
     */
    private $dtSaiEnt;

    /**
     *
     * @ORM\Column(name="numero", type="integer", nullable=false)
     * @var null|int
     *
     * @Groups("entity")
     */
    private $numero;

    /**
     *
     * @ORM\Column(name="serie", type="integer", nullable=false)
     * @var null|int
     *
     * @Groups("entity")
     */
    private $serie;

    /**
     *
     * @ORM\Column(name="tipo", type="string", nullable=true, length=30)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $tipoNotaFiscal;

    /**
     *
     * @ORM\Column(name="entrada_saida", nullable=false)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $entradaSaida;


    /**
     * @ORM\Column(name="documento_emitente", type="string", nullable=false)
     * @var null|string
     * @Groups("entity")
     */
    private $documentoEmitente;

    /**
     * @ORM\Column(name="xnome_emitente", type="string", nullable=false)
     * @var null|string
     * @Groups("entity")
     */
    private $xNomeEmitente;

    /**
     * @ORM\Column(name="inscr_est_emitente", type="string", nullable=true)
     * @var null|string
     * @Groups("entity")
     */
    private $inscricaoEstadualEmitente;

    /**
     *
     * @ORM\Column(name="cep_emitente", type="string", nullable=true, length=9)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $cepEmitente;

    /**
     *
     * @ORM\Column(name="logradouro_emitente", type="string", nullable=true, length=200)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $logradouroEmitente;

    /**
     *
     * @ORM\Column(name="numero_emitente", type="string", nullable=true, length=200)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $numeroEmitente;

    /**
     *
     * @ORM\Column(name="fone_emitente", type="string", nullable=true, length=50)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $foneEmitente;

    /**
     *
     * @ORM\Column(name="bairro_emitente", type="string", nullable=true, length=120)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $bairroEmitente;

    /**
     *
     * @ORM\Column(name="cidade_emitente", type="string", nullable=true, length=120)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $cidadeEmitente;

    /**
     *
     * @ORM\Column(name="estado_emitente", type="string", nullable=true, length=2)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $estadoEmitente;


    /**
     * @ORM\Column(name="documento_destinatario", type="string", nullable=false)
     * @var null|string
     * @Groups("entity")
     */
    private $documentoDestinatario;

    /**
     * @ORM\Column(name="xnome_destinatario", type="string", nullable=false)
     * @var null|string
     * @Groups("entity")
     */
    private $xNomeDestinatario;

    /**
     * @ORM\Column(name="inscr_est", type="string", nullable=false)
     * @var null|string
     * @Groups("entity")
     */
    private $inscricaoEstadualDestinatario;

    /**
     *
     * @ORM\Column(name="logradouro_destinatario", type="string", nullable=true, length=200)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $logradouroDestinatario;

    /**
     *
     * @ORM\Column(name="numero_destinatario", type="string", nullable=true, length=200)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $numeroDestinatario;

    /**
     *
     * @ORM\Column(name="bairro_destinatario", type="string", nullable=true, length=120)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $bairroDestinatario;

    /**
     *
     * @ORM\Column(name="cidade_destinatario", type="string", nullable=true, length=120)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $cidadeDestinatario;

    /**
     *
     * @ORM\Column(name="estado_destinatario", type="string", nullable=true, length=2)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $estadoDestinatario;

    /**
     *
     * @ORM\Column(name="cep_destinatario", type="string", nullable=true, length=9)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $cepDestinatario;

    /**
     *
     * @ORM\Column(name="fone_destinatario", type="string", nullable=true, length=50)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $foneDestinatario;

    /**
     *
     * @ORM\Column(name="email_destinatario", type="string", nullable=true, length=200)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $emailDestinatario;

    /**
     *
     * @ORM\Column(name="motivo_cancelamento", type="string", nullable=true, length=3000)
     * @var null|string
     *
     */
    private $motivoCancelamento;

    /**
     *
     * @ORM\Column(name="info_compl", type="string", nullable=true, length=3000)
     * @var null|string
     */
    private $infoCompl;

    /**
     *
     * @ORM\Column(name="total_descontos", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $totalDescontos;

    /**
     *
     * @ORM\Column(name="subtotal", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     */
    private $subtotal;

    /**
     *
     * @ORM\Column(name="valor_total", type="decimal", nullable=false, precision=15, scale=2)
     * @var null|float
     *
     * @Groups("entity")
     */
    private $valorTotal;


    /**
     * @ORM\Column(name="transp_documento", type="string", nullable=false)
     * @var null|string
     */
    private $transpDocumento;

    /**
     * @ORM\Column(name="transp_nome", type="string", nullable=false)
     * @var null|string
     */
    private $transpNome;

    /**
     * @ORM\Column(name="transp_inscr_est", type="string", nullable=true)
     * @var null|string
     */
    private $transpInscricaoEstadual;

    /**
     * @ORM\Column(name="transp_endereco", type="string", nullable=true)
     * @var null|string
     */
    private $transpEndereco;

    /**
     *
     * @ORM\Column(name="transp_cidade", type="string", nullable=true, length=120)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $transpCidade;

    /**
     *
     * @ORM\Column(name="transp_estado", type="string", nullable=true, length=2)
     * @var string|null
     *
     * @Groups("entity")
     */
    private $transpEstado;

    /**
     *
     * @ORM\Column(name="transp_especie_volumes", type="string", nullable=true, length=200)
     * @var null|string
     */
    private $transpEspecieVolumes;

    /**
     *
     * @ORM\Column(name="transp_marca_volumes", type="string", nullable=true, length=200)
     * @var null|string
     */
    private $transpMarcaVolumes;

    /**
     *
     * @ORM\Column(name="transp_modalidade_frete", type="string", nullable=false, length=30)
     * @var null|string
     */
    private $transpModalidadeFrete;

    /**
     *
     * @ORM\Column(name="transp_numeracao_volumes", type="string", nullable=true, length=200)
     * @var null|string
     */
    private $transpNumeracaoVolumes;

    /**
     *
     * @ORM\Column(name="transp_peso_bruto", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $transpPesoBruto;

    /**
     *
     * @ORM\Column(name="transp_peso_liquido", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $transpPesoLiquido;

    /**
     *
     * @ORM\Column(name="transp_qtde_volumes", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $transpQtdeVolumes;

    /**
     *
     * @ORM\Column(name="transp_valor_total_frete", type="decimal", nullable=true, precision=15, scale=2)
     * @var null|float
     */
    private $transpValorTotalFrete;

    /**
     *
     * @ORM\Column(name="indicador_forma_pagto", type="string", nullable=false, length=30)
     * @var null|string
     */
    private $indicadorFormaPagto;


    /**
     *
     * @ORM\Column(name="a03id_nf_referenciada", type="string", nullable=true, length=100)
     * @var null|string
     */
    private $a03idNfReferenciada;

    /**
     *
     * @ORM\Column(name="xml_nota", type="string", nullable=true)
     * @var null|string
     *
     * @NotUppercase()
     */
    private $xmlNota;

    /**
     * Informa se o XML é de um resumo <resNFe> (ainda não foi baixada o XML da nota completa).
     *
     * @ORM\Column(name="resumo", type="boolean", nullable=true)
     * @var null|bool
     *
     * @Groups("entity")
     */
    private $resumo;

    /**
     *
     * @ORM\Column(name="nrec", type="string", length=30, nullable=true)
     * @var null|string
     */
    private $nRec;

    /**
     *
     * @ORM\Column(name="cstat_lote", type="integer", nullable=true)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $cStatLote;

    /**
     *
     * @ORM\Column(name="xmotivo_lote", type="string", length=255, nullable=true)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $xMotivoLote;

    /**
     *
     * @ORM\Column(name="cstat", type="integer", nullable=true)
     * @var null|int
     *
     * @Groups("entity")
     */
    private $cStat;

    /**
     *
     * @ORM\Column(name="xmotivo", type="string", length=255, nullable=true)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $xMotivo;

    /**
     *
     * @ORM\Column(name="manifest_dest", type="string", length=255, nullable=true)
     * @var null|string
     *
     * @Groups("entity")
     */
    private $manifestDest;

    /**
     * Informa quando foi alterado o status do último $manifestDest.
     *
     * @ORM\Column(name="dt_manifest_dest", type="datetime", nullable=true)
     * @var null|\DateTime
     *
     * @Groups("entity")
     */
    private $dtManifestDest;

    /**
     *
     * @ORM\Column(name="nsu", type="integer", nullable=false)
     * @var null|int
     *
     * @Groups("entity")
     */
    public $nsu;

    /**
     *
     * @var NotaFiscalItem[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="NotaFiscalItem",
     *      cascade={"all"},
     *      mappedBy="notaFiscal",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"ordem" = "ASC"})
     */
    private $itens;

    /**
     *
     * @var NotaFiscalEvento[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="NotaFiscalEvento",
     *      cascade={"all"},
     *      mappedBy="notaFiscal",
     *      orphanRemoval=true
     * )
     */
    private $eventos;

    /**
     *
     * @var NotaFiscalCartaCorrecao[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="NotaFiscalCartaCorrecao",
     *      cascade={"all"},
     *      mappedBy="notaFiscal",
     *      orphanRemoval=true
     * )
     */
    private $cartasCorrecao;

    /**
     *
     * @var NotaFiscalHistorico[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="NotaFiscalHistorico",
     *      cascade={"all"},
     *      mappedBy="notaFiscal",
     *      orphanRemoval=true
     * )
     */
    private $historicos;


    public function __construct()
    {
        $this->itens = new ArrayCollection();
        $this->eventos = new ArrayCollection();
        $this->historicos = new ArrayCollection();
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param null|string $uuid
     * @return NotaFiscal
     */
    public function setUuid(?string $uuid): NotaFiscal
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRandFaturam(): ?string
    {
        return $this->randFaturam;
    }

    /**
     * @param null|string $randFaturam
     * @return NotaFiscal
     */
    public function setRandFaturam(?string $randFaturam): NotaFiscal
    {
        $this->randFaturam = $randFaturam;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCnf(): ?string
    {
        return $this->cnf;
    }

    /**
     * @param null|string $cnf
     * @return NotaFiscal
     */
    public function setCnf(?string $cnf): NotaFiscal
    {
        $this->cnf = $cnf;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getChaveAcesso(): ?string
    {
        return $this->chaveAcesso;
    }

    /**
     * @param null|string $chaveAcesso
     * @return NotaFiscal
     */
    public function setChaveAcesso(?string $chaveAcesso): NotaFiscal
    {
        $this->chaveAcesso = $chaveAcesso;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProtocoloAutorizacao(): ?string
    {
        return $this->protocoloAutorizacao;
    }

    /**
     * @param null|string $protocoloAutorizacao
     * @return NotaFiscal
     */
    public function setProtocoloAutorizacao(?string $protocoloAutorizacao): NotaFiscal
    {
        $this->protocoloAutorizacao = $protocoloAutorizacao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtProtocoloAutorizacao(): ?\DateTime
    {
        return $this->dtProtocoloAutorizacao;
    }

    /**
     * @param \DateTime|null $dtProtocoloAutorizacao
     * @return NotaFiscal
     */
    public function setDtProtocoloAutorizacao(?\DateTime $dtProtocoloAutorizacao): NotaFiscal
    {
        $this->dtProtocoloAutorizacao = $dtProtocoloAutorizacao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtEmissao(): ?\DateTime
    {
        return $this->dtEmissao;
    }

    /**
     * @param \DateTime|null $dtEmissao
     * @return NotaFiscal
     */
    public function setDtEmissao(?\DateTime $dtEmissao): NotaFiscal
    {
        $this->dtEmissao = $dtEmissao;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtSaiEnt(): ?\DateTime
    {
        return $this->dtSaiEnt;
    }

    /**
     * @param \DateTime|null $dtSaiEnt
     * @return NotaFiscal
     */
    public function setDtSaiEnt(?\DateTime $dtSaiEnt): NotaFiscal
    {
        $this->dtSaiEnt = $dtSaiEnt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumero(): ?int
    {
        return $this->numero;
    }

    /**
     * @param int|null $numero
     * @return NotaFiscal
     */
    public function setNumero(?int $numero): NotaFiscal
    {
        $this->numero = $numero;
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
     * @return NotaFiscal
     */
    public function setValorTotal(?float $valorTotal): NotaFiscal
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumentoEmitente(): ?string
    {
        return $this->documentoEmitente;
    }

    /**
     * @param string|null $documentoEmitente
     * @return NotaFiscal
     */
    public function setDocumentoEmitente(?string $documentoEmitente): NotaFiscal
    {
        $this->documentoEmitente = preg_replace("/[\D]/", '', $documentoEmitente);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getXNomeEmitente(): ?string
    {
        return $this->xNomeEmitente;
    }

    /**
     * @param string|null $xNomeEmitente
     * @return NotaFiscal
     */
    public function setXNomeEmitente(?string $xNomeEmitente): NotaFiscal
    {
        $this->xNomeEmitente = $xNomeEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInscricaoEstadualEmitente(): ?string
    {
        return $this->inscricaoEstadualEmitente;
    }

    /**
     * @param string|null $inscricaoEstadualEmitente
     * @return NotaFiscal
     */
    public function setInscricaoEstadualEmitente(?string $inscricaoEstadualEmitente): NotaFiscal
    {
        $this->inscricaoEstadualEmitente = $inscricaoEstadualEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCepEmitente(): ?string
    {
        return $this->cepEmitente;
    }

    /**
     * @param string|null $cepEmitente
     * @return NotaFiscal
     */
    public function setCepEmitente(?string $cepEmitente): NotaFiscal
    {
        $this->cepEmitente = $cepEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogradouroEmitente(): ?string
    {
        return $this->logradouroEmitente;
    }

    /**
     * @param string|null $logradouroEmitente
     * @return NotaFiscal
     */
    public function setLogradouroEmitente(?string $logradouroEmitente): NotaFiscal
    {
        $this->logradouroEmitente = $logradouroEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroEmitente(): ?string
    {
        return $this->numeroEmitente;
    }

    /**
     * @param string|null $numeroEmitente
     * @return NotaFiscal
     */
    public function setNumeroEmitente(?string $numeroEmitente): NotaFiscal
    {
        $this->numeroEmitente = $numeroEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFoneEmitente(): ?string
    {
        return $this->foneEmitente;
    }

    /**
     * @param string|null $foneEmitente
     * @return NotaFiscal
     */
    public function setFoneEmitente(?string $foneEmitente): NotaFiscal
    {
        $this->foneEmitente = $foneEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBairroEmitente(): ?string
    {
        return $this->bairroEmitente;
    }

    /**
     * @param string|null $bairroEmitente
     * @return NotaFiscal
     */
    public function setBairroEmitente(?string $bairroEmitente): NotaFiscal
    {
        $this->bairroEmitente = $bairroEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCidadeEmitente(): ?string
    {
        return $this->cidadeEmitente;
    }

    /**
     * @param string|null $cidadeEmitente
     * @return NotaFiscal
     */
    public function setCidadeEmitente(?string $cidadeEmitente): NotaFiscal
    {
        $this->cidadeEmitente = $cidadeEmitente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEstadoEmitente(): ?string
    {
        return $this->estadoEmitente;
    }

    /**
     * @param string|null $estadoEmitente
     * @return NotaFiscal
     */
    public function setEstadoEmitente(?string $estadoEmitente): NotaFiscal
    {
        $this->estadoEmitente = $estadoEmitente;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTipoNotaFiscal(): ?string
    {
        return $this->tipoNotaFiscal;
    }

    /**
     * @param null|string $tipoNotaFiscal
     * @return NotaFiscal
     */
    public function setTipoNotaFiscal(?string $tipoNotaFiscal): NotaFiscal
    {
        $this->tipoNotaFiscal = $tipoNotaFiscal;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntradaSaida(): ?string
    {
        return $this->entradaSaida;
    }

    /**
     * @param string|null $entradaSaida
     * @return NotaFiscal
     */
    public function setEntradaSaida(?string $entradaSaida): NotaFiscal
    {
        $this->entradaSaida = $entradaSaida;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSerie(): ?int
    {
        return $this->serie;
    }

    /**
     * @param int|null $serie
     * @return NotaFiscal
     */
    public function setSerie(?int $serie): NotaFiscal
    {
        $this->serie = $serie;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumentoDestinatario(): ?string
    {
        return $this->documentoDestinatario;
    }

    /**
     * @param string|null $documentoDestinatario
     * @return NotaFiscal
     */
    public function setDocumentoDestinatario(?string $documentoDestinatario): NotaFiscal
    {
        $this->documentoDestinatario = preg_replace("/[\D]/", '', $documentoDestinatario);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getXNomeDestinatario(): ?string
    {
        return $this->xNomeDestinatario;
    }

    /**
     * @param string|null $xNomeDestinatario
     * @return NotaFiscal
     */
    public function setXNomeDestinatario(?string $xNomeDestinatario): NotaFiscal
    {
        $this->xNomeDestinatario = $xNomeDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInscricaoEstadualDestinatario(): ?string
    {
        return $this->inscricaoEstadualDestinatario;
    }

    /**
     * @param string|null $inscricaoEstadualDestinatario
     * @return NotaFiscal
     */
    public function setInscricaoEstadualDestinatario(?string $inscricaoEstadualDestinatario): NotaFiscal
    {
        $this->inscricaoEstadualDestinatario = $inscricaoEstadualDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogradouroDestinatario(): ?string
    {
        return $this->logradouroDestinatario;
    }

    /**
     * @param string|null $logradouroDestinatario
     * @return NotaFiscal
     */
    public function setLogradouroDestinatario(?string $logradouroDestinatario): NotaFiscal
    {
        $this->logradouroDestinatario = $logradouroDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroDestinatario(): ?string
    {
        return $this->numeroDestinatario;
    }

    /**
     * @param string|null $numeroDestinatario
     * @return NotaFiscal
     */
    public function setNumeroDestinatario(?string $numeroDestinatario): NotaFiscal
    {
        $this->numeroDestinatario = $numeroDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBairroDestinatario(): ?string
    {
        return $this->bairroDestinatario;
    }

    /**
     * @param string|null $bairroDestinatario
     * @return NotaFiscal
     */
    public function setBairroDestinatario(?string $bairroDestinatario): NotaFiscal
    {
        $this->bairroDestinatario = $bairroDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCidadeDestinatario(): ?string
    {
        return $this->cidadeDestinatario;
    }

    /**
     * @param string|null $cidadeDestinatario
     * @return NotaFiscal
     */
    public function setCidadeDestinatario(?string $cidadeDestinatario): NotaFiscal
    {
        $this->cidadeDestinatario = $cidadeDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEstadoDestinatario(): ?string
    {
        return $this->estadoDestinatario;
    }

    /**
     * @param string|null $estadoDestinatario
     * @return NotaFiscal
     */
    public function setEstadoDestinatario(?string $estadoDestinatario): NotaFiscal
    {
        $this->estadoDestinatario = $estadoDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCepDestinatario(): ?string
    {
        return $this->cepDestinatario;
    }

    /**
     * @param string|null $cepDestinatario
     * @return NotaFiscal
     */
    public function setCepDestinatario(?string $cepDestinatario): NotaFiscal
    {
        $this->cepDestinatario = $cepDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFoneDestinatario(): ?string
    {
        return $this->foneDestinatario;
    }

    /**
     * @param string|null $foneDestinatario
     * @return NotaFiscal
     */
    public function setFoneDestinatario(?string $foneDestinatario): NotaFiscal
    {
        $this->foneDestinatario = $foneDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailDestinatario(): ?string
    {
        return $this->emailDestinatario;
    }

    /**
     * @param string|null $emailDestinatario
     * @return NotaFiscal
     */
    public function setEmailDestinatario(?string $emailDestinatario): NotaFiscal
    {
        $this->emailDestinatario = $emailDestinatario;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMotivoCancelamento(): ?string
    {
        return $this->motivoCancelamento;
    }

    /**
     * @param null|string $motivoCancelamento
     * @return NotaFiscal
     */
    public function setMotivoCancelamento(?string $motivoCancelamento): NotaFiscal
    {
        $this->motivoCancelamento = $motivoCancelamento;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAmbiente(): ?string
    {
        return $this->ambiente;
    }

    /**
     * @param null|string $ambiente
     * @return NotaFiscal
     */
    public function setAmbiente(?string $ambiente): NotaFiscal
    {
        $this->ambiente = $ambiente;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getInfoCompl(): ?string
    {
        return $this->infoCompl;
    }

    /**
     * @param null|string $infoCompl
     * @return NotaFiscal
     */
    public function setInfoCompl(?string $infoCompl): NotaFiscal
    {
        $this->infoCompl = $infoCompl;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalDescontos(): ?float
    {
        return $this->totalDescontos;
    }

    /**
     * @param float|null $totalDescontos
     * @return NotaFiscal
     */
    public function setTotalDescontos(?float $totalDescontos): NotaFiscal
    {
        $this->totalDescontos = $totalDescontos;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    /**
     * @param float|null $subtotal
     * @return NotaFiscal
     */
    public function setSubtotal(?float $subtotal): NotaFiscal
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspDocumento(): ?string
    {
        return $this->transpDocumento;
    }

    /**
     * @param string|null $transpDocumento
     * @return NotaFiscal
     */
    public function setTranspDocumento(?string $transpDocumento): NotaFiscal
    {
        $this->transpDocumento = $transpDocumento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspNome(): ?string
    {
        return $this->transpNome;
    }

    /**
     * @param string|null $transpNome
     * @return NotaFiscal
     */
    public function setTranspNome(?string $transpNome): NotaFiscal
    {
        $this->transpNome = $transpNome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspInscricaoEstadual(): ?string
    {
        return $this->transpInscricaoEstadual;
    }

    /**
     * @param string|null $transpInscricaoEstadual
     * @return NotaFiscal
     */
    public function setTranspInscricaoEstadual(?string $transpInscricaoEstadual): NotaFiscal
    {
        $this->transpInscricaoEstadual = $transpInscricaoEstadual;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspEndereco(): ?string
    {
        return $this->transpEndereco;
    }

    /**
     * @param string|null $transpEndereco
     * @return NotaFiscal
     */
    public function setTranspEndereco(?string $transpEndereco): NotaFiscal
    {
        $this->transpEndereco = $transpEndereco;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspCidade(): ?string
    {
        return $this->transpCidade;
    }

    /**
     * @param string|null $transpCidade
     * @return NotaFiscal
     */
    public function setTranspCidade(?string $transpCidade): NotaFiscal
    {
        $this->transpCidade = $transpCidade;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranspEstado(): ?string
    {
        return $this->transpEstado;
    }

    /**
     * @param string|null $transpEstado
     * @return NotaFiscal
     */
    public function setTranspEstado(?string $transpEstado): NotaFiscal
    {
        $this->transpEstado = $transpEstado;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTranspEspecieVolumes(): ?string
    {
        return $this->transpEspecieVolumes;
    }

    /**
     * @param null|string $transpEspecieVolumes
     * @return NotaFiscal
     */
    public function setTranspEspecieVolumes(?string $transpEspecieVolumes): NotaFiscal
    {
        $this->transpEspecieVolumes = $transpEspecieVolumes;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTranspMarcaVolumes(): ?string
    {
        return $this->transpMarcaVolumes;
    }

    /**
     * @param null|string $transpMarcaVolumes
     * @return NotaFiscal
     */
    public function setTranspMarcaVolumes(?string $transpMarcaVolumes): NotaFiscal
    {
        $this->transpMarcaVolumes = $transpMarcaVolumes;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTranspModalidadeFrete(): ?string
    {
        return $this->transpModalidadeFrete;
    }

    /**
     * @param null|string $transpModalidadeFrete
     * @return NotaFiscal
     */
    public function setTranspModalidadeFrete(?string $transpModalidadeFrete): NotaFiscal
    {
        $this->transpModalidadeFrete = $transpModalidadeFrete;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTranspNumeracaoVolumes(): ?string
    {
        return $this->transpNumeracaoVolumes;
    }

    /**
     * @param null|string $transpNumeracaoVolumes
     * @return NotaFiscal
     */
    public function setTranspNumeracaoVolumes(?string $transpNumeracaoVolumes): NotaFiscal
    {
        $this->transpNumeracaoVolumes = $transpNumeracaoVolumes;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTranspPesoBruto(): ?float
    {
        return $this->transpPesoBruto;
    }

    /**
     * @param float|null $transpPesoBruto
     * @return NotaFiscal
     */
    public function setTranspPesoBruto(?float $transpPesoBruto): NotaFiscal
    {
        $this->transpPesoBruto = $transpPesoBruto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTranspPesoLiquido(): ?float
    {
        return $this->transpPesoLiquido;
    }

    /**
     * @param float|null $transpPesoLiquido
     * @return NotaFiscal
     */
    public function setTranspPesoLiquido(?float $transpPesoLiquido): NotaFiscal
    {
        $this->transpPesoLiquido = $transpPesoLiquido;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTranspQtdeVolumes(): ?float
    {
        return $this->transpQtdeVolumes;
    }

    /**
     * @param float|null $transpQtdeVolumes
     * @return NotaFiscal
     */
    public function setTranspQtdeVolumes(?float $transpQtdeVolumes): NotaFiscal
    {
        $this->transpQtdeVolumes = $transpQtdeVolumes;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getIndicadorFormaPagto(): ?string
    {
        return $this->indicadorFormaPagto;
    }

    /**
     * @param null|string $indicadorFormaPagto
     * @return NotaFiscal
     */
    public function setIndicadorFormaPagto(?string $indicadorFormaPagto): NotaFiscal
    {
        $this->indicadorFormaPagto = $indicadorFormaPagto;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNaturezaOperacao(): ?string
    {
        return $this->naturezaOperacao;
    }

    /**
     * @param null|string $naturezaOperacao
     * @return NotaFiscal
     */
    public function setNaturezaOperacao(?string $naturezaOperacao): NotaFiscal
    {
        $this->naturezaOperacao = $naturezaOperacao;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getA03idNfReferenciada(): ?string
    {
        return $this->a03idNfReferenciada;
    }

    /**
     * @param null|string $a03idNfReferenciada
     * @return NotaFiscal
     */
    public function setA03idNfReferenciada(?string $a03idNfReferenciada): NotaFiscal
    {
        $this->a03idNfReferenciada = $a03idNfReferenciada;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFinalidadeNf(): ?string
    {
        return $this->finalidadeNf;
    }

    /**
     * @param null|string $finalidadeNf
     * @return NotaFiscal
     */
    public function setFinalidadeNf(?string $finalidadeNf): NotaFiscal
    {
        $this->finalidadeNf = $finalidadeNf;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTranspValorTotalFrete(): ?float
    {
        return $this->transpValorTotalFrete;
    }

    /**
     * @param float|null $transpValorTotalFrete
     * @return NotaFiscal
     */
    public function setTranspValorTotalFrete(?float $transpValorTotalFrete): NotaFiscal
    {
        $this->transpValorTotalFrete = $transpValorTotalFrete;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNRec(): ?string
    {
        return $this->nRec;
    }

    /**
     * @param string|null $nRec
     * @return NotaFiscal
     */
    public function setNRec(?string $nRec): NotaFiscal
    {
        $this->nRec = $nRec;
        return $this;
    }

    /**
     * @return NotaFiscalItem[]|ArrayCollection
     */
    public function getItens()
    {
        return $this->itens;
    }

    /**
     * @param NotaFiscalItem[]|ArrayCollection $itens
     * @return NotaFiscal
     */
    public function setItens($itens): NotaFiscal
    {
        $this->itens = $itens;
        return $this;
    }

    public function deleteAllItens(): NotaFiscal
    {
        if ($this->itens) {
            foreach ($this->itens as $item) {
                $item->setNotaFiscal(null);
            }
            $this->itens->clear();
        }
        return $this;
    }

    /**
     * @return NotaFiscalEvento[]|ArrayCollection
     */
    public function getEventos()
    {
        return $this->eventos;
    }

    /**
     * @param NotaFiscalEvento[]|ArrayCollection $eventos
     * @return NotaFiscal
     */
    public function setEventos($eventos)
    {
        $this->eventos = $eventos;
        return $this;
    }

    /**
     * @param NotaFiscalItem $item
     */
    public function addItem(NotaFiscalItem $item): void
    {
        if (!$this->itens->contains($item)) {
            $this->itens->add($item);
        }
    }

    /**
     * @return NotaFiscalCartaCorrecao[]|ArrayCollection
     */
    public function getCartasCorrecao()
    {
        return $this->cartasCorrecao;
    }

    /**
     * @param NotaFiscalCartaCorrecao[]|ArrayCollection $cartaCorrecaos
     * @return NotaFiscal
     */
    public function setCartasCorrecao($cartaCorrecaos): NotaFiscal
    {
        $this->cartasCorrecao = $cartaCorrecaos;
        return $this;
    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     */
    public function addCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): void
    {
        if (!$this->cartasCorrecao->contains($cartaCorrecao)) {
            $this->cartasCorrecao->add($cartaCorrecao);
        }
    }

    /**
     * @return NotaFiscalHistorico[]|ArrayCollection
     */
    public function getHistoricos()
    {
        return $this->historicos;
    }

    /**
     * @param NotaFiscalHistorico[]|ArrayCollection $historicos
     * @return NotaFiscal
     */
    public function setHistoricos($historicos): NotaFiscal
    {
        $this->historicos = $historicos;
        return $this;
    }

    /**
     * @param NotaFiscalHistorico $historico
     */
    public function addHistorico(NotaFiscalHistorico $historico): void
    {
        if (!$this->historicos->contains($historico)) {
            $this->historicos->add($historico);
        }
    }

    /**
     * @return string
     *
     * @Groups("entity")
     */
    public function getInfoStatus(): string
    {
        $infoStatus = '';
        if ($this->getCStat()) {
            $infoStatus .= $this->getCStat() . ' - ' . $this->getXMotivo();
        }
        if ($this->getCStatLote()) {
            $infoStatus .= ' (' . $this->getCStatLote() . ' - ' . $this->getXMotivoLote() . ')';
        }
        if ($this->getAmbiente() === 'HOM') {
            $infoStatus .= ' *** EMITIDA EM HOMOLOGAÇÃO';
        }
        return $infoStatus;
    }

    /**
     * @return int|null
     */
    public function getCStat(): ?int
    {
        return $this->cStat;
    }

    /**
     * @param int|null $cStat
     * @return NotaFiscal
     */
    public function setCStat(?int $cStat): NotaFiscal
    {
        $this->cStat = $cStat;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getXMotivo(): ?string
    {
        return $this->xMotivo;
    }

    /**
     * @param string|null $xMotivo
     * @return NotaFiscal
     */
    public function setXMotivo(?string $xMotivo): NotaFiscal
    {
        $this->xMotivo = $xMotivo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCStatLote(): ?string
    {
        return $this->cStatLote;
    }

    /**
     * @param string|null $cStatLote
     * @return NotaFiscal
     */
    public function setCStatLote(?string $cStatLote): NotaFiscal
    {
        $this->cStatLote = $cStatLote;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getXMotivoLote(): ?string
    {
        return $this->xMotivoLote;
    }

    /**
     * @param string|null $xMotivoLote
     * @return NotaFiscal
     */
    public function setXMotivoLote(?string $xMotivoLote): NotaFiscal
    {
        $this->xMotivoLote = $xMotivoLote;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getManifestDest(): ?string
    {
        return $this->manifestDest;
    }

    /**
     * @param string|null $manifestDest
     * @return NotaFiscal
     */
    public function setManifestDest(?string $manifestDest): NotaFiscal
    {
        $this->manifestDest = $manifestDest;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDtManifestDest(): ?\DateTime
    {
        return $this->dtManifestDest;
    }

    /**
     * @param \DateTime|null $dtManifestDest
     * @return NotaFiscal
     */
    public function setDtManifestDest(?\DateTime $dtManifestDest): NotaFiscal
    {
        $this->dtManifestDest = $dtManifestDest;
        return $this;
    }

    /**
     * @return \SimpleXMLElement|null
     */
    public function getXMLDecoded(): ?\SimpleXMLElement
    {
        if ($this->getXmlNota()) {
            try {
                $xmlUnzip = gzdecode(base64_decode($this->getXmlNota()));
                return simplexml_load_string($xmlUnzip);
            } catch (\Exception $e) {
                try {
                    return simplexml_load_string($this->xmlNota);
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getXmlNota(): ?string
    {
        return $this->xmlNota;
    }

    /**
     * @param null|string $xmlNota
     * @return NotaFiscal
     */
    public function setXmlNota(?string $xmlNota): NotaFiscal
    {
        $this->xmlNota = $xmlNota;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getResumo(): ?bool
    {
        return $this->resumo;
    }

    /**
     * @param bool|null $resumo
     * @return NotaFiscal
     */
    public function setResumo(?bool $resumo): NotaFiscal
    {
        $this->resumo = $resumo;
        return $this;
    }


}