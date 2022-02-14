<?php

namespace App\Controller\Fiscal;

use App\Form\Fiscal\NotaFiscalCartaCorrecaoType;
use App\Form\Fiscal\NotaFiscalItemType;
use App\Form\Fiscal\NotaFiscalType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\ValidaCPFCNPJ;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalCartaCorrecao;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalCartaCorrecaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Fiscal\NotaFiscalRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use NFePHP\DA\NFe\Daevento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

/**
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalController extends FormListController
{

    /** @var NotaFiscalEntityHandler */
    protected $entityHandler;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private SpedNFeBusiness $spedNFeBusiness;

    private NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler;

    private NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler;

    private NFeUtils $nfeUtils;

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }

    /**
     * @required
     * @param SpedNFeBusiness $spedNFeBusiness
     */
    public function setSpedNFeBusiness(SpedNFeBusiness $spedNFeBusiness): void
    {
        $this->spedNFeBusiness = $spedNFeBusiness;
    }

    /**
     * @required
     * @param NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler
     */
    public function setNotaFiscalItemEntityHandler(NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler): void
    {
        $this->notaFiscalItemEntityHandler = $notaFiscalItemEntityHandler;
    }

    /**
     * @required
     * @param NotaFiscalEntityHandler $entityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler
     */
    public function setCartaCorrecaoEntityHandler(NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler): void
    {
        $this->cartaCorrecaoEntityHandler = $cartaCorrecaoEntityHandler;
    }

    /**
     * @required
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }


    /**
     *
     * @Route("/fis/notaFiscal/emitidas/imprimirCancelamento/{notaFiscal}", name="fis_notaFiscal_emitidas_imprimirCancelamento")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     */
    public function imprimirCancelamento(NotaFiscal $notaFiscal): RedirectResponse
    {
        try {

            $conn = $this->getEntityHandler()->getDoctrine()->getConnection();
            $evento = $conn->fetchAssociative('SELECT xml FROM fis_nf_evento WHERE desc_evento = \'CANCELAMENTO\' AND nota_fiscal_id = :notaFiscalId', ['notaFiscalId' => $notaFiscal->getId()]);

            $xml = $evento['xml'];

            $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->documentoEmitente);

            $dadosEmitente = [
                'razao' => $nfeConfigsEmUso['razaosocial'],
                'logradouro' => $nfeConfigsEmUso['enderEmit_xLgr'],
                'numero' => $nfeConfigsEmUso['enderEmit_nro'],
                'complemento' => '',
                'bairro' => $nfeConfigsEmUso['enderEmit_xBairro'],
                'CEP' => $nfeConfigsEmUso['enderEmit_cep'],
                'municipio' => $nfeConfigsEmUso['enderEmit_xMun'],
                'UF' => $nfeConfigsEmUso['enderEmit_UF'],
                'telefone' => $nfeConfigsEmUso['telefone'],
                'email' => ''
            ];

            $daevento = new Daevento($xml, $dadosEmitente);
            $daevento->debugMode(true);
            $daevento->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');

            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            $response = file_get_contents($nfeConfigsEmUso['logo_fiscal'] ?? $_SERVER['CROSIER_LOGO'], false, stream_context_create($arrContextOptions));

            $logo = 'data://text/plain;base64,' . base64_encode($response);
            // $daevento->monta($logo);
            $pdf = $daevento->render($logo);
            header('Content-Type: application/pdf');
            echo $pdf;
        } catch (\Throwable $e) {
            echo 'Ocorreu um erro durante o processamento :' . $e->getMessage();
        }
    }

    
    /**
     *
     * @Route("/fis/notaFiscal/emitidas/imprimir/{notaFiscal}", name="fis_notaFiscal_emitidas_imprimir")
     * @param NotaFiscal $notaFiscal
     * @return void
     */
    public function imprimir(NotaFiscal $notaFiscal): void
    {
        try {
            $pdf = $this->notaFiscalBusiness->gerarPDF($notaFiscal);
            header('Content-Type: application/pdf');
            if (!$pdf) {
                throw new \RuntimeException('Erro ao gerar PDF');
            }
            echo $pdf;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Ocorreu um erro durante o processamento :' . $e->getMessage());
        }
    }

    /**
     *
     * @Route("/fis/notaFiscal/emitidas/imprimirCartaCorrecao/{cartaCorrecao}", name="fis_notaFiscal_emitidas_imprimirCartaCorrecao")
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     * @return void
     */
    public function imprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): void
    {
        try {
            $xml = $cartaCorrecao->getMsgRetorno();

            $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsByCNPJ($cartaCorrecao->notaFiscal->documentoEmitente);

            $dadosEmitente = [
                'razao' => $nfeConfigsEmUso['razaosocial'],
                'logradouro' => $nfeConfigsEmUso['enderEmit_xLgr'],
                'numero' => $nfeConfigsEmUso['enderEmit_nro'],
                'complemento' => '',
                'bairro' => $nfeConfigsEmUso['enderEmit_xBairro'],
                'CEP' => $nfeConfigsEmUso['enderEmit_cep'],
                'municipio' => $nfeConfigsEmUso['enderEmit_xMun'],
                'UF' => $nfeConfigsEmUso['enderEmit_UF'],
                'telefone' => $nfeConfigsEmUso['telefone'],
                'email' => ''
            ];

            $daevento = new Daevento($xml, $dadosEmitente);
            $daevento->debugMode(true);
            $daevento->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');

            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            $response = file_get_contents($nfeConfigsEmUso['logo_fiscal'] ?? $_SERVER['CROSIER_LOGO'], false, stream_context_create($arrContextOptions));

            $logo = 'data://text/plain;base64,' . base64_encode($response);
            // $daevento->monta($logo);
            $pdf = $daevento->render($logo);
            header('Content-Type: application/pdf');
            echo $pdf;
        } catch (\InvalidArgumentException $e) {
            echo 'Ocorreu um erro durante o processamento :' . $e->getMessage();
        }
    }
    

    /**
     * @Route("/fis/notaFiscal/emitidas/copiarNotaFiscalItem/{notaFiscalItem}", name="fis_notaFiscal_emitidas_copiarNotaFiscalItem")
     * @param SessionInterface $session
     * @param NotaFiscalItem $notaFiscalItem
     * @return JsonResponse
     */
    public function copiarNotaFiscalItem(SessionInterface $session, NotaFiscalItem $notaFiscalItem): JsonResponse
    {
        $session->set('fis_notaFiscal_emitidas_copiarNotaFiscalItem', $notaFiscalItem->getId());
        $this->addFlash('success', 'Item copiado');
        return new JsonResponse(['result' => 'OK']);
    }

    /**
     * @Route("/fis/notaFiscal/emitidas/colarNotaFiscalItem/{notaFiscal}", name="fis_notaFiscal_emitidas_colarNotaFiscalItem")
     * @param SessionInterface $session
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     * @throws ViewException
     */
    public function colarNotaFiscalItem(SessionInterface $session, NotaFiscal $notaFiscal): RedirectResponse
    {
        $notaFiscalItemId = $session->get('fis_notaFiscal_emitidas_copiarNotaFiscalItem');
        /** @var NotaFiscalItem $notaFiscalItem */
        $notaFiscalItem = $this->getDoctrine()->getRepository(NotaFiscalItem::class)->find($notaFiscalItemId);
        $this->notaFiscalBusiness->colarItem($notaFiscal, $notaFiscalItem);
        return $this->redirectToRoute('fis_notaFiscal_emitidas_form', ['id' => $notaFiscal->getId(), '_fragment' => 'itens']);
    }

    /**
     * @Route("/fis/notaFiscal/emitidas/consultarCNPJ", name="fis_notaFiscal_emitidas_consultarCNPJ")
     * @param Request $request
     * @return JsonResponse
     */
    public function consultarCNPJ(Request $request): JsonResponse
    {
        try {
            $cnpj = preg_replace("/[^0-9]/", '', $request->get('cnpj'));
            if (!ValidaCPFCNPJ::valida($cnpj)) {
                return new JsonResponse(['result' => 'ERRO', 'msg' => 'CPF/CNPJ inválido']);
            }
            $uf = $request->get('uf');
            $r = $this->notaFiscalBusiness->consultarCNPJ($cnpj, $uf);
            if (isset($r['dados'])) {
                return new JsonResponse(['result' => 'OK', 'dados' => $r['dados']]);
            } else {
                return new JsonResponse(['result' => 'ERRO', 'msg' => $r['xMotivo']]);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'ERRO', 'msg' => 'Erro ao consultar CNPJ']);
        }
    }


    /**
     *
     * @Route("/fis/notaFiscal/emitidas/downloadXML/{nf}", name="fis_notaFiscal_emitidas_downloadXML", requirements={"nf"="\d+"})
     *
     * @param NotaFiscal $nf
     * @return Response
     * @throws ViewException
     */
    public function downloadXML(Request $request, NotaFiscal $nf): Response
    {
        $filename = $nf->chaveAcesso . '-' . strtolower($nf->tipoNotaFiscal) . '.xml';

        if (!$nf->getXMLDecoded() || $request->get("regerar")) {
            $nf = $this->spedNFeBusiness->gerarXML($nf);
        }
        if (!$nf->getXMLDecoded()) {
            throw new ViewException('XMLDecoded n/d');
        }
        
        if ($nf->getXMLDecoded()->getName() !== 'nfeProc' && !$request->get('naoAssinar')) {
            $nf = $this->spedNFeBusiness->gerarXML($nf);
            $tools = $this->nfeUtils->getToolsEmUso();
            $tools->model($nf->tipoNotaFiscal === 'NFE' ? '55' : '65');
            $this->notaFiscalBusiness->handleIdeFields($nf);
            $fileContent = $tools->signNFe($nf->getXmlNota());
        } else {
            $fileContent = $nf->getXMLDecodedAsString();
        }

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
     * @Route("/fis/notaFiscal/emitidas/downloadXMLs", name="fis_notaFiscal_emitidas_downloadXMLs")
     * @throws ViewException
     */
    public function downloadXMLsMesAno(Request $request)
    {
        $documentoEmitente = $request->get('documentoEmitente');
        if (!$documentoEmitente) {
            throw new ViewException('documentoEmitente n/d');
        }
        $dtEmissao = $request->get('dtEmissao');
        $dtEmissaoDe = DateTimeUtils::parseDateStr($dtEmissao['after']);
        $dtEmissaoAte = DateTimeUtils::parseDateStr($dtEmissao['before']);
        if (!$dtEmissaoDe || !$dtEmissaoAte) {
            return CrosierApiResponse::error(null, false,'Período n/d');
        }
        
        if (DateTimeUtils::diffInDias($dtEmissaoAte, $dtEmissaoDe) > 31) {
            return CrosierApiResponse::error(null, false,'Período não pode ser superior a 31 dias');
        }

        $zip = new ZipArchive();
        $filename = $documentoEmitente . '_' . $dtEmissaoDe->format('Ymd') . '-' . $dtEmissaoAte->format('Ymd');
        $arquivo = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . $filename . '.zip';
        @unlink($arquivo);

        if ($zip->open($arquivo, ZipArchive::CREATE) !== TRUE) {
            return CrosierApiResponse::error(null, false, 'Erro ao gerar arquivo zip');
        }
        
        /** @var NotaFiscalRepository $repoNotasFiscais */
        $repoNotasFiscais = $this->getDoctrine()->getRepository(NotaFiscal::class);

        $nfes = $repoNotasFiscais->findByFiltersSimpl([
            ['dtEmissao', 'BETWEEN_DATE', [$dtEmissaoDe, $dtEmissaoAte] ],
            ['documentoEmitente', 'EQ', $documentoEmitente],
            ['numero', 'IS_NOT_EMPTY'],
            ['ambiente', 'EQ', 'PROD']
        ], ['serie' => 'ASC', 'numero' => 'ASC'], 0, -1);

        // homologadas
        $nfes100 = [];
        // canceladas
        $nfes101 = [];

        // homologadas
        $nfces100 = [];
        // canceladas
        $nfces101 = [];

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
                $tools->model($nf->tipoNotaFiscal === 'NFE' ? '55' : '65');
                $fileContent = $tools->signNFe($nf->getXmlNota());
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
                    $nfes100[] = $nf;
                } else if ((int)$nf->cStat === 101 || ((int)$nf->cStat === 135 && (int)$nf->cStatLote === 101)) {
                    $problemas[] = 'NFE ' . $nf->numero . ' (Chave: ' . $nf->chaveAcesso . ') CANCELADA';
                    $nfes101[] = $nf;
                } else {
                    $msg = 'NFE ' . $nf->numero . ' (Chave: ' . $nf->chaveAcesso . ') com status diferente de 100 ou 101. Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
            } else if ($nf->tipoNotaFiscal === 'NFCE') {
                if ((int)$nf->cStat === 100) {
                    $nfces100[] = $nf;
                } else if ((int)$nf->cStat === 101) {
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
     *
     * @Route("/fis/notaFiscal/emitidas/inutilizaNumeracao", name="fis_notaFiscal_emitidas_inutilizaNumeracao")
     *
     * @param Request $request
     * @return Response
     */
    public function inutilizaNumeracao(Request $request): Response
    {
        $tipo = $request->get('tipo');
        $serie = $request->get('serie');
        $numero = $request->get('numero');

        $r = $this->spedNFeBusiness->inutilizaNumeracao($tipo, $serie, $numero);

        return new Response('<pre>' . print_r($r, true));

    }

    /**
     *
     * @Route("/fis/notaFiscal/emitidas/imprimirDANFCE", name="fis_notaFiscal_emitidas_imprimirDANFCE")
     * @param Request $request
     * @return Response
     *
     * @throws ViewException
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     */
    public function imprimirDANFCE(Request $request): Response
    {
        gc_collect_cycles();
        gc_disable();

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('enable_remote', true);
        $pdfOptions->set('isPhpEnabled', true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);


        $nfId = $request->get('nfId');
        if (!$nfId) {
            throw new \RuntimeException('nfId não informado');
        }
        /** @var NotaFiscal $nf */
        $nf = $this->getDoctrine()->getRepository(NotaFiscal::class)->find($nfId);
        if (!$nf) {
            throw new \RuntimeException('nf não encontrada');
        }

        $configs = $this->nfeUtils->getNFeConfigsByCNPJ($nf->documentoEmitente);

        $primeiros = $nf->chaveAcesso . '|2|1|' . (int)$configs['CSCid_prod'];
        $codigoHash = sha1($primeiros . $configs['CSC_prod']);
        $qrcode = 'http://www.fazenda.pr.gov.br/nfce/qrcode?p=' . $primeiros . '|' . $codigoHash;

        $chaveAcesso =
            substr($nf->chaveAcesso, 0, 4) . ' ' .
            substr($nf->chaveAcesso, 4, 4) . ' ' .
            substr($nf->chaveAcesso, 8, 4) . ' ' .
            substr($nf->chaveAcesso, 12, 4) . ' ' .
            substr($nf->chaveAcesso, 16, 4) . ' ' .
            substr($nf->chaveAcesso, 24, 4) . ' ' .
            substr($nf->chaveAcesso, 28, 4) . ' ' .
            substr($nf->chaveAcesso, 32, 4) . ' ' .
            substr($nf->chaveAcesso, 36, 4) . ' ' .
            substr($nf->chaveAcesso, 40, 4);

        $params = [
            'xml' => $nf->getXMLDecoded(),
            'cancelada' => (int)$nf->cStat === 135,
            'chaveAcesso' => $chaveAcesso,
            'qrcode' => $qrcode
        ];

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('/Fiscal/pdf/danfce.html.twig', $params);
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);


        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream('danfce.pdf', [
            'Attachment' => false
        ]);

        gc_collect_cycles();
        gc_enable();

    }


    /**
     *
     * @Route("/fis/notaFiscal/emitidas/imprimirDANFCEhtml", name="fis_notaFiscal_emitidas_imprimirDANFCEhtml")
     * @return Response
     *
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     * @throws \Exception
     */
    public function imprimirDANFCEhtml(Request $request): Response
    {
        $nfId = $request->get('nfId');
        if (!$nfId) {
            throw new \RuntimeException('nfId não informado');
        }
        /** @var NotaFiscal $nf */
        $nf = $this->getDoctrine()->getRepository(NotaFiscal::class)->find($nfId);
        if (!$nf) {
            throw new \RuntimeException('nf não encontrada');
        }

        $configs = $this->nfeUtils->getNFeConfigsByCNPJ($nf->documentoEmitente);

        $primeiros = $nf->chaveAcesso . '|2|1|' . (int)$configs['CSCid_prod'];
        $codigoHash = sha1($primeiros . $configs['CSC_prod']);
        $qrcode = 'http://www.fazenda.pr.gov.br/nfce/qrcode?p=' . $primeiros . '|' . $codigoHash;

        $nf->chaveAcesso = 
            substr($nf->chaveAcesso, 0, 4) . ' ' .
            substr($nf->chaveAcesso, 4, 4) . ' ' .
            substr($nf->chaveAcesso, 8, 4) . ' ' .
            substr($nf->chaveAcesso, 12, 4) . ' ' .
            substr($nf->chaveAcesso, 16, 4) . ' ' .
            substr($nf->chaveAcesso, 24, 4) . ' ' .
            substr($nf->chaveAcesso, 28, 4) . ' ' .
            substr($nf->chaveAcesso, 32, 4) . ' ' .
            substr($nf->chaveAcesso, 36, 4) . ' ' .
            substr($nf->chaveAcesso, 40, 4)
        ;

        $params = [
            'nf' => $nf,
            'cnpj' => $configs['cnpj'],
            'razaoSocial' => $configs['razaosocial'],
            'enderecoCompleto' => $configs['enderecoCompleto'],
            'qrcode' => $qrcode
        ];

        return $this->render('/Fiscal/pdf/danfce.html.twig', $params);


    }

    /**
     * @Route("/fis/notaFiscal/emitidas/consultaRecibo/{notaFiscal}", name="fis_notaFiscal_emitidas_consultaRecibo")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     * @throws ViewException
     */
    public function consultarRecibo(NotaFiscal $notaFiscal): Response
    {
        $this->spedNFeBusiness->consultaRecibo($notaFiscal);
        $xml = $notaFiscal->getXMLDecoded();
        $r = [];
        $r[] = 'cStat: ' . $xml->cStat;
        $r[] = 'xMotivo: ' . $xml->xMotivo;
        $r[] = 'dhRecbto: ' . $xml->dhRecbto;
        $r[] = 'protNFe.chNFe: ' . $xml->protNFe->infProt->chNFe;
        $r[] = 'protNFe.dhRecbto: ' . $xml->protNFe->infProt->dhRecbto;
        $r[] = 'protNFe.nProt: ' . $xml->protNFe->infProt->nProt;
        $r[] = 'protNFe.digVal: ' . $xml->protNFe->infProt->digVal;
        $r[] = 'protNFe.cStat: ' . $xml->protNFe->infProt->cStat;
        $r[] = 'protNFe.xMotivo: ' . $xml->protNFe->infProt->xMotivo;

        return new Response(implode('<br>', $r));
    }


}
