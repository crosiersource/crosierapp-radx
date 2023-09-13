<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalCartaCorrecao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalCartaCorrecaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalItemEntityHandler;
use Dompdf\Dompdf;
use Dompdf\Options;
use NFePHP\DA\NFe\Daevento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class ImpressoesController extends FormListController
{

    /** @var NotaFiscalEntityHandler */
    protected $entityHandler;

    /** @required */
    public NotaFiscalBusiness $notaFiscalBusiness;

    /** @required */
    public SpedNFeBusiness $spedNFeBusiness;

    /** @required */
    public NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler;

    /** @required */
    public NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler;

    /** @required */
    public NFeUtils $nfeUtils;


    /**
     * @Route("/fis/emissaonfe/imprimirCancelamento/{notaFiscal}", name="fis_emissaonfe_imprimirCancelamento")
     */
    public function imprimirCancelamento(NotaFiscal $notaFiscal): void
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
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
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
     * @Route("/fis/emissaonfe/imprimir/{notaFiscal}", name="fis_emissaonfe_imprimir")
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
     * @Route("/fis/emissaonfe/imprimirCartaCorrecao/{cartaCorrecao}", name="fis_emissaonfe_imprimirCartaCorrecao")
     */
    public function imprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): void
    {
        try {
            $xml = $cartaCorrecao->msgRetorno;

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


            $arrContextOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
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
     * @Route("/fis/emissaonfe/imprimirDANFCE", name="fis_emissaonfe_imprimirDANFCE")
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
     * @Route("/fis/emissaonfe/imprimirDANFCEhtml", name="fis_emissaonfe_imprimirDANFCEhtml")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
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
            substr($nf->chaveAcesso, 40, 4);

        $params = [
            'nf' => $nf,
            'cnpj' => $configs['cnpj'],
            'razaoSocial' => $configs['razaosocial'],
            'enderecoCompleto' => $configs['enderecoCompleto'],
            'qrcode' => $qrcode
        ];

        return $this->render('/Fiscal/pdf/danfce.html.twig', $params);
    }


}
