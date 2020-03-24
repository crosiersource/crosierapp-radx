<?php

namespace App\Business\Fiscal;

use App\Entity\Fiscal\DistDFe;
use App\Entity\Fiscal\FinalidadeNF;
use App\Entity\Fiscal\ModalidadeFrete;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalEvento;
use App\Entity\Fiscal\NotaFiscalItem;
use App\EntityHandler\Fiscal\DistDFeEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalEventoEntityHandler;
use App\Repository\Fiscal\DistDFeRepository;
use App\Repository\Fiscal\NotaFiscalRepository;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class DistDFeBusiness
{

    private DistDFeEntityHandler $distDFeEntityHandler;

    private EntityManagerInterface $doctrine;

    private LoggerInterface $logger;

    private NFeUtils $nfeUtils;

    private NotaFiscalEntityHandler $notaFiscalEntityHandler;

    private NotaFiscalEventoEntityHandler $notaFiscalEventoEntityHandler;

    /**
     * @param EntityManagerInterface $doctrine
     * @param DistDFeEntityHandler $distDFeEntityHandler
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     * @param LoggerInterface $logger
     * @param NFeUtils $nfeUtils
     * @param NotaFiscalEventoEntityHandler $notaFiscalEventoEntityHandler
     */
    public function __construct(EntityManagerInterface $doctrine,
                                DistDFeEntityHandler $distDFeEntityHandler,
                                NotaFiscalEntityHandler $notaFiscalEntityHandler,
                                LoggerInterface $logger,
                                NFeUtils $nfeUtils,
                                NotaFiscalEventoEntityHandler $notaFiscalEventoEntityHandler)
    {
        $this->doctrine = $doctrine;
        $this->distDFeEntityHandler = $distDFeEntityHandler;
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
        $this->logger = $logger;
        $this->nfeUtils = $nfeUtils;
        $this->notaFiscalEventoEntityHandler = $notaFiscalEventoEntityHandler;
    }

    /**
     * @param string $cnpj
     * @return int
     * @throws ViewException
     */
    public function obterDistDFesAPartirDoUltimoNSU(string $cnpj): int
    {
        /** @var DistDFeRepository $repo */
        $repo = $this->doctrine->getRepository(DistDFe::class);
        $ultNSU = $repo->findUltimoNSU($cnpj);
        return $this->obterDistDFes($ultNSU, $cnpj);
    }

    /**
     * Obtém as DistDFes emitidas contra o CNPJ a partir do $nsu informado
     *
     * @param int $nsu
     * @param string $cnpj
     * @return int
     * @throws ViewException
     */
    public function obterDistDFes(int $nsu, string $cnpj): int
    {
        $qtdeObtida = 0;

        try {
            $tools = $this->nfeUtils->getToolsEmUso();
            $tools->model('55');
            $tools->setEnvironment(1);
            /** @var DistDFeRepository $repo */
            $repo = $this->doctrine->getRepository(DistDFe::class);
            $iCount = 0; //executa a busca de DFe em loop
            // $nsu--; // decrementa, pois o webservice retorna a partir do próximo
            do {
                $iCount++;
                if ($iCount === 50) { // máximo de 50 * 50
                    break;
                }
                $resp = $tools->sefazDistDFe($nsu);
                $xmlResp = simplexml_load_string($resp);
                $xmlResp->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
                $r = $xmlResp->xpath('//soap:Body');

                if (!($r[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip ?? false)) {
                    break;
                }

                $qtdeDocs = $r[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip->count();

                for ($i = 0; $i < $qtdeDocs; $i++) {
                    $doc = $r[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip[$i];
                    $nsu = (int)$doc->attributes()['NSU'];
                    $existe = $repo->findOneBy(['nsu' => $nsu]);
                    if (!$existe) {
                        $xml = $doc->__toString();
                        $dfe = new DistDFe();
                        $dfe->nsu = $nsu;
                        $dfe->xml = $xml;
                        $dfe->documento = $cnpj;
                        $this->distDFeEntityHandler->save($dfe);
                        $qtdeObtida++;
                    }
                }
                if ($qtdeDocs < 50) {
                    break;
                }
                sleep(3);
            } while (true);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter DFes (NSU: ' . $nsu . ')');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter DFes (NSU: ' . $nsu . ')');
        }

        return $qtdeObtida;
    }


    /**
     * @throws ViewException
     */
    public function obterDistDFesDeNSUsPulados(): int
    {
        $nsusPulados = $this->getNSUsPulados();
        $qtde = 0;
        foreach ($nsusPulados as $nsu) {
            $this->obterDistDFeByNSU($nsu);
            $qtde++;
            sleep(3);
        }
        return $qtde;
    }

    /**
     * @return array
     * @throws ViewException
     */
    public function getNSUsPulados(): array
    {
        /** @var DistDFeRepository $repo */
        $repo = $this->doctrine->getRepository(DistDFe::class);
        $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
        $nsus = $repo->findAllNSUs($cnpjEmUso);
        $pulados = [];
        $primeiro = $nsus[0];
        $ultimo = $nsus[count($nsus) - 1];
        for ($i = $primeiro; $i < $ultimo; $i++) {
            if (!in_array($i, $nsus)) {
                $pulados[] = $i;
            }
        }

        return $pulados;
    }

    /**
     *
     * @param int $nsu
     * @return bool
     * @throws ViewException
     */
    public function obterDistDFeByNSU(int $nsu): bool
    {
        try {
            $tools = $this->nfeUtils->getToolsEmUso();
            $tools->model('55');
            $tools->setEnvironment(1);

            $resp = $tools->sefazDistDFe(0, $nsu);
            $xmlResp = simplexml_load_string($resp);
            $xmlResp->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $r = $xmlResp->xpath('//soap:Body');

            $xml = 'Nenhum documento localizado';
            if ($r[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip ?: false) {
                $doc = $r[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip[0];
                $nsuRetornado = (int)$doc->attributes()['NSU'];
                if ($nsuRetornado === $nsu) {
                    $xml = gzdecode(base64_decode($doc->__toString()));
                }
            }

            $dfe = new DistDFe();
            $dfe->nsu = $nsu;
            $dfe->xml = $xml;
            $this->distDFeEntityHandler->save($dfe);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter DFe (NSU: ' . $nsu . ')');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter DFe (NSU: ' . $nsu . ')');
        }
    }

    /**
     * @param DistDFe $distDFe
     * @throws ViewException
     */
    public function reprocessarDistDFe(DistDFe $distDFe): void
    {
        switch ($distDFe->tipoDistDFe) {
            case 'NFEPROC':
                $nf = $this->nfeProc2NotaFiscal($distDFe->getXMLDecoded(), $distDFe->notaFiscal);
                $distDFe->notaFiscal = $nf;
                $distDFe->status = 'PROCESSADO';
                $this->distDFeEntityHandler->save($distDFe);
                break;
            case 'RESNFE':
                $this->resNfe2NotaFiscal($distDFe);
                break;
            case 'PROCEVENTONFE':
            case 'RESEVENTO':
                $this->reprocessarDistDFeDeEvento($distDFe);
                break;
        }

    }

    /**
     * XML de NF completa!
     * Processa um elemento do tipo nfeProc (que pode vir de um DistDFe ou de uma nota fiscal baixada).
     *
     * @param \SimpleXMLElement $xml
     * @param NotaFiscal|null $nf
     * @return NotaFiscal
     * @throws ViewException
     */
    public function nfeProc2NotaFiscal(\SimpleXMLElement $xml, NotaFiscal $nf = null): NotaFiscal
    {
        if (!$nf) {
            $nf = $this->doctrine->getRepository(NotaFiscal::class)->findOneBy(['chaveAcesso' => substr($xml->NFe->infNFe['Id'][0], 3)]);
            if (!$nf) {
                $nf = new NotaFiscal();
            }
        }

        $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();

        $nf->setResumo(false);
        $nf->setXmlNota($xml->asXML());

        $nf->setDocumentoDestinatario(preg_replace("/[^0-9]/", '', $nfeConfigs['cnpj']));
        $nf->setXNomeDestinatario($nfeConfigs['razaosocial']);
        $nf->setInscricaoEstadualDestinatario($nfeConfigs['ie']);

        $nf->setNumero((int)$xml->NFe->infNFe->ide->nNF->__toString());
        $nf->setCnf((int)$xml->NFe->infNFe->ide->cNF->__toString());
        $mod = (int)$xml->NFe->infNFe->ide->mod->__toString();
        $nf->setTipoNotaFiscal($mod === 55 ? 'NFE' : 'NFCE');

        $nf->setEntradaSaida($xml->NFe->infNFe->ide->tpNF->__toString() === 0 ? 'E' : 'S');
        $nf->setProtocoloAutorizacao($xml->NFe->infNFe->ide->nProt->__toString());

        $nf->setSerie((int)$xml->NFe->infNFe->ide->serie->__toString());
        $nf->setNaturezaOperacao($xml->NFe->infNFe->ide->natOp->__toString());
        $nf->setDtEmissao(DateTimeUtils::parseDateStr($xml->NFe->infNFe->ide->dhEmi->__toString()));
        if ($xml->NFe->infNFe->ide->dhSaiEnt->__toString() ?: null) {
            $nf->setDtSaiEnt(DateTimeUtils::parseDateStr($xml->NFe->infNFe->ide->dhSaiEnt->__toString()));
        }
        $nf->setFinalidadeNf(FinalidadeNF::getByCodigo($xml->NFe->infNFe->ide->finNFe->__toString())['key']);

        if ($xml->NFe->infNFe->ide->NFref->refNFe ?? null) {
            $nf->setA03idNfReferenciada($xml->NFe->infNFe->ide->NFref->refNFe->__toString());
        }

        $documentoEmitente = $xml->NFe->infNFe->emit->CNPJ->__toString() ?? $xml->NFe->infNFe->emit->CPF->__toString();

        $nf->setDocumentoEmitente($documentoEmitente);
        $nf->setXNomeEmitente($xml->NFe->infNFe->emit->xNome->__toString());
        $nf->setInscricaoEstadualDestinatario($xml->NFe->infNFe->emit->ie->__toString()); // ????


        if ($nf->getId()) {
            $nf->deleteAllItens();
        }
        /** @var NotaFiscal $nf */
        $nf = $this->notaFiscalEntityHandler->save($nf);

        foreach ($xml->NFe->infNFe->det as $iValue) {
            $item = $iValue;

            $nfItem = new NotaFiscalItem();
            $nfItem->setNotaFiscal($nf);

            $nfItem->setOrdem($item['nItem']->__toString());
            $nfItem->setCodigo($item->prod->cProd->__toString());
            $nfItem->setEan($item->prod->cEAN->__toString());
            $nfItem->setDescricao($item->prod->xProd->__toString());
            $nfItem->setNcm($item->prod->NCM->__toString());
            $nfItem->setCfop($item->prod->CFOP->__toString());
            $nfItem->setUnidade($item->prod->uCom->__toString());
            $nfItem->setQtde((float)$item->prod->qCom->__toString());
            $nfItem->setValorUnit((float)$item->prod->vUnCom->__toString());
            $nfItem->setValorTotal((float)$item->prod->vProd->__toString());
            $nfItem->setValorDesconto((float)$item->prod->vDesc->__toString());

            $this->notaFiscalEntityHandler->handleSavingEntityId($nfItem);

            $nf->addItem($nfItem);
        }

        // FRETE
        $nf->setTranspModalidadeFrete(ModalidadeFrete::getByCodigo($xml->NFe->infNFe->transp->modFrete->__toString())['key'] ?? null);

        if ($xml->NFe->infNFe->transp->vol->qVol ?? null) {
            $nf->setTranspQtdeVolumes((float)$xml->NFe->infNFe->transp->vol->qVol->__toString());
        }
        if ($xml->NFe->infNFe->transp->vol->esp ?? null) {
            $nf->setTranspEspecieVolumes($xml->NFe->infNFe->transp->vol->esp->__toString());
        }
        if ($xml->NFe->infNFe->transp->vol->marca) {
            $nf->setTranspMarcaVolumes($xml->NFe->infNFe->transp->vol->marca->__toString());
        }
        if ($xml->NFe->infNFe->transp->vol->nVol ?? null) {
            $nf->setTranspNumeracaoVolumes($xml->NFe->infNFe->transp->vol->nVol);
        }
        if ($xml->NFe->infNFe->transp->vol->pesoL ?? null) {
            $nf->setTranspPesoLiquido((float)$xml->NFe->infNFe->transp->vol->pesoL->__toString());
        }
        if ($xml->NFe->infNFe->transp->vol->pesoB ?? null) {
            $nf->setTranspPesoBruto((float)$xml->NFe->infNFe->transp->vol->pesoB->__toString());
        }

        $nf->setValorTotal((float)$xml->NFe->infNFe->pag->detPag->vPag->__toString());

        if ($xml->NFe->infNFe->infAdic->infCpl ?? null) {
            $nf->setInfoCompl($xml->NFe->infNFe->infAdic->infCpl->__toString());
        }

        /** @var NotaFiscal $nf */
        $nf = $this->notaFiscalEntityHandler->save($nf);

        return $nf;
    }


    /**
     * Se o XML for de resumo...
     *
     * @param DistDFe $distDFe
     * @return DistDFe
     * @throws ViewException
     */
    public function resNfe2NotaFiscal(DistDFe $distDFe): DistDFe
    {
        try {
            $xml = $distDFe->getXMLDecoded();
            if (!$xml) {
                throw new ViewException('Erro ao fazer o parse do xml para NF (chave: ' . $distDFe->chave . ')');
            }

            if ($distDFe->notaFiscal) {
                $nf = $distDFe->notaFiscal;
            } else {
                /** @var NotaFiscalRepository $repoNotaFiscal */
                $repoNotaFiscal = $this->doctrine->getRepository(NotaFiscal::class);
                /** @var NotaFiscal $nf */
                $nf = $repoNotaFiscal->findOneBy(['chaveAcesso' => $distDFe->chave]);
                if (!$nf) {
                    $nf = new NotaFiscal();
                }
            }
            $nf->setXmlNota($distDFe->xml);
            $nf->setChaveAcesso($distDFe->chave);
            $nf->nsu = $distDFe->nsu;
            $nf->setResumo(true);

            $nf->setDtEmissao(DateTimeUtils::parseDateStr($xml->dhEmi->__toString()));

            $nf->setEntradaSaida($xml->tpNF->__toString() === 0 ? 'E' : 'S');
            $nf->setProtocoloAutorizacao($xml->nProt->__toString());

            $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();
            $nf->setDocumentoDestinatario(preg_replace("/[^0-9]/", '', $nfeConfigs['cnpj']));
            $nf->setXNomeDestinatario($nfeConfigs['razaosocial']);
            $nf->setInscricaoEstadualDestinatario($nfeConfigs['ie']);

            if ($xml->CNPJ ?? null) {
                $nf->setDocumentoEmitente($xml->CNPJ->__toString());
            }
            if ($xml->CPF ?? null) {
                $nf->setDocumentoEmitente($xml->CPF->__toString());
            }
            $nf->setXNomeEmitente($xml->xNome->__toString());
            if ($xml->IE ?? null) {
                $nf->setInscricaoEstadualEmitente($xml->IE->__toString());
            }

            $nf->setValorTotal((float)$xml->vNF->__toString());

            /** @var NotaFiscal $nf */
            $nf = $this->notaFiscalEntityHandler->save($nf);
            $distDFe->status = 'PROCESSADO';
            $distDFe->notaFiscal = $nf;

        } catch (\Throwable $e) {
            $this->logger->error('Erro para a chave: ' . $nf->getChaveAcesso());
            $distDFe->status = 'ERRO AO PROCESSAR';
        }

        return $this->distDFeEntityHandler->save($distDFe);
    }

    /**
     * @param DistDFe $distDFe
     * @throws ViewException
     */
    public function reprocessarDistDFeDeEvento(DistDFe $distDFe): void
    {
        try {

            if (strpos($distDFe->tipoDistDFe, 'EVENTO') === FALSE) {
                throw new ViewException('DistDFe não é sobre evento');
            }

            /** @var NotaFiscalRepository $repoNotaFiscal */
            $repoNotaFiscal = $this->doctrine->getRepository(NotaFiscal::class);
            /** @var NotaFiscal $nf */
            $nf = $repoNotaFiscal->findOneBy(['chaveAcesso' => $distDFe->chave]);
            if (!$nf) {
                throw new ViewException('Evento para NF que não consta no BD (chave: ' . $distDFe->chave . ')');
            }

            $nfEvento = $distDFe->notaFiscalEvento ?? new NotaFiscalEvento();

            $xml = $distDFe->getXMLDecoded();
            if (!$xml) {
                throw new ViewException('XML inválido para reprocessarDistDFeDeEvento');
            }
            $xmlName = $xml->getName();
            $tpEvento = null;
            $nSeqEvento = null;
            $descEvento = null;
            if ($xmlName === 'resEvento') {
                $tpEvento = (int)$xml->tpEvento->__toString();
                $nSeqEvento = (int)$xml->nSeqEvento->__toString();
                $descEvento = $xml->xEvento->__toString();
            }
            if ($xmlName === 'procEventoNFe') {
                $tpEvento = (int)$xml->evento->infEvento->tpEvento->__toString();
                $nSeqEvento = (int)$xml->evento->infEvento->nSeqEvento->__toString();
                $descEvento = $xml->evento->infEvento->detEvento->descEvento->__toString();
            }
            if (!$tpEvento || !$nSeqEvento) {
                throw new ViewException('tpEvento, nSeqEvento ou descEvento não encontrados (tpEvento = ' . $tpEvento . ', nSeqEvento = ' . $nSeqEvento . ')' . ', descEvento = ' . $descEvento . ')');
            }

            try {
                $nfEvento->setXml($distDFe->xml);
                $nfEvento->setNotaFiscal($nf);
                $nfEvento->setTpEvento($tpEvento);
                $nfEvento->setNSeqEvento($nSeqEvento);
                $nfEvento->setDescEvento($descEvento);
                $this->notaFiscalEventoEntityHandler->save($nfEvento);
                $distDFe->nSeqEvento = $nSeqEvento;
                $distDFe->tpEvento = $tpEvento;
                $distDFe->notaFiscalEvento = $nfEvento;
                $distDFe->notaFiscal = $nfEvento->getNotaFiscal();
                $distDFe->status = 'PROCESSADO';
            } catch (\Exception $e) {
                throw new ViewException('Erro ao salvar fis_nf ou fis_distdfe (chave ' . $distDFe->chave . ')');
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao processar DistDFe: salvando evento para NFe (chave ' . $distDFe->chave . ')');
            $this->logger->error($e->getMessage());
            $distDFe->status = 'ERRO AO PROCESSAR';
        }

        $this->distDFeEntityHandler->save($distDFe);


    }

    /**
     * Processo que extrai a DFe e salva como uma entidade NotaFiscal ou como um NotaFiscalEvento.
     *
     * @throws ViewException
     */
    public function processarDistDFesObtidos(): void
    {
        // Primeiro processa os DistDFes dos tipos NFEPROC e RESNFE
        $this->processarDistDFesObtidosDeNFes();
        // Depois processa os DistDFes dos tipos PROCEVENTONFE e RESEVENTO
        $this->processarDistDFesObtidosDeEventos();
    }

    /**
     * Processo que extrai a DFe e salva como uma entidade NotaFiscal ou como um NotaFiscalEvento.
     *
     * @throws ViewException
     */
    public function processarDistDFesObtidosDeNFes(): void
    {
        try {
            /** @var DistDFeRepository $repoDistDFe */
            $repoDistDFe = $this->doctrine->getRepository(DistDFe::class);
            $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            $distDFesAProcessar =  $repoDistDFe->findDistDFeNotInNotaFiscal($cnpjEmUso);


            foreach ($distDFesAProcessar as $distDFeId) {
                /** @var DistDFe $distDFe */
                $distDFe = $repoDistDFe->find($distDFeId);
                // gzdecode(base64_decode($distDFe->getXml()))
                $xml = $distDFe->getXMLDecoded();
                if (!$xml) {
                    continue;
                }
                $xmlName = $xml->getName();

                if ($xmlName === 'nfeProc') {
                    $nf = $this->nfeProc2NotaFiscal($distDFe->getXMLDecoded());
                    $distDFe->notaFiscal = $nf;
                    $this->distDFeEntityHandler->save($distDFe);
                } elseif ($xmlName === 'resNFe') {
                    $this->resNfe2NotaFiscal($distDFe);
                } else {
                    $this->logger->error('Erro ao processar DistDFe: não reconhecido (chave ' . $distDFe->chave . ')');
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao processarDistDFesObtidos()');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao processarDistDFesObtidos()');
        }
    }

    /**
     *
     */
    public function processarDistDFesObtidosDeEventos(): void
    {
        /** @var DistDFeRepository $repoDistDFe */
        $repoDistDFe = $this->doctrine->getRepository(DistDFe::class);

        $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
        $distDFesAProcessar = $repoDistDFe->findDistDFeNotInNotaFiscalEvento($cnpjEmUso);

        /** @var NotaFiscalRepository $repoNotaFiscal */
        $repoNotaFiscal = $this->doctrine->getRepository(NotaFiscal::class);

        /** @var DistDFe $distDFe */
        foreach ($distDFesAProcessar as $distDFeId) {
            try {
                /** @var DistDFe $distDFe */
                $distDFe = $repoDistDFe->find($distDFeId);
                /** @var NotaFiscal $nf */
                $nf = $repoNotaFiscal->findOneBy(['chaveAcesso' => $distDFe->chave]);
                if (!$nf) {
                    throw new ViewException('Evento para NF que não consta no BD (chave: ' . $distDFe->chave . ')');
                }
                $xml = $distDFe->getXMLDecoded();
                if (!$xml) {
                    continue;
                }
                $xmlName = $xml->getName();
                $tpEvento = null;
                $nSeqEvento = null;
                $descEvento = null;
                if ($xmlName === 'resEvento') {
                    $tpEvento = (int)$xml->tpEvento->__toString();
                    $nSeqEvento = (int)$xml->nSeqEvento->__toString();
                    $descEvento = $xml->xEvento->__toString();
                }
                if ($xmlName === 'procEventoNFe') {
                    $tpEvento = (int)$xml->evento->infEvento->tpEvento->__toString();
                    $nSeqEvento = (int)$xml->evento->infEvento->nSeqEvento->__toString();
                    $descEvento = $xml->evento->infEvento->detEvento->descEvento->__toString();
                }
                if (!$tpEvento || !$nSeqEvento) {
                    throw new ViewException('tpEvento, nSeqEvento ou descEvento não encontrados (tpEvento = ' . $tpEvento . ', nSeqEvento = ' . $nSeqEvento . ')' . ', descEvento = ' . $descEvento . ')');
                }

                try {
                    $nfEvento = new NotaFiscalEvento();
                    $nfEvento->setXml($distDFe->xml);
                    $nfEvento->setNotaFiscal($nf);
                    $nfEvento->setTpEvento($tpEvento);
                    $nfEvento->setNSeqEvento($nSeqEvento);
                    $nfEvento->setDescEvento($descEvento);
                    $this->notaFiscalEventoEntityHandler->save($nfEvento);

                    $distDFe->nSeqEvento = $nSeqEvento;
                    $distDFe->tpEvento = $tpEvento;
                    $distDFe->notaFiscalEvento = $nfEvento;
                    $distDFe->status = 'PROCESSADO';
                } catch (\Exception $e) {
                    throw new ViewException('Erro ao salvar fis_nf ou fis_distdfe (chave ' . $distDFe->chave . ')');
                }
            } catch (\Exception $e) {
                $this->logger->error('Erro ao processar DistDFe: salvando evento para NFe (chave ' . $distDFe->chave . ')');
                $this->logger->error($e->getMessage());
                $distDFe->status = 'ERRO AO PROCESSAR';
            }

            $this->distDFeEntityHandler->save($distDFe);
        }
    }

    /**
     * Download da DFe pela chave (utilizado após a manifestação da nota e sua subsequente autorização de download).
     *
     * @param NotaFiscal $notaFiscal
     * @throws ViewException
     */
    public function downloadNFe(NotaFiscal $notaFiscal): void
    {
        try {
            $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoDestinatario());
            $tools->model('55');
            $tools->setEnvironment(1);
            $response = $tools->sefazDownload($notaFiscal->getChaveAcesso());
            $xmlDownload = simplexml_load_string($response);
            $xmlDownload->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $xml = $xmlDownload->xpath('//soap:Body');

            $cStat = $xml[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->cStat ?? null;
            if (!$cStat || !$cStat->__toString()) {
                $this->logger->info('Erro ao obter cStat para chave: ' . $notaFiscal->getChaveAcesso() . ')');
            }
            $cStat = $cStat->__toString();

            if ($cStat !== '138') {
                $this->logger->info('cStat diferente de 138 para chave ' . $notaFiscal->getChaveAcesso() . ' (cStat = ' . $cStat . ')');
                $xMotivo = $xml[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->xMotivo ?? null;
                if ($xMotivo instanceof \SimpleXMLElement) {
                    $this->logger->info('xMotivo: ' . $xMotivo->__toString());
                }
            }

            $zip = $xml[0]->nfeDistDFeInteresseResponse->nfeDistDFeInteresseResult->retDistDFeInt->loteDistDFeInt->docZip->__toString() ?? null;
            if ($zip) {
                $notaFiscal->setXmlNota($zip);
                $this->nfeProc2NotaFiscal($notaFiscal->getXMLDecoded(), $notaFiscal);
            } else {
                $this->logger->error('Erro ao obter XML (download zip) para a chave: ' . $notaFiscal->getChaveAcesso());
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao fazer o download do XML (chave: ' . $notaFiscal->getChaveAcesso() . ')');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao fazer o download do XML (chave: ' . $notaFiscal->getChaveAcesso() . ')');
        }
    }

    /**
     * Descompacta o xml e procura o tipo, chave e dados de evento.
     *
     * @throws ViewException
     */
    public function extrairChaveETipoDosDistDFes(): void
    {
        /** @var DistDFeRepository $repo */
        $repo = $this->doctrine->getRepository(DistDFe::class);
        $distDFesSemChave = $repo->findByFiltersSimpl([['chave', 'IS_EMPTY'], ['xml', 'NOT_LIKE', 'Nenhum documento localizado']], null, 0, -1);
        $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();
        /** @var DistDFe $distDFe */
        foreach ($distDFesSemChave as $distDFe) {
            try {
                $xml = $distDFe->getXMLDecoded();
                if (!$xml) continue;
                $chave = null;
                $cnpj = null;
                // Para XML de <resEvento>
                $xmlName = $xml->getName();
                if ($xmlName === 'nfeProc') {
                    $chave = $xml->protNFe->infProt->chNFe->__toString();
                    $cnpj = $xml->NFe->infNFe->emit->CNPJ->__toString();;
                } elseif ($xmlName === 'resNFe') {
                    $chave = $xml->chNFe->__toString();
                    $cnpj = $xml->CNPJ->__toString();
                } elseif ($xmlName === 'resEvento') {
                    $chave = $xml->chNFe->__toString();
                    $cnpj = $xml->CNPJ->__toString();
                    $distDFe->tpEvento = (int)$xml->tpEvento->__toString();
                    $distDFe->nSeqEvento = (int)$xml->nSeqEvento->__toString();
                } elseif ($xmlName === 'procEventoNFe') {
                    $chave = $xml->evento->infEvento->chNFe->__toString();
                    $cnpj = $xml->evento->infEvento->CNPJ->__toString();
                    $distDFe->tpEvento = (int)$xml->evento->infEvento->tpEvento->__toString();
                    $distDFe->nSeqEvento = (int)$xml->evento->infEvento->nSeqEvento->__toString();
                }
                if (!$chave) {
                    throw new \RuntimeException('Não consegui encontrar a chave');
                }
                $distDFe->proprio = $nfeConfigs['cnpj'] === $cnpj;
                $distDFe->tipoDistDFe = $xml->getName();
                $distDFe->chave = $chave;
                $this->distDFeEntityHandler->save($distDFe);
            } catch (\Exception $e) {
                $this->logger->error('Erro ao extrair chave do DistDFe id=' . $distDFe->getId());
            }
        }
    }


}
    