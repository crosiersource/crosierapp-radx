<?php

namespace App\Controller\Fiscal\API;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Repository\Fiscal\NotaFiscalRepository;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

/**
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalController extends BaseController
{

    /**
     * @param SyslogBusiness $syslog
     */
    public function setSyslog(SyslogBusiness $syslog): void
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
    }


    /**
     * @Route("/fis/notaFiscal/nfEntrada/list", name="fis_notaFiscal_nfEntrada_list")
     */
    public function nfEntradaList(): Response
    {
        $params = [
            'jsEntry' => 'Fiscal/NotaFiscal/nfEntradaList'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fis/notaFiscal/nfEntrada/manifestarEmLote", name="fis_notaFiscal_nfEntrada_manifestarEmLote")
     * @IsGranted("ROLE_FISCAL_ADMIN", statusCode=403)
     */
    public function manifestarEmLote(Request $request, SpedNFeBusiness $spedNFeBusiness): Response
    {
        /**
         * 210200 Confirmação da operação
         * 210210 Ciência da Operação
         * 210220 Desconhecimento da operação
         * 21040 Operação não realizada
         */
        $codManifest = $request->get('codManifest') ?? '210210';
        try {
            $rNfsIds = $request->get('nfsIds');
            $nfsIds = explode(',', $rNfsIds);
            $repoNotaFiscal = $this->getDoctrine()->getRepository(NotaFiscal::class);
            foreach ($nfsIds as $nfId) {
                $nf = $repoNotaFiscal->find($nfId);
                $spedNFeBusiness->manifestar($nf, $codManifest);
            }
            return new JsonResponse([
                'RESULT' => 'OK',
                'MSG' => 'Manifestação(ões) executada(s) com sucesso',
            ]);
        } catch (Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->syslog->err('manifestarEmLote', $msg);
            return new JsonResponse([
                'RESULT' => 'ERR',
                'MSG' => $msg
            ]);
        }
    }


    /**
     * @Route("/fis/notaFiscal/nfEntrada/downloadXMLs", name="fis_notaFiscal_nfEntrada_downloadXMLs")
     * @throws ViewException
     */
    public function downloadXMLsEmLote(Request $request, NFeUtils $nfeUtils): Response
    {
        $nfsIds = $request->get('nfsIds');

        $zip = new ZipArchive();
        $uuid = StringUtils::guidv4();
        
        $arquivo = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . '/' . $uuid . '.zip';
        @unlink($arquivo);

        if ($zip->open($arquivo, ZipArchive::CREATE) !== TRUE) {
            throw new RuntimeException('Não foi possível escrever o arquivo zip');
        }

        /** @var NotaFiscalRepository $repoNotasFiscais */
        $repoNotasFiscais = $this->getDoctrine()->getRepository(NotaFiscal::class);

        $nfs = $repoNotasFiscais->findByFiltersSimpl([
            ['id', 'IN', explode(',', $nfsIds)],
        ], null, 0, -1);

        /** @var NotaFiscal $nf */
        foreach ($nfs as $nf) {
            $nomeArquivo = $nf->chaveAcesso . '-' . strtolower($nf->tipoNotaFiscal) . '.xml';
            $zip->addFromString($nomeArquivo, $nf->getXMLDecodedAsString());
        }

        $zip->close();

        // Return a response with a specific content
        $response = new Response(file_get_contents($arquivo));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-length', filesize($arquivo));

        // Set the content disposition
        $response->headers->set('Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $uuid . '.zip'
            )
        );
        $files = glob($_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . '/*.zip'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }
        // Dispatch request
        return $response;
    }


}