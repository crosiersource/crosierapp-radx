<?php

namespace App\Business\Fiscal;

use CrosierSource\CrosierLibBaseBundle\APIClient\CrosierEntityIdAPIClient;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Municipio;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\MunicipioRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\FileUtils\FileUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\FinalidadeNF;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\ModalidadeFrete;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalCartaCorrecao;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\TipoNotaFiscal;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalCartaCorrecaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Classe que trata da integração com o Unimake (UniNfe).
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class UnimakeBusiness
{

    /** @var NotaFiscalEntityHandler */
    private $notaFiscalEntityHandler;

    /** @var NotaFiscalCartaCorrecaoEntityHandler */
    private $notaFiscalCartaCorrecaoEntityHandler;

    private $doctrine;

    /** @var CrosierEntityIdAPIClient */
    private $crosierEntityIdAPIClient;

    /**
     * UnimakeBusiness constructor.
     * @param EntityManagerInterface $doctrine
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     * @param CrosierEntityIdAPIClient $crosierEntityIdAPIClient
     */
    public function __construct(EntityManagerInterface $doctrine,
                                NotaFiscalEntityHandler $notaFiscalEntityHandler,
                                NotaFiscalCartaCorrecaoEntityHandler $notaFiscalCartaCorrecaoEntityHandler,
                                CrosierEntityIdAPIClient $crosierEntityIdAPIClient)
    {
        $this->doctrine = $doctrine;
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
        $this->notaFiscalCartaCorrecaoEntityHandler = $notaFiscalCartaCorrecaoEntityHandler;
        $this->crosierEntityIdAPIClient = $crosierEntityIdAPIClient;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function faturar(NotaFiscal $notaFiscal): NotaFiscal
    {
        $pastaXMLExemplos = $_SERVER['PASTAARQUIVOSXMLEXEMPLO'];

        $exemploNFe = file_get_contents($pastaXMLExemplos . '/exemplo-nfe.xml');
        $nfe = simplexml_load_string($exemploNFe);
        if (!$nfe) {
            throw new \RuntimeException('Não foi possível obter o template XML da NFe');
        }

        $nfe->infNFe->ide->nNF = $notaFiscal->getNumero();

        $nfe->infNFe->ide->cNF = $notaFiscal->getCnf();

        $nfe->infNFe->ide->mod = TipoNotaFiscal::get($notaFiscal->getTipoNotaFiscal())['codigo'];
        $nfe->infNFe->ide->serie = $notaFiscal->getSerie();

        // Campo tpEmis
        // 1-Emissão Normal
        // 2-Contingência em Formulário de Segurança
        // 3-Contingência SCAN (desativado)
        // 4-Contingência EPEC
        // 5-Contingência em Formulário de Segurança FS-DA
        // 6-Contingência SVC-AN
        // 7-Contingência SVC-RS

        $tpEmis = 1;
        $nfe->infNFe->ide->tpEmis = $tpEmis;

        $nfe->infNFe['Id'] = 'NFe' . $notaFiscal->getChaveAcesso();
        $nfe->infNFe->ide->cDV = NFeKeys::verifyingDigit(substr($notaFiscal->getChaveAcesso(), 0, -1));

        $nfe->infNFe->ide->natOp = $notaFiscal->getNaturezaOperacao();

        $nfe->infNFe->ide->dhEmi = $notaFiscal->getDtEmissao()->format('Y-m-d\TH:i:sP');

        $nfe->infNFe->ide->tpNF = $notaFiscal->getEntradaSaida() === 'E' ? '0' : '1';

        $finNFe = FinalidadeNF::get($notaFiscal->getFinalidadeNf())['codigo'];
        $nfe->infNFe->ide->finNFe = $finNFe;

        // Devolução
        if ($finNFe === 4) {
            if (!$notaFiscal->getA03idNfReferenciada()) {
                throw new \RuntimeException('Nota fiscal de devolução sem Id NF Referenciada.');
            }
            // else
            $nfe->infNFe->ide->NFref->refNFe = $notaFiscal->getA03idNfReferenciada();
        }

        if ($notaFiscal->getTipoNotaFiscal() === 'NFE') {
            $nfe->infNFe->ide->dhSaiEnt = $notaFiscal->getDtSaiEnt()->format('Y-m-d\TH:i:sP');
        } else {
            unset($nfe->infNFe->ide->dhSaiEnt); // NFCE não possui
            $nfe->infNFe->ide->idDest = 1;
        }

        // 1=Operação interna;
        // 2=Operação interestadual;
        // 3=Operação com exterior.
        if ($notaFiscal->getDocumentoDestinatario()) {

            if (strlen($notaFiscal->getDocumentoDestinatario()) === 14) {
                $nfe->infNFe->dest->CNPJ = preg_replace("/[^0-9]/", '', $notaFiscal->getDocumentoDestinatario());
            } else {
                $nfe->infNFe->dest->CPF = preg_replace("/[^0-9]/", '', $notaFiscal->getDocumentoDestinatario());
            }

            if ($notaFiscal->getAmbiente() === 'HOM') {
                $nfe->infNFe->dest->xNome = 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';
            } else {
                $nfe->infNFe->dest->xNome = trim($notaFiscal->getXNomeDestinatario());
            }

            if ($notaFiscal->getTipoNotaFiscal() === 'NFE') {


                $ufDestinatario = $notaFiscal->getEstadoDestinatario();
                if ($ufDestinatario && $ufDestinatario === 'PR') {
                    $idDest = 1;
                } else {
                    $idDest = 2;
                }
                $nfe->infNFe->ide->idDest = $idDest;

                $nfe->infNFe->dest->enderDest->xLgr = trim($notaFiscal->getLogradouroDestinatario());
                $nfe->infNFe->dest->enderDest->nro = trim($notaFiscal->getNumeroDestinatario());
                $nfe->infNFe->dest->enderDest->xBairro = trim($notaFiscal->getBairroDestinatario());

                /** @var MunicipioRepository $repoMunicipio */
                $repoMunicipio = $this->doctrine->getRepository(Municipio::class);

                /** @var Municipio $r */
                $r = $repoMunicipio->findOneByFiltersSimpl([
                    ['municipioNome', 'EQ', $notaFiscal->getCidadeDestinatario()],
                    ['ufSigla', 'EQ', $notaFiscal->getEstadoDestinatario()]
                ]);

                if (!$r ||
                    strtoupper(StringUtils::removerAcentos($r->getMunicipioNome())) !== strtoupper(StringUtils::removerAcentos($notaFiscal->getCidadeDestinatario()))) {
                    throw new ViewException('Município inválido: [' . $notaFiscal->getCidadeDestinatario() . '-' . $notaFiscal->getEstadoDestinatario() . ']');
                }

                $nfe->infNFe->dest->enderDest->cMun = $r->getMunicipioCodigo();
                $nfe->infNFe->dest->enderDest->xMun = $r->getMunicipioNome();
                $nfe->infNFe->dest->enderDest->UF = $r->getUfSigla();


                $nfe->infNFe->dest->enderDest->CEP = preg_replace('/[^0-9]/', '', $notaFiscal->getCepDestinatario());
                $nfe->infNFe->dest->enderDest->cPais = 1058;
                $nfe->infNFe->dest->enderDest->xPais = 'BRASIL';
                $nfe->infNFe->dest->enderDest->fone = preg_replace("/[^0-9]/", '', $notaFiscal->getFoneDestinatario());
            }


            // 1=Contribuinte ICMS (informar a IE do destinatário);
            // 2=Contribuinte isento de Inscrição no cadastro de Contribuintes do ICMS;
            // 9=Não Contribuinte, que pode ou não possuir Inscrição Estadual no Cadastro de Contribuintes do ICMS.
            // Nota 1: No caso de NFC-e informar indIEDest=9 e não informar a tag IE do destinatário;
            // Nota 2: No caso de operação com o Exterior informar indIEDest=9 e não informar a tag IE do destinatário;
            // Nota 3: No caso de Contribuinte Isento de Inscrição (indIEDest=2), não informar a tag IE do destinatário.

            if ($notaFiscal->getTipoNotaFiscal() === 'NFCE') {
                $nfe->infNFe->dest->indIEDest = 9;
                unset($nfe->infNFe->transp);
                unset($nfe->infNFe->dest->IE);
            } else {
                if (($notaFiscal->getInscricaoEstadualDestinatario() === 'ISENTO') || !$notaFiscal->getInscricaoEstadualDestinatario()) {
                    unset($nfe->infNFe->dest->IE);
                    $nfe->infNFe->dest->indIEDest = 2;
                } else {
                    $nfe->infNFe->dest->indIEDest = 1;
                    if ($notaFiscal->getInscricaoEstadualDestinatario()) {
                        $nfe->infNFe->dest->IE = trim($notaFiscal->getInscricaoEstadualDestinatario());
                    } else {
                        unset($nfe->infNFe->dest->IE);
                    }
                }
            }
        } else {
            unset($nfe->infNFe->dest);
        }

        // 0=Sem geração de DANFE;
        // 1=DANFE normal, Retrato;
        // 2=DANFE normal, Paisagem;
        // 3=DANFE Simplificado;
        // 4=DANFE NFC-e;
        // 5=DANFE NFC-e em mensagem eletrônica (o envio de mensagem eletrônica pode ser feita de forma simultânea com a impressão do DANFE; usar o tpImp=5 quando esta for a única forma de disponibilização do DANFE).

        if ($notaFiscal->getTipoNotaFiscal() === 'NFCE') {
            $nfe->infNFe->ide->tpImp = 4;
        } else {
            $nfe->infNFe->ide->tpImp = 1;
        }

        // 1=Produção
        // 2=Homologação
        if ($notaFiscal->getAmbiente() === 'PROD') {
            $nfe->infNFe->ide->tpAmb = 1;
        } else {
            $nfe->infNFe->ide->tpAmb = 2;
        }


        unset($nfe->infNFe->det);
        $i = 1;

        $total_bcICMS = 0;
        $total_vICMS = 0;
        foreach ($notaFiscal->getItens() as $nfItem) {
            $itemXML = $nfe->infNFe->addChild('det');
            $itemXML['nItem'] = $nfItem->getOrdem();
            $itemXML->prod->cProd = $nfItem->getCodigo();
            $itemXML->prod->cEAN = 'SEM GTIN';

            if ($notaFiscal->getAmbiente() === 'HOM' && $i === 1) {
                $xProd = 'NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';
            } else {
                $xProd = $nfItem->getDescricao();
            }

            $itemXML->prod->xProd = $xProd;
            $itemXML->prod->NCM = $nfItem->getNcm();
            $itemXML->prod->CFOP = $nfItem->getCfop();
            $itemXML->prod->uCom = $nfItem->getUnidade();
            $itemXML->prod->qCom = $nfItem->getQtde();
            $itemXML->prod->vUnCom = $nfItem->getValorUnit();
            $itemXML->prod->vProd = number_format($nfItem->getValorTotal(), 2, '.', '');
            $itemXML->prod->cEANTrib = 'SEM GTIN';
            $itemXML->prod->uTrib = $nfItem->getUnidade();
            $itemXML->prod->qTrib = $nfItem->getQtde();
            $itemXML->prod->vUnTrib = number_format($nfItem->getValorUnit(), 2, '.', '');
            if (bccomp($nfItem->getValorDesconto(), 0.00, 2)) {
                $itemXML->prod->vDesc = number_format(abs($nfItem->getValorDesconto()), 2, '.', '');
            }
            $itemXML->prod->indTot = '1';


            if (!$nfItem->getCsosn()) {
                $nfItem->setCsosn(103);
            }

            if ($nfItem->getCsosn() === 900) {
                $itemXML->imposto->ICMS->ICMSSN900->orig = '0';
                $itemXML->imposto->ICMS->ICMSSN900->CSOSN = $nfItem->getCsosn();
                $itemXML->imposto->ICMS->ICMSSN900->modBC = '0';
                $itemXML->imposto->ICMS->ICMSSN900->vBC = $nfItem->getIcmsValorBc();
                $itemXML->imposto->ICMS->ICMSSN900->pICMS = $nfItem->getIcmsAliquota();
                $itemXML->imposto->ICMS->ICMSSN900->vICMS = $nfItem->getIcmsValor();
                // Soma para o total
                $total_bcICMS += $nfItem->getIcmsValorBc();
                $total_vICMS += $nfItem->getIcmsValor();
            } else { // o nosso por padrão é sempre 103
                $itemXML->imposto->ICMS->ICMSSN102->orig = '0';
                $itemXML->imposto->ICMS->ICMSSN102->CSOSN = $nfItem->getCsosn();
            }

            $itemXML->imposto->PIS->PISNT->CST = '07';
            $itemXML->imposto->COFINS->COFINSNT->CST = '07';

            $i++;
        }
        $nfe->infNFe->addChild('total');
        $nfe->infNFe->total->ICMSTot->vBC = number_format($total_bcICMS, 2, '.', '');
        $nfe->infNFe->total->ICMSTot->vICMS = number_format($total_vICMS, 2, '.', '');
        $nfe->infNFe->total->ICMSTot->vICMSDeson = '0.00';
        $nfe->infNFe->total->ICMSTot->vFCP = '0.00';
        $nfe->infNFe->total->ICMSTot->vBCST = '0.00';
        $nfe->infNFe->total->ICMSTot->vST = '0.00';
        $nfe->infNFe->total->ICMSTot->vFCPST = '0.00';
        $nfe->infNFe->total->ICMSTot->vFCPSTRet = '0.00';
        $nfe->infNFe->total->ICMSTot->vProd = number_format($notaFiscal->getSubtotal(), 2, '.', '');
        $nfe->infNFe->total->ICMSTot->vFrete = '0.00';
        $nfe->infNFe->total->ICMSTot->vSeg = '0.00';
        // if (bccomp($notaFiscal->getTotalDescontos(), 0.00, 2)) {
        $nfe->infNFe->total->ICMSTot->vDesc = number_format(abs($notaFiscal->getTotalDescontos()), 2, '.', '');
        // }
        $nfe->infNFe->total->ICMSTot->vII = '0.00';
        $nfe->infNFe->total->ICMSTot->vIPI = '0.00';
        $nfe->infNFe->total->ICMSTot->vIPIDevol = '0.00';
        $nfe->infNFe->total->ICMSTot->vPIS = '0.00';
        $nfe->infNFe->total->ICMSTot->vCOFINS = '0.00';
        $nfe->infNFe->total->ICMSTot->vOutro = '0.00';
        $nfe->infNFe->total->ICMSTot->vNF = number_format($notaFiscal->getValorTotal(), 2, '.', '');
        $nfe->infNFe->total->ICMSTot->vTotTrib = '0.00';

        if ($notaFiscal->getTipoNotaFiscal() === 'NFCE') {
            $nfe->infNFe->transp->modFrete = 9;

        } else {
            $nfe->infNFe->transp->modFrete = ModalidadeFrete::get($notaFiscal->getTranspModalidadeFrete())['codigo'];

            if ($notaFiscal->getTranspDocumento()) {

                $nfe->infNFe->transp->transporta->CNPJ = $notaFiscal->getTranspDocumento();
                $nfe->infNFe->transp->transporta->xNome = trim($notaFiscal->getTranspNome());
                if ($notaFiscal->getTranspInscricaoEstadual()) {
                    $nfe->infNFe->transp->transporta->IE = trim($notaFiscal->getTranspInscricaoEstadual());
                }

                $nfe->infNFe->transp->transporta->xEnder = substr($notaFiscal->getTranspEndereco(), 0, 60);

                /** @var MunicipioRepository $repoMunicipio */
                $repoMunicipio = $this->doctrine->getRepository(Municipio::class);

                /** @var Municipio $r */
                $r = $repoMunicipio->findOneByFiltersSimpl([
                    ['municipioNome', 'EQ', $notaFiscal->getTranspCidade()],
                    ['ufSigla', 'EQ', $notaFiscal->getTranspEstado()]
                ]);

                if (!$r || strtoupper(StringUtils::removerAcentos($r->getMunicipioNome())) !== strtoupper(StringUtils::removerAcentos($notaFiscal->getTranspCidade()))) {
                    throw new ViewException('Município inválido: [' . $notaFiscal->getTranspCidade() . '-' . $notaFiscal->getTranspEstado() . ']');
                }


                $nfe->infNFe->transp->transporta->xMun = $r->getMunicipioNome();
                $nfe->infNFe->transp->transporta->UF = $r->getUfSigla();

                $nfe->infNFe->transp->vol->qVol = number_format($notaFiscal->getTranspQtdeVolumes(), 0);
                $nfe->infNFe->transp->vol->esp = $notaFiscal->getTranspEspecieVolumes();
                if ($notaFiscal->getTranspMarcaVolumes()) {
                    $nfe->infNFe->transp->vol->marca = $notaFiscal->getTranspMarcaVolumes();
                }
                if ($notaFiscal->getTranspNumeracaoVolumes()) {
                    $nfe->infNFe->transp->vol->nVol = $notaFiscal->getTranspNumeracaoVolumes();
                }

                $nfe->infNFe->transp->vol->pesoL = number_format($notaFiscal->getTranspPesoLiquido(), 3, '.', '');
                $nfe->infNFe->transp->vol->pesoB = number_format($notaFiscal->getTranspPesoBruto(), 3, '.', '');

            }
        }

        if ($finNFe === 3 or $finNFe === 4) {
            $nfe->infNFe->pag->detPag->tPag = '90';
            $nfe->infNFe->pag->detPag->vPag = '0.00';
        } else {
            $nfe->infNFe->pag->detPag->tPag = '01';
            $nfe->infNFe->pag->detPag->vPag = number_format($notaFiscal->getValorTotal(), 2, '.', '');
        }


        if ($notaFiscal->getInfoCompl()) {
            $infoCompl = preg_replace("/\r/", '', $notaFiscal->getInfoCompl());
            $infoCompl = preg_replace("/\n/", ';', $infoCompl);
            $nfe->infNFe->infAdic->infCpl = trim($infoCompl);
        }

        $nfe->infNFe->infRespTec->CNPJ = '77498442000134';
        $nfe->infNFe->infRespTec->xContato = 'Carlos Eduardo Pauluk';
        $nfe->infNFe->infRespTec->email = 'carlospauluk@gmail.com';
        $nfe->infNFe->infRespTec->fone = '4232276657';


        // Número randômico para que não aconteça de pegar XML de retorno de tentativas de faturamento anteriores
        $rand = random_int(10000000, 99999999);
        $notaFiscal->setRandFaturam($rand);

        $notaFiscal->setCStatLote(-100);
        $notaFiscal->setXMotivoLote('AGUARDANDO FATURAMENTO');

        $this->notaFiscalEntityHandler->save($notaFiscal);

        $notaFiscal->setXmlNota($nfe->asXML());

        /** @var NotaFiscal $notaFiscal */
        $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);

        $notaFiscal = $this->consultarRetorno($notaFiscal, $rand);
        if ($notaFiscal->getCStat() === 100) {
            $this->imprimir($notaFiscal);
        }
        return $notaFiscal;
    }

    /**
     * Verifica nos arquivos de retorno quais os status.
     *
     * @param NotaFiscal $notaFiscal
     * @param $rand
     * @return NotaFiscal|null
     * @throws ViewException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function consultarRetorno(NotaFiscal $notaFiscal, $rand): ?NotaFiscal
    {
        $id = $notaFiscal->getId();
        if (!$id) {
            return null;
        }

        $uuid = $notaFiscal->getUuid();

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];

        $pastaRetorno = $pastaUnimake . '/retorno/';

        // pega o número do lote do arquivo $notaFiscal->getUuid() . "-num-lot.xml"
        $arquivoNumLot = $pastaRetorno . $uuid . '-' . $rand . '-num-lot.xml';

        $arquivoErr = $pastaRetorno . $uuid . '-' . $rand . '-nfe.err';

        $count = 20;


        while (true) {
            if (!file_exists($arquivoNumLot) && !file_exists($arquivoErr)) {
                sleep(1);
                $count--;
                if ($count <= 0) {
                    break;
                }
            } else {

                if (file_exists($arquivoNumLot)) {
                    // pega o arquivo com lote: 000000000[lote]-rec.xml
                    $xmlNumLot = simplexml_load_string(file_get_contents($arquivoNumLot));
                    $numLot = str_pad($xmlNumLot->NumeroLoteGerado, 15, '0', STR_PAD_LEFT);

                    // retEnviNFe->infRec->nRec
                    $arquivoRec = $pastaRetorno . $numLot . '-rec.xml';
                    if (!file_exists($arquivoRec)) {
                        sleep(1);
                        $count--;
                        if ($count <= 0) {
                            break;
                        }
                    } else {
                        $xmlRec = simplexml_load_string(file_get_contents($arquivoRec));

                        if ((int)$xmlRec->cStat === 103) {
                            $nRec = $xmlRec->infRec->nRec;
                            // pega o arquivo com [nRec]-pro-rec.xml
                            $arquivoProRec = $pastaRetorno . $nRec . '-pro-rec.xml';

                            if (!file_exists($arquivoProRec)) {
                                sleep(1);
                                $count--;
                                if ($count <= 0) {
                                    break;
                                }
                            } else {
                                $xmlProRec = simplexml_load_string(file_get_contents($arquivoProRec));

                                $cStat = $xmlProRec->protNFe->infProt->cStat->__toString();
                                $xMotivo = $xmlProRec->protNFe->infProt->xMotivo->__toString();
                                $nProt = $xmlProRec->protNFe->infProt->nProt->__toString();

                                $notaFiscal->setCStat($cStat);
                                $notaFiscal->setXMotivo($xMotivo);
                                $notaFiscal->setProtocoloAutorizacao($nProt);
                                // $notaFiscal->setDtSpartacusStatus(new \DateTime());
                                $this->notaFiscalEntityHandler->save($notaFiscal);
                                $this->doctrine->flush();
                                break;
                            }
                        }
                        sleep(1);
                        $count--;
                        if ($count <= 0) {
                            break;
                        }
                    }
                } else if (file_exists($arquivoErr)) {
                    $err = file($arquivoErr);
                    $message = explode('|', $err[2])[1];

                    $notaFiscal->setCStat(0);
                    $notaFiscal->setXMotivo($message);
                    $this->notaFiscalEntityHandler->save($notaFiscal);
                    $this->doctrine->flush();
                    return $notaFiscal;
                } else {
                    throw new \RuntimeException('Nem o arquivo de sucesso, nem o de erro, foram encontrados.');
                }

            }
        }
        return $notaFiscal;
    }

    /**
     * Faz uma consulta ao status da NotaFiscal na receita.
     *
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws \Exception
     */
    public function consultarStatus(NotaFiscal $notaFiscal): NotaFiscal
    {
        $pastaXMLExemplos = $_SERVER['PASTAARQUIVOSXMLEXEMPLO'];

        $exemplo = file_get_contents($pastaXMLExemplos . '/-ped-sit4.xml');
        $pedSit = simplexml_load_string($exemplo);

        $pedSit->tpAmb = $notaFiscal->getAmbiente() === 'PROD' ? '1' : '2';
        $pedSit->chNFe = $notaFiscal->getChaveAcesso();

        // número randômico para casos onde várias consultas possam ser feitas
        $rand = random_int(10000000, 99999999);

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        $fileName = $pastaUnimake . '/envio/' . $notaFiscal->getUuid() . '-CONS-SIT-' . $rand . '-nfe.xml';
        $xml = $pedSit->asXML();
        $bytes = file_put_contents($fileName, $xml);
        if ($bytes < 1) {
            throw new \RuntimeException('Não foi possível escrever no arquivo: [' . $fileName . ']');
        }

        $count = 20;
        $arqRetorno = $pastaUnimake . '/retorno/' . $notaFiscal->getUuid() . '-CONS-SIT-' . $rand . '-sit.xml';
        while (true) {
            if (!file_exists($arqRetorno)) {
                sleep(1);
                $count--;
                if ($count <= 0) {
                    throw new \RuntimeException('Erro ao consultar status da Nota Fiscal. (id = [' . $notaFiscal->getId() . ']');
                }
            } else {
                $retorno = simplexml_load_string(file_get_contents($arqRetorno));

                $notaFiscal->setCStat($retorno->cStat->__toString());
                $notaFiscal->setXMotivo($retorno->xMotivo->__toString());

                if ($retorno->protNFe && $retorno->protNFe->infProt && $retorno->protNFe->infProt->nProt) {
                    $notaFiscal->setProtocoloAutorizacao($retorno->protNFe->infProt->nProt->__toString());
                }

                // Verifica os eventos
                if ($retorno->procEventoNFe) {
                    foreach ($retorno->procEventoNFe as $evento) {
                        if ($evento->retEvento->infEvento->tpEvento->__toString() === "110110") {
                            $seq = $evento->retEvento->infEvento->nSeqEvento->__toString();
                            /** @var NotaFiscalCartaCorrecao $cartaCorrecao */
                            $cartaCorrecao = $this->doctrine->getRepository(NotaFiscalCartaCorrecao::class)->findOneBy(['notaFiscal' => $notaFiscal, 'seq' => $seq]);
                            $cartaCorrecao->setMsgRetorno($evento->retEvento->infEvento->xMotivo->__toString());
                            $this->notaFiscalCartaCorrecaoEntityHandler->save($cartaCorrecao);
                        }
                    }
                }

                $this->notaFiscalEntityHandler->save($notaFiscal);
                $this->doctrine->flush();
                break;
            }
        }

        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimir(NotaFiscal $notaFiscal)
    {
        // Z:\enviado\Autorizados\201808
        $id = $notaFiscal->getId();
        if (!$id)
            return;

        $uuid = $notaFiscal->getUuid();

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];

        $pastaAutorizados = $pastaUnimake . '/enviado/Autorizados/' . $notaFiscal->getDtEmissao()->format('Ym') . '/';
        $pastaReimpressao = $pastaUnimake . '/reimpressao/';

        $arquivoProcNFe = $pastaAutorizados . $uuid . '-' . $notaFiscal->getRandFaturam() . '-procNFe.xml';
        $arquivoReimpressao = $pastaReimpressao . $uuid . '-' . $notaFiscal->getRandFaturam() . '-procNFe.xml';
        if (file_exists($arquivoProcNFe)) {
            copy($arquivoProcNFe, $arquivoReimpressao);
        } else {
            throw new \RuntimeException('Arquivo não encontrado para reimpressão: ' . $arquivoProcNFe);
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimirCancelamento(NotaFiscal $notaFiscal)
    {
        // \enviado\Autorizados\201808
        $id = $notaFiscal->getId();
        if (!$id)
            return;

        // 41180877498442000134650040000000701344865736_110111_01-procEventoNFe
        $chaveNota = $notaFiscal->getChaveAcesso();
        $tpEvento = '110111';
        $nSeqEvento = '01';

        $nomeArquivo = $chaveNota . '_' . $tpEvento . '_' . $nSeqEvento . '-procEventoNFe.xml';

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];

        $pastaAutorizados = $pastaUnimake . '/enviado/Autorizados/' . $notaFiscal->getDtEmissao()->format('Ym') . '/';

        if (file_exists($pastaAutorizados . $nomeArquivo)) {
            copy($pastaAutorizados . $nomeArquivo, $pastaUnimake . '/reimpressao/' . $nomeArquivo);
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao)
    {
        $notaFiscal = $cartaCorrecao->getNotaFiscal();
        // \enviado\Autorizados\201808
        $id = $notaFiscal->getId();
        if (!$id)
            return;

        // Estranho que aqui o Unimake não coloca o tpEvento no nome do arquivo. No cancelamento ele coloca.

        $chaveNota = $notaFiscal->getChaveAcesso();
        // $tpEvento = '110110';
        $nSeqEvento = $cartaCorrecao->getSeq();

//        $nomeArquivo = $chaveNota . "_" . $tpEvento . "_" . str_pad($nSeqEvento,2,'0',STR_PAD_LEFT) . "-procEventoNFe.xml";
        $nomeArquivo = $chaveNota . '_' . str_pad($nSeqEvento, 2, '0', STR_PAD_LEFT) . '-procEventoNFe.xml';

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];

//        $pastaAutorizados = $pastaUnimake . '/enviado/Autorizados/' . $notaFiscal->getDtEmissao()->format('Ym') . '/';

        // FIXME: depois que fizer o OneToMany para cartas de correção, isso não é mais necessário.
        FileUtils::getDirContents($pastaUnimake . '/enviado/Autorizados/', $files);
        $achouArquivo = false;
        foreach ($files as $file) {
            if (basename($file) === $nomeArquivo) {
                copy($file, $pastaUnimake . '/reimpressao/' . $nomeArquivo);
                $achouArquivo = true;
                break;
            }
        }
        if (!$achouArquivo) {
            throw new \RuntimeException('Arquivo da carta de correção não encontrado (' . $nomeArquivo . ')');
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function cancelar(NotaFiscal $notaFiscal)
    {
        $notaFiscal = $this->verificaSePrecisaConsultarStatus($notaFiscal);

        if ($notaFiscal->getCStat() !== 100 && $notaFiscal->getCStat() !== 204) {
            throw new \RuntimeException("Nota Fiscal com status diferente de '100' ou de '204' não pode ser cancelada. (id: " . $notaFiscal->getId() . ')');
        }

        $pastaXMLExemplos = $_SERVER['PASTAARQUIVOSXMLEXEMPLO'];

        $exemploNFe = file_get_contents($pastaXMLExemplos . '/-ped-canc4.xml');
        $pedCanc = simplexml_load_string($exemploNFe);

        // Identificador da TAG a ser assinada, a regra de formação do Id é: “ID” + tpEvento + chave da NF-e + nSeqEvento
        // ID1101113511031029073900013955001000000001105112804102

        $tpEvento = '110111';
        $chaveNota = $notaFiscal->getChaveAcesso();
        $nSeqEvento = '01';

        $id = 'ID' . $tpEvento . $chaveNota . $nSeqEvento;

        // número randômico para casos onde várias consultas possam ser feitas
        $rand = random_int(10000000, 99999999);

        $pedCanc->idLote = $rand;
        $pedCanc->evento->infEvento['Id'] = $id;
        $pedCanc->evento->infEvento->cOrgao = '41'; // TODO: substituir aqui pela busca do pessoaEmitente->estado->getCodigoIBGE()
        $pedCanc->evento->infEvento->tpAmb = $notaFiscal->getAmbiente() === 'PROD' ? '1' : '2';
        $pedCanc->evento->infEvento->CNPJ = $notaFiscal->getDocumentoEmitente();
        $pedCanc->evento->infEvento->chNFe = $chaveNota;
        $pedCanc->evento->infEvento->dhEvento = (new \DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d\TH:i:sP');
        $pedCanc->evento->infEvento->tpEvento = '110111';
        $pedCanc->evento->infEvento->detEvento->xJust = $notaFiscal->getMotivoCancelamento();
        $pedCanc->evento->infEvento->detEvento->nProt = $notaFiscal->getProtocoloAutorizacao();

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        file_put_contents($pastaUnimake . '/envio/' . $notaFiscal->getUuid() . '-CANCELAR-' . $rand . '-nfe.xml', $pedCanc->asXML());

        $count = 20;
        $arqRetornoSucesso = $pastaUnimake . '/retorno/' . $notaFiscal->getUuid() . '-CANCELAR-' . $rand . '-ret-env-canc.xml';
        $arqRetornoErro = $pastaUnimake . '/retorno/' . $notaFiscal->getUuid() . '-CANCELAR-' . $rand . '-ret-env-canc.err';
        while (true) {
            if (!file_exists($arqRetornoSucesso) && !file_exists($arqRetornoErro)) {
                sleep(1);
                $count--;
                if ($count <= 0) {
                    throw new \RuntimeException('Erro ao cancelar a Nota Fiscal. (id = [' . $notaFiscal->getId() . ']');
                }
            } else {
                if (file_exists($arqRetornoSucesso)) {
                    $retorno = simplexml_load_string(file_get_contents($arqRetornoSucesso));

                    $notaFiscal->setCStat($retorno->retEvento->infEvento->cStat->__toString());
                    $notaFiscal->setXMotivo($retorno->retEvento->infEvento->xMotivo->__toString());

                    $this->notaFiscalEntityHandler->save($notaFiscal);
                    $this->doctrine->flush();
                    break;
                } else if (file_exists($arqRetornoErro)) {
                    $err = file($arqRetornoErro);
                    $message = explode('|', $err[2])[1];

                    $notaFiscal->setCStat(0);
                    $notaFiscal->setXMotivo($message);
                    $this->notaFiscalEntityHandler->save($notaFiscal);
                    $this->doctrine->flush();
                    return $notaFiscal;
                }
            }
        }

        return $notaFiscal;
    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     * @return NotaFiscalCartaCorrecao
     * @throws ViewException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function cartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): NotaFiscalCartaCorrecao
    {
        $pastaXMLExemplos = $_SERVER['PASTAARQUIVOSXMLEXEMPLO'];

        $exemploNFe = file_get_contents($pastaXMLExemplos . '/-cce4.xml');
        $cartaCorr = simplexml_load_string($exemploNFe);

        // Identificador da TAG a ser assinada, a regra de formação do Id é: “ID” + tpEvento + chave da NF-e + nSeqEvento
        // ID1101113511031029073900013955001000000001105112804102

        $tpEvento = '110110';
        $chaveNota = $cartaCorrecao->getNotaFiscal()->getChaveAcesso();
        $nSeqEvento = $cartaCorrecao->getSeq();

        $id = 'ID' . $tpEvento . $chaveNota . str_pad($nSeqEvento, 2, '0', STR_PAD_LEFT);

        // número randômico para casos onde várias consultas possam ser feitas
        $rand = random_int(100000000000000, 999999999999999);


        $cartaCorr->idLote = $rand;
        $cartaCorr->evento->infEvento['Id'] = $id;
        $cartaCorr->evento->infEvento->cOrgao = '41'; // TODO: substituir aqui pela busca do pessoaEmitente->estado->getCodigoIBGE()
        $cartaCorr->evento->infEvento->tpAmb = $cartaCorrecao->getNotaFiscal()->getAmbiente() === 'PROD' ? '1' : '2';


        $cartaCorr->evento->infEvento->CNPJ = $cartaCorrecao->getNotaFiscal()->getDocumentoEmitente();
        $cartaCorr->evento->infEvento->chNFe = $chaveNota;
        $cartaCorr->evento->infEvento->dhEvento = (new \DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d\TH:i:sP');
        $cartaCorr->evento->infEvento->tpEvento = '110110';
        $cartaCorr->evento->infEvento->nSeqEvento = $nSeqEvento;

        $cartaCorr->evento->infEvento->detEvento->xCorrecao = $cartaCorrecao->getCartaCorrecao();

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        file_put_contents($pastaUnimake . '/envio/' . $cartaCorrecao->getNotaFiscal()->getUuid() . '-CARTACORR-' . $rand . '-nfe.xml', $cartaCorr->asXML());

        $count = 20;
        $arqRetornoSucesso = $pastaUnimake . '/retorno/' . $cartaCorrecao->getNotaFiscal()->getUuid() . '-CARTACORR-' . $rand . '-ret-env-cce.xml';
        $arqRetornoErro = $pastaUnimake . '/retorno/' . $cartaCorrecao->getNotaFiscal()->getUuid() . '-CARTACORR-' . $rand . '-ret-env-cce.err';
        while (true) {
            if (!file_exists($arqRetornoSucesso) && !file_exists($arqRetornoErro)) {
                sleep(1);
                $count--;
                if ($count <= 0) {
                    throw new \RuntimeException('Erro ao enviar CARTA DE CORREÇÃO para a Nota Fiscal. (id = [' . $cartaCorrecao->getId() . ']');
                }
            } else {
                if (file_exists($arqRetornoSucesso)) {
                    $retorno = simplexml_load_string(file_get_contents($arqRetornoSucesso));

                    $msgRetorno = $retorno->retEvento->infEvento->cStat->__toString() .
                        ' (' . $retorno->retEvento->infEvento->xMotivo->__toString() . ')';

                    $cartaCorrecao->setMsgRetorno($msgRetorno);

                    $this->notaFiscalCartaCorrecaoEntityHandler->save($cartaCorrecao);
                    $this->doctrine->flush();
                    break;
                } else if (file_exists($arqRetornoErro)) {
                    $err = file($arqRetornoErro);
                    $message = explode('|', $err[2])[1];

                    $cartaCorrecao->setMsgRetorno($message);

                    $this->notaFiscalCartaCorrecaoEntityHandler->save($cartaCorrecao);
                    $this->doctrine->flush();
                    return $cartaCorrecao;
                }
            }
        }

        return $cartaCorrecao;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws \Exception
     */
    public function verificaSePrecisaConsultarStatus(NotaFiscal $notaFiscal)
    {
        if (!$notaFiscal->getCStat() or !$notaFiscal->getXMotivo() or !$notaFiscal->getProtocoloAutorizacao()) {
            $notaFiscal = $this->consultarStatus($notaFiscal);
        }

        return $notaFiscal;
    }

    /**
     * @param $cnpj
     * @return mixed
     */
    public function consultarCNPJ($cnpj)
    {
        $pastaXMLExemplos = $_SERVER['PASTAARQUIVOSXMLEXEMPLO'];

        $exemplo = file_get_contents($pastaXMLExemplos . '/-cons-cad.xml');
        $consCad = simplexml_load_string($exemplo);
        $consCad->infCons->CNPJ = $cnpj;

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        $rand = random_int(100000000000000, 999999999999999);
        file_put_contents($pastaUnimake . '/envio/' . $cnpj . '-' . $rand . '-consCad.xml', $consCad->asXML());


        $count = 20;
        $arqRetornoSucesso = $pastaUnimake . '/retorno/' . $cnpj . '-' . $rand . '-consCad.xml-ret-cons-cad.xml';
        while (true) {
            if (!file_exists($arqRetornoSucesso)) {
                sleep(1);
                $count--;
                if ($count <= 0) {
                    throw new \RuntimeException('Erro ao consultar CNPJ (' . $cnpj . ')');
                }
            } else {
                if (file_exists($arqRetornoSucesso)) {
                    $retorno = simplexml_load_string(file_get_contents($arqRetornoSucesso));

                    if ($retorno && isset($retorno->infCons)) {
                        $dados['CNPJ'] = $retorno->infCons->CNPJ->__toString();
                        $dados['IE'] = $retorno->infCons->infCad->IE ? $retorno->infCons->infCad->IE->__toString() : null;
                        $dados['UF'] = $retorno->infCons->infCad->UF ? $retorno->infCons->infCad->UF->__toString() : null;
                        $dados['razaoSocial'] = $retorno->infCons->infCad->xNome ? $retorno->infCons->infCad->xNome->__toString() : null;
                        $dados['nomeFantasia'] = $retorno->infCons->infCad->xFant ? $retorno->infCons->infCad->xFant->__toString() : null;
                        if (isset($retorno->infCons->infCad->ender)) {
                            $dados['endereco']['logradouro'] = $retorno->infCons->infCad->ender->xLgr ? $retorno->infCons->infCad->ender->xLgr->__toString() : null;
                            $dados['endereco']['numero'] = $retorno->infCons->infCad->ender->nro ? $retorno->infCons->infCad->ender->nro->__toString() : null;
                            $dados['endereco']['bairro'] = $retorno->infCons->infCad->ender->xBairro ? $retorno->infCons->infCad->ender->xBairro->__toString() : null;
                            $dados['endereco']['cidade'] = $retorno->infCons->infCad->ender->xMun ? $retorno->infCons->infCad->ender->xMun->__toString() : null;
                            $dados['endereco']['cep'] = $retorno->infCons->infCad->ender->CEP ? $retorno->infCons->infCad->ender->CEP->__toString() : null;
                        }
                        return $dados;
                    }


                    break;
                }
            }
        }
    }
}
