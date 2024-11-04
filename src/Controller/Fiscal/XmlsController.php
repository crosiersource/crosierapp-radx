<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Messenger\CrosierQueueHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalCartaCorrecaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Fiscal\NotaFiscalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class XmlsController extends FormListController
{

    /** @var NotaFiscalEntityHandler */
    protected $entityHandler;

    /** @required */
    public NotaFiscalBusiness $notaFiscalBusiness;

    /** @required */
    public SpedNFeBusiness $spedNFeBusiness;

    /** @required */
    public NFeUtils $nfeUtils;

    /** @required */
    public NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler;

    /** @required */
    public NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler;


    /**
     * @Route("/api/fis/notaFiscal/downloadXML/{nf}", name="api_fis_notaFiscal_downloadXML", requirements={"nf"="\d+"})
     * @throws ViewException
     */
    public function downloadXML(Request $request, NotaFiscal $nf): Response
    {
        $filename = $nf->chaveAcesso . '-' . strtolower($nf->tipoNotaFiscal) . '.xml';

        if (!$nf->getXMLDecoded() || $request->get('regerar')) {
            $nf = $this->spedNFeBusiness->gerarXML($nf);
        }
        if (!$nf->getXMLDecoded()) {
            throw new ViewException('XMLDecoded n/d');
        }

//        if ($nf->getXMLDecoded()->getName() !== 'nfeProc' && !$request->get('naoAssinar')) {
//            $nf = $this->spedNFeBusiness->gerarXML($nf);
//            $tools = $this->nfeUtils->getToolsEmUso();
//            $tools->model($nf->tipoNotaFiscal === 'NFE' ? '55' : '65');
//            $this->notaFiscalBusiness->handleIdeFields($nf);
//            $fileContent = $tools->signNFe($nf->getXmlNota());
//        } else {
        $fileContent = $nf->getXMLDecodedAsString();
//        }

        // Return a response with a specific content
        $response = new Response($fileContent);
        $response->headers->set('Content-Type', 'application/xml');

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;
    }


    /**
     * @Route("/api/fis/notaFiscal/downloadXMLsMesAno", name="api_fis_notaFiscal_downloadXMLsMesAno")
     * @throws ViewException
     */
    public function downloadXMLsMesAno(Request $request): Response
    {
        $documentoEmitente = $request->get('documentoEmitente') ?? $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
        
        $mesano = $request->get('mesano');
        if ($mesano) {
            $dtsMesAno = DateTimeUtils::getDatasMesAno($mesano);
            $dtEmissaoDe = $dtsMesAno['i'];
            $dtEmissaoAte = $dtsMesAno['f'];
            if (!$dtEmissaoDe || !$dtEmissaoAte) {
                return CrosierApiResponse::error(null, false, 'Período n/d');
            }
        } else {
            $dtEmissaoDe = DateTimeUtils::parseDateStr($request->query->get('dtEmissao')['after']);
            $dtEmissaoAte = DateTimeUtils::parseDateStr($request->query->get('dtEmissao')['before']);
        }
        
        if (DateTimeUtils::diffInDias($dtEmissaoAte, $dtEmissaoDe) > 30) {
            return CrosierApiResponse::error(null, false, 'Para o download dos XMLs o período não pode ser superior a 31 dias');
        }
        
        $zip = new \ZipArchive();
        $filename = $documentoEmitente . '_' . $dtEmissaoDe->format('Ymd') . '-' . $dtEmissaoAte->format('Ymd');
        $arquivo = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . $filename . '.zip';
        @unlink($arquivo);

        if ($zip->open($arquivo, \ZipArchive::CREATE) !== TRUE) {
            return CrosierApiResponse::error(null, false, 'Erro ao gerar arquivo zip');
        }
        
        $filters = [
            ['dtEmissao', 'BETWEEN_DATE', [$dtEmissaoDe, $dtEmissaoAte]],
            ['documentoEmitente', 'EQ', $documentoEmitente],
            ['numero', 'IS_NOT_EMPTY'],
            ['ambiente', 'EQ', 'PROD']
        ];
        
        if ($request->get('numero')) {
            $filters[] = ['numero', 'EQ', $request->get('numero')];
        }
        if ($request->get('serie')) {
            $filters[] = ['serie', 'EQ', $request->get('serie')];
        }
        if ($request->get('documentoDestinatario')) {
            $filters[] = ['documentoDestinatario', 'EQ', $request->get('documentoDestinatario')];
        }
        if ($request->get('xnomeDestinatario')) {
            $filters[] = ['xnomeDestinatario', 'LIKE', '%' . $request->get('xnomeDestinatario') . '%'];
        }
        if ($request->get('chaveAcesso')) {
            $filters[] = ['chaveAcesso', 'EQ', $request->get('chaveAcesso')];
        }
        if ($request->get('valorTotal')) {
            $filters[] = ['valorTotal', 'EQ', $request->get('valorTotal')];
        }
        if ($request->get('naturezaOperacao')) {
            $filters[] = ['naturezaOperacao', 'LIKE', '%' . $request->get('naturezaOperacao') . '%'];
        }
        if ($request->get('finalidadeNf') && $request->get('finalidadeNf') !== 'null') {
            $filters[] = ['finalidadeNf', 'EQ', $request->get('finalidadeNf')];
        }
        
        

        /** @var NotaFiscalRepository $repoNotasFiscais */
        $repoNotasFiscais = $this->doctrine->getRepository(NotaFiscal::class);

        $nfes = $repoNotasFiscais->findByFiltersSimpl($filters, ['serie' => 'ASC', 'numero' => 'ASC'], 0, -1);

        // homologadas
        $nfes100 = [];
        // canceladas
        $nfes101 = [];

        // homologadas
        $nfces100 = [];
        // canceladas
        $nfces101 = [];
        
        $nfesDevolucao = [];
        $nfcesDevolucao = [];

        $verifNumeros = [];

        $problemas = [];
        $problemas[] = 'CNPJ: ' . $documentoEmitente;

        /** @var NotaFiscal $nf */
        foreach ($nfes as $nf) {
            $this->logger->info($nf->tipoNotaFiscal . '. Série: ' . $nf->serie . ', Número: ' . $nf->numero . '.');
            if (!$nf->numero) {
                $this->logger->info('Nota sem número. Continuando...');
                continue;
            }
            if (!$nf->cStat) {
                $this->logger->info('Nota sem "cstat". Continuando...');
                continue;
            }

            if ((int)$nf->cStat === -100) {
                $this->spedNFeBusiness->consultarStatus($nf);
            }

            if (((int)$nf->cStat === 100 || (int)$nf->cStat === 101) && !$nf->getXmlNota()) {
                if ((int)$nf->cStatLote === 217) {
                    $msg = 'NFE (Chave: ' . $nf->chaveAcesso . ') com statLote = 217 (NF-E NAO CONSTA NA BASE DE DADOS DA SEFAZ). Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
                $this->logger->info('XML não encontrado para nota ' . $nf->chaveAcesso);
                $nf = $this->spedNFeBusiness->gerarXML($nf);
                $tools = $this->nfeUtils->getToolsByCNPJ($nf->documentoEmitente);
                $tools->model($nf->tipoNotaFiscal === 'NFE' ? '55' : '65');
                $fileContent = $tools->signNFe($nf->getXmlNota());
                $nf->jsonData['xml_assinado'][] = $fileContent;
                $nf->setXmlNota($fileContent);
                $this->entityHandler->save($nf);
            }
            if (!$nf->getXMLDecoded()) {
                $this->logger->info('getXMLDecoded não encontrado para nota ' . $nf->chaveAcesso);
            }
            if ($nf->getXMLDecoded()->getName() !== 'nfeProc') {
                $this->logger->info('XML sem o nfeProc. Consultando status...');
                $this->spedNFeBusiness->consultarStatus($nf);
                if ((int)$nf->cStatLote !== 104 && (int)$nf->cStatLote !== 100) {
                    $msg = $nf->tipoNotaFiscal . '. Série: ' . $nf->serie . ', Número: ' . $nf->numero . ': cStatLote: ' . $nf->cStatLote . ', xMotivoLote: ' . $nf->xMotivoLote;
                    $this->logger->error($msg);
                    $problemas[] = $msg;
                    continue;
                }
            }

            if ($nf->tipoNotaFiscal === 'NFE') {
                if ((int)$nf->cStat === 100) {
                    if ($nf->finalidadeNf === 'DEVOLUCAO') {
                        $nfesDevolucao[] = $nf;
                    } else {
                        $nfes100[] = $nf;
                    }
                } elseif ((int)$nf->cStat === 101 || ((int)$nf->cStat === 135 && (int)$nf->cStatLote === 101)) {
                    $problemas[] = 'NFE ' . $nf->numero . ' (Chave: ' . $nf->chaveAcesso . ') CANCELADA';
                    $nfes101[] = $nf;
                } else {
                    $msg = 'NFE ' . $nf->numero . ' (Chave: ' . $nf->chaveAcesso . ') com status diferente de 100 ou 101. Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
            } elseif ($nf->tipoNotaFiscal === 'NFCE') {
                if ((int)$nf->cStat === 100) {
                    if ($nf->finalidadeNf === 'DEVOLUCAO') {
                        $nfcesDevolucao[] = $nf;
                    } else {
                        $nfces100[] = $nf;
                    }
                } elseif ((int)$nf->cStat === 101) {
                    $nfces101[] = $nf;
                } else {
                    $msg = 'NFE ' . $nf->numero . ' (Chave: ' . $nf->chaveAcesso . ') com status diferente de 100 ou 101. Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
            }
            $verifNumeros[$nf->tipoNotaFiscal][] = $nf->numero;
        }

        foreach ($verifNumeros as $tipo => $numeros) {
            $aux = $numeros[0];
            foreach ($numeros as $numero) {
                if ($numero !== $aux) {
                    $msg = 'Número pulado: ' . $aux . ', tipo: ' . $tipo;
                    $problemas[] = $msg;
                    $aux = $numero;
                }
                $aux++;
            }
        }

        if (!file_exists($_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/')) {
            if (!@mkdir($concurrentDirectory = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/', 0777, true) && !is_dir($concurrentDirectory)) {
                return CrosierApiResponse::error(null, false, sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        /** @var NotaFiscal $nfe100 */
        foreach ($nfes100 as $nfe100) {
            $nomeArquivo = $nfe100->chaveAcesso . '-' . $nfe100->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfe100->getXmlNota());
            touch($arquivoCompleto, $nfe100->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFEs/homologadas/' . $nomeArquivo);
        }

        foreach ($nfes101 as $nfe101) {
            $nomeArquivo = $nfe101->chaveAcesso . '-' . $nfe101->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfe101->getXmlNota());
            touch($arquivoCompleto, $nfe101->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFEs/canceladas/' . $nomeArquivo);
        }

        foreach ($nfces100 as $nfce100) {
            $nomeArquivo = $nfce100->chaveAcesso . '-' . $nfce100->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfce100->getXmlNota());
            touch($arquivoCompleto, $nfce100->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFCEs/homologadas/' . $nomeArquivo);
        }

        foreach ($nfces101 as $nfce101) {
            $nomeArquivo = $nfce101->chaveAcesso . '-' . $nfce101->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfce101->getXmlNota());
            touch($arquivoCompleto, $nfce101->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFCEs/canceladas/' . $nomeArquivo);
        }

        foreach ($nfesDevolucao as $nfDevolucao) {
            $nomeArquivo = $nfDevolucao->chaveAcesso . '-' . $nfDevolucao->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfDevolucao->getXmlNota());
            touch($arquivoCompleto, $nfDevolucao->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFEs/devolucoes/' . $nomeArquivo);
        }
        
        foreach ($nfcesDevolucao as $nfDevolucao) {
            $nomeArquivo = $nfDevolucao->chaveAcesso . '-' . $nfDevolucao->numero . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfDevolucao->getXmlNota());
            touch($arquivoCompleto, $nfDevolucao->dtEmissao->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFCEs/devolucoes/' . $nomeArquivo);
        }

        $zip->addFromString('avisos.txt', implode(PHP_EOL, $problemas));

        $zip->close();

        // Return a response with a specific content
        $response = new Response(file_get_contents($arquivo));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-length', filesize($arquivo));
        $response->headers->set('filename', $filename . '.zip');

        // Set the content disposition
        $response->headers->set('Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename . '.zip'
            )
        );
        @rmdir($_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/');
        // Dispatch request
        return $response;
    }


    /**
     * @Route("/api/notaFiscal/enviarXmlParaPasta/{id}", name="teste")
     */
    public function teste(CrosierQueueHandler $h, int $id): Response
    {
        $h->post('fiscal.eventos.nova_nf_com_xml', ['id' => $id]);
        return new Response('OK');
    }


}
