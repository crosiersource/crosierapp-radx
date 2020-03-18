<?php

namespace App\Business\Fiscal;

use App\Entity\Fiscal\FinalidadeNF;
use App\Entity\Fiscal\ModalidadeFrete;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalCartaCorrecao;
use App\Entity\Fiscal\NotaFiscalEvento;
use App\Entity\Fiscal\TipoNotaFiscal;
use App\EntityHandler\Fiscal\NotaFiscalCartaCorrecaoEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalEventoEntityHandler;
use App\Repository\Fiscal\NotaFiscalRepository;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\APIClient\CrosierEntityIdAPIClient;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Municipio;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\MunicipioRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Classe que trata da integração com a RF via projeto nfephp-org
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class SpedNFeBusiness
{

    private NotaFiscalEntityHandler $notaFiscalEntityHandler;

    private NotaFiscalCartaCorrecaoEntityHandler $notaFiscalCartaCorrecaoEntityHandler;

    private EntityManagerInterface $doctrine;

    private CrosierEntityIdAPIClient $crosierEntityIdAPIClient;

    private LoggerInterface $logger;

    private NFeUtils $nfeUtils;

    private ParameterBagInterface $params;

    private NotaFiscalEventoEntityHandler $notaFiscalEventoEntityHandler;


    /**
     * @param EntityManagerInterface $doctrine
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     * @param NotaFiscalCartaCorrecaoEntityHandler $notaFiscalCartaCorrecaoEntityHandler
     * @param CrosierEntityIdAPIClient $crosierEntityIdAPIClient
     * @param LoggerInterface $logger
     * @param NFeUtils $nfeUtils
     * @param ParameterBagInterface $params
     */
    public function __construct(EntityManagerInterface $doctrine,
                                NotaFiscalEntityHandler $notaFiscalEntityHandler,
                                NotaFiscalEventoEntityHandler $notaFiscalEventoEntityHandler,
                                NotaFiscalCartaCorrecaoEntityHandler $notaFiscalCartaCorrecaoEntityHandler,
                                CrosierEntityIdAPIClient $crosierEntityIdAPIClient,
                                LoggerInterface $logger,
                                NFeUtils $nfeUtils,
                                ParameterBagInterface $params)
    {
        $this->doctrine = $doctrine;
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
        $this->notaFiscalCartaCorrecaoEntityHandler = $notaFiscalCartaCorrecaoEntityHandler;
        $this->crosierEntityIdAPIClient = $crosierEntityIdAPIClient;
        $this->logger = $logger;
        $this->nfeUtils = $nfeUtils;
        $this->params = $params;
        $this->notaFiscalEventoEntityHandler = $notaFiscalEventoEntityHandler;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     */
    public function gerarXML(NotaFiscal $notaFiscal): NotaFiscal
    {
        $exemploNFe = file_get_contents($this->params->get('kernel.project_dir') . '/files/Fiscal/exemplos/exemplo-nfe.xml');
        $nfe = simplexml_load_string($exemploNFe);
        if (!$nfe) {
            throw new \RuntimeException('Não foi possível obter o template XML da NFe');
        }
        $nfe->infNFe->ide->nNF = $notaFiscal->getNumero();

        $nfe->infNFe->ide->cNF = $notaFiscal->getCnf();

        $nfe->infNFe->ide->mod = TipoNotaFiscal::get($notaFiscal->getTipoNotaFiscal())['codigo'];
        $nfe->infNFe->ide->serie = $notaFiscal->getSerie();

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

        $nfeConfigs = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());

        $nfe->infNFe->emit->CNPJ = $nfeConfigs['cnpj'];
        $nfe->infNFe->emit->xNome = $nfeConfigs['razaosocial'];
        $nfe->infNFe->emit->xFant = $nfeConfigs['razaosocial'];
        $nfe->infNFe->emit->IE = $nfeConfigs['ie'];
        $nfe->infNFe->emit->enderEmit->xLgr = $nfeConfigs['enderEmit_xLgr'];
        $nfe->infNFe->emit->enderEmit->nro = $nfeConfigs['enderEmit_nro'];
        $nfe->infNFe->emit->enderEmit->xBairro = $nfeConfigs['enderEmit_xBairro'];
        $nfe->infNFe->emit->enderEmit->CEP = preg_replace('/\D/', '', $nfeConfigs['enderEmit_cep']);
        $nfe->infNFe->emit->enderEmit->fone = preg_replace('/\D/', '', $nfeConfigs['telefone']);


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


                $nfe->infNFe->dest->enderDest->CEP = preg_replace('/\D/', '', $notaFiscal->getCepDestinatario());
                $nfe->infNFe->dest->enderDest->cPais = 1058;
                $nfe->infNFe->dest->enderDest->xPais = 'BRASIL';
                if (trim($notaFiscal->getFoneDestinatario())) {
                    $nfe->infNFe->dest->enderDest->fone = preg_replace('/\D/', '', $notaFiscal->getFoneDestinatario());
                }
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
                $itemXML->imposto->ICMS->ICMSSN900->vBC = number_format(abs($nfItem->getIcmsValorBc()), 2, '.', '');
                $itemXML->imposto->ICMS->ICMSSN900->pICMS = $nfItem->getIcmsAliquota();
                $itemXML->imposto->ICMS->ICMSSN900->vICMS = number_format(abs($nfItem->getIcmsValor()), 2, '.', '');
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

        $nfe->infNFe->infRespTec->CNPJ = $nfeConfigs['cnpj'];
        $nfe->infNFe->infRespTec->xContato = 'Carlos Eduardo Pauluk';
        $nfe->infNFe->infRespTec->email = 'carlospauluk@gmail.com';
        $nfe->infNFe->infRespTec->fone = preg_replace('/\D/', '', $nfeConfigs['telefone']);


        // Número randômico para que não aconteça de pegar XML de retorno de tentativas de faturamento anteriores
        $rand = random_int(10000000, 99999999);
        $notaFiscal->setRandFaturam($rand);

        $notaFiscal->setCStatLote(-100);
        $notaFiscal->setXMotivoLote('AGUARDANDO FATURAMENTO');

        $this->notaFiscalEntityHandler->save($notaFiscal);

        $notaFiscal->setXmlNota($nfe->asXML());

        /** @var NotaFiscal $notaFiscal */
        $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     */
    public function enviaNFe(NotaFiscal $notaFiscal): NotaFiscal
    {
        try {
            $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoEmitente());
            $tools->model($notaFiscal->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
            if (!isset($notaFiscal->getXMLDecoded()->infNFe->Signature) && !isset($notaFiscal->getXMLDecoded()->Signature)) {
                $xmlAssinado = $tools->signNFe($notaFiscal->getXmlNota());
                $notaFiscal->setXmlNota($xmlAssinado);
                $this->notaFiscalEntityHandler->save($notaFiscal);
            } else {
                $xmlAssinado = $notaFiscal->getXmlNota();
            }
            $idLote = random_int(1000000000000, 9999999999999);
            $resp = $tools->sefazEnviaLote([$xmlAssinado], $idLote);//transforma o xml de retorno em um stdClass
            $st = new Standardize();
            $std = $st->toStd($resp);
            $notaFiscal->setCStatLote($std->cStat);
            $notaFiscal->setXMotivoLote($std->xMotivo);
            if ((string)$std->cStat === '103') {
                $notaFiscal->setNRec($std->infRec->nRec);
            }
            $this->notaFiscalEntityHandler->save($notaFiscal);
            $tentativa = 1;
            while (true) {
                $this->consultaRecibo($notaFiscal);
                if (!$notaFiscal->getCStat() || (int)$notaFiscal->getCStat() === -100) {
                    sleep(1);
                    if (++$tentativa === 4) break;
                } else {
                    break;
                }
            }
            return $notaFiscal;
        } catch (\Throwable $e) {
            $this->logger->error('enviaNFe - id: ' . $notaFiscal->getId());
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao enviar a NFe');
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     */
    public function consultarStatus(NotaFiscal $notaFiscal): NotaFiscal
    {
        //$content = conteúdo do certificado PFX
        $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoEmitente());
        $tools->model($notaFiscal->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
        //consulta número de recibo
        //$numeroRecibo = número do recíbo do envio do lote
        $tpAmb = $notaFiscal->getAmbiente() === 'PROD' ? '1' : '2';
        $xmlResp = $tools->sefazConsultaChave($notaFiscal->getChaveAcesso(), $tpAmb);
        //transforma o xml de retorno em um stdClass
        $st = new Standardize();
        $std = $st->toStd($xmlResp);

        $notaFiscal->setCStatLote($std->cStat);
        $notaFiscal->setXMotivoLote($std->xMotivo);

        if ($std->cStat === '104' || $std->cStat === '100') { //lote processado (tudo ok)
            $cStat = $std->protNFe->infProt->cStat;
            $notaFiscal->setCStat($cStat);
            $notaFiscal->setXMotivo($std->protNFe->infProt->xMotivo);
            if ($notaFiscal->getXmlNota() && $notaFiscal->getXMLDecoded()->getName() !== 'nfeProc') {
                try {
                    if (!isset($notaFiscal->getXMLDecoded()->infNFe->Signature) &&
                        !isset($notaFiscal->getXMLDecoded()->Signature)) {
                        $xmlAssinado = $tools->signNFe($notaFiscal->getXmlNota());
                        $notaFiscal->setXmlNota($xmlAssinado);
                    }
                    $r = Complements::toAuthorize($notaFiscal->getXmlNota(), $xmlResp);
                    $notaFiscal->setXmlNota($r);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->logger->error('Erro no Complements::toAuthorize para $notaFiscal->id = ' . $notaFiscal->getId());
                }
            }
            if (in_array($cStat, ['100', '302'])) { //DENEGADAS
                $notaFiscal->setProtocoloAutorizacao($std->protNFe->infProt->nProt);
                $notaFiscal->setDtProtocoloAutorizacao(DateTimeUtils::parseDateStr($std->protNFe->infProt->dhRecbto));
            }
        } else if ($std->cStat === 217) {
            $this->consultaRecibo($notaFiscal);
        }
        /** @var NotaFiscal $notaFiscal */
        $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     * @throws \Exception
     */
    public function cancelar(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getCStat() !== 100 && $notaFiscal->getCStat() !== 204) {
            throw new \RuntimeException('Nota Fiscal com status diferente de \'100\' ou de \'204\' não pode ser cancelada. (id: ' . $notaFiscal->getId() . ')');
        }

        $nfeConfigs = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());
        if ($notaFiscal->getDocumentoEmitente() !== $nfeConfigs['cnpj']) {
            throw new ViewException('Documento Emitente diferente do CNPJ configurado');
        }

        $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoEmitente());
        $tools->model($notaFiscal->getTipoNotaFiscal() === 'NFE' ? '55' : '65');

        $chaveNota = $notaFiscal->getChaveAcesso();
        $xJust = $notaFiscal->getMotivoCancelamento();
        $nProt = $notaFiscal->getProtocoloAutorizacao();
        $response = $tools->sefazCancela($chaveNota, $xJust, $nProt);

        $stdCl = new Standardize($response);
        $std = $stdCl->toStd();

        //verifique se o evento foi processado
        if ((string)$std->cStat !== '128') {
            $notaFiscal->setCStat($std->cStat);
            $notaFiscal->setXMotivo($std->retEvento->infEvento->xMotivo);
            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
        } else {
            $cStat = $std->retEvento->infEvento->cStat;
            if ($cStat == '101' || $cStat == '155' || $cStat == '135') {
                $xml = Complements::toAuthorize($tools->lastRequest, $response);

                $notaFiscal->setCStat($cStat);
                $notaFiscal->setXMotivo($std->retEvento->infEvento->xMotivo);
                /** @var NotaFiscal $notaFiscal */
                $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);

                $evento = new NotaFiscalEvento();
                $evento->setNotaFiscal($notaFiscal);
                $evento->setXml($xml);
                $evento->setDescEvento('CANCELAMENTO');
                $evento->setNSeqEvento(1);
                $evento->setTpEvento(110111);
                $this->notaFiscalEventoEntityHandler->save($evento);
            } else {
                $notaFiscal->setCStat($cStat);
                $notaFiscal->setXMotivo($std->retEvento->infEvento->xMotivo);
                /** @var NotaFiscal $notaFiscal */
                $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
            }
        }

        return $notaFiscal;
    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     * @return NotaFiscalCartaCorrecao
     * @throws ViewException
     * @throws \Exception
     */
    public function cartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): NotaFiscalCartaCorrecao
    {
        $tools = $this->nfeUtils->getToolsByCNPJ($cartaCorrecao->getNotaFiscal()->getDocumentoEmitente());
        $tools->model($cartaCorrecao->getNotaFiscal()->getTipoNotaFiscal() === 'NFE' ? '55' : '65');

        $chave = $cartaCorrecao->getNotaFiscal()->getChaveAcesso();
        $nSeqEvento = $cartaCorrecao->getSeq();

        $response = $tools->sefazCCe($chave, $cartaCorrecao->getCartaCorrecao(), $nSeqEvento);

        $stdCl = new Standardize($response);
        $std = $stdCl->toStd();

        //verifique se o evento foi processado
        if ($std->cStat != 128) {
            $this->logger->error('Erro ao enviar carta de correção');
            $this->logger->error('$std->cStat != 128');
        } else {
            $cStat = $std->retEvento->infEvento->cStat;
            if ($cStat == '135' || $cStat == '136') {
                //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
                $xml = Complements::toAuthorize($tools->lastRequest, $response);
                $cartaCorrecao->setMsgRetorno($xml);
                $cartaCorrecao = $this->notaFiscalCartaCorrecaoEntityHandler->save($cartaCorrecao);
            } else {
                $this->logger->error('Erro ao enviar carta de correção');
                $this->logger->error('cStat: ' . $cStat);
            }
        }

        return $cartaCorrecao;

    }

    /**
     * @param string $cnpj
     * @param string $uf
     * @return mixed
     * @throws ViewException
     */
    public function consultarCNPJ(string $cnpj, string $uf)
    {
        try {
            $tools = $this->nfeUtils->getToolsEmUso();
            $iest = '';
            $cpf = '';
            $response = $tools->sefazCadastro($uf, $cnpj, $iest, $cpf);
            $xmlResp = simplexml_load_string($response);
            $xmlResp->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $xml = $xmlResp->xpath('//soap:Body');
            return $xml[0]->nfeResultMsg->retConsCad->infCons;
        } catch (\Exception $e) {
            throw new ViewException('Erro ao consultar o CNPJ');
        }
    }


    /**
     * @param NotaFiscal $notaFiscal
     * @param int $codManifest
     * @return void
     * @throws ViewException
     */
    public function manifestar(NotaFiscal $notaFiscal, int $codManifest)
    {
        try {
            // Código do evento:
            // 210200 - Confirmação da Operação
            // 210210 - Ciência da Operação
            // 210220 - Desconhecimento da Operação
            // 210240 - Operação não Realizada

            $tpEvento = $codManifest; //ciencia da operação
            $xJust = ''; //a ciencia não requer justificativa
            $nSeqEvento = 1;

            $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoDestinatario());

            $response = $tools->sefazManifesta($notaFiscal->getChaveAcesso(), $tpEvento, $xJust, $nSeqEvento);
            $st = new Standardize($response);

            if ($st->simpleXml()->cStat->__toString() === '128') {
                $notaFiscal->setManifestDest('210210 - Ciência da Operação');
            }
            $notaFiscal->setDtManifestDest(new \DateTime());

            $this->notaFiscalEntityHandler->save($notaFiscal);

        } catch (\Exception $e) {
            $this->logger->error('Erro ao processar XML');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao manifestar DFe (chave: ' . $notaFiscal->getChaveAcesso() . ')');
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return void
     * @throws ViewException
     */
    public function consultaChave(NotaFiscal $notaFiscal)
    {
        try {
            $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoEmitente());
            $tools->model($notaFiscal->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
            $response = $tools->sefazConsultaChave($notaFiscal->getChaveAcesso());

            //você pode padronizar os dados de retorno atraves da classe abaixo
            //de forma a facilitar a extração dos dados do XML
            //NOTA: mas lembre-se que esse XML muitas vezes será necessário,
            //      quando houver a necessidade de protocolos
            $stdCl = new Standardize($response);
            //nesse caso $std irá conter uma representação em stdClass do XML
            $std = $stdCl->toStd();
            //nesse caso o $arr irá conter uma representação em array do XML
            $arr = $stdCl->toArray();
            //nesse caso o $json irá conter uma representação em JSON do XML
            $json = $stdCl->toJson();


        } catch (\Exception $e) {
            $this->logger->error('Erro ao processar XML');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao consultaChaveDFe (chave: ' . $notaFiscal->getChaveAcesso() . ')');
        }
    }


    /**
     * @throws \Exception
     */
    public function deletarNaoNotas(): void
    {
        // Obtém todas as fis_nf que não tenham dtEmissao (arbitrário)
        /** @var NotaFiscalRepository $repo */
        $repo = $this->doctrine->getRepository(NotaFiscal::class);
        $nfes = $repo->findNotasNaoProcessadas();

        $idsADeletar = [];

        /** @var NotaFiscal $nf */
        foreach ($nfes as $nf) {
            try {
                $xml = $nf->getXMLDecoded();

                if ($xml->getName() === 'nfeProc') {
                    continue;
                }

                if ($xml->getName() === 'resNFe') {
                    continue;
                }

                if ($xml->getName() === 'resEvento') {
                    $idsADeletar[] = $nf->getId();
                    continue;
                }


                throw new ViewException('XML inválido (fis_nf.id = ' . $nf->getId() . ')');
            } catch (\Exception $e) {
                $this->logger->error('Erro ao fazer o parse do xml para NF (chave: ' . $nf->getChaveAcesso() . ')');
            }
        }

        foreach ($idsADeletar as $id) {
            $this->notaFiscalEntityHandler->delete($repo->find($id));
        }

    }

    /**
     * Imprime pelo Unimake.
     *
     * @param NotaFiscal $notaFiscal
     */
    public function imprimir(NotaFiscal $notaFiscal)
    {
        $uuid = $notaFiscal->getUuid();
        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        $pastaReimpressao = $pastaUnimake . '/reimpressao/';
        $arquivoReimpressao = $pastaReimpressao . $uuid . '-' . $notaFiscal->getRandFaturam() . '-procNFe.xml';
        file_put_contents($arquivoReimpressao, $notaFiscal->getXmlNota());
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimirCancelamento(NotaFiscal $notaFiscal)
    {

    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     */
    public function imprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao)
    {

    }


    /**
     * @param string $tipoNotaFiscal
     * @param int $serie
     * @param int $numero
     * @return array
     */
    public function inutilizaNumeracao(string $tipoNotaFiscal, int $serie, int $numero)
    {
        try {
            $tools = $this->nfeUtils->getToolsEmUso();
            $tools->model($tipoNotaFiscal === 'NFE' ? '55' : '65');
            $xJust = 'Erro de digitação dos números sequencias das notas';
            $response = $tools->sefazInutiliza($serie, $numero, $numero, $xJust, 1);
            $stdCl = new Standardize($response);
            return $stdCl->toArray();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @throws ViewException
     */
    public function consultaRecibo(NotaFiscal $notaFiscal)
    {
        try {
            if (!$notaFiscal->getNRec()) {
                throw new ViewException('nRec N/D');
            }
            $tools = $this->nfeUtils->getToolsByCNPJ($notaFiscal->getDocumentoEmitente());
            $tools->model($notaFiscal->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
            $xmlResp = $tools->sefazConsultaRecibo($notaFiscal->getNRec());
            $std = (new Standardize($xmlResp))->toStd();
            $notaFiscal->setCStatLote($std->cStat);
            $notaFiscal->setXMotivoLote($std->xMotivo);
            if ((int)$std->cStat === 104 || (int)$std->cStat === 100) { //lote processado (tudo ok)
                $cStat = $std->protNFe->infProt->cStat;
                $notaFiscal->setCStat($cStat);
                $notaFiscal->setXMotivo($std->protNFe->infProt->xMotivo);
                if ($notaFiscal->getXmlNota() && $notaFiscal->getXMLDecoded()->getName() !== 'nfeProc') {
                    try {
                        if (!isset($notaFiscal->getXMLDecoded()->infNFe->Signature) &&
                            !isset($notaFiscal->getXMLDecoded()->Signature)) {
                            $xmlAssinado = $tools->signNFe($notaFiscal->getXmlNota());
                            $notaFiscal->setXmlNota($xmlAssinado);
                        }
                        $r = Complements::toAuthorize($notaFiscal->getXmlNota(), $xmlResp);
                        $notaFiscal->setXmlNota($r);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                        $this->logger->error('Erro no Complements::toAuthorize para $notaFiscal->id = ' . $notaFiscal->getId());
                    }
                }
                if (in_array($cStat, ['100', '302'])) { //DENEGADAS
                    $notaFiscal->setProtocoloAutorizacao($std->protNFe->infProt->nProt);
                    $notaFiscal->setDtProtocoloAutorizacao(DateTimeUtils::parseDateStr($std->protNFe->infProt->dhRecbto));
                }
            }
            $this->notaFiscalEntityHandler->save($notaFiscal);
        } catch (\Throwable $e) {
            $this->logger->error('consultaRecibo - Id: ' . $notaFiscal->getId());
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao consultar recibo');
        }
    }

}