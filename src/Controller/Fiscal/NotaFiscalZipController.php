<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotaFiscalZipController
 * @package App\Controller
 *
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalZipController extends BaseController
{

    /** @var NotaFiscalBusiness */
    private $notaFiscalBusiness;

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }


    /**
     *
     * @Route("/fis/notaFiscalZip/ini/", name="fis_notaFiscalZip_ini")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function ini(Request $request)
    {
        $mesano = ((new \DateTime())->modify('last month'))->format('Y-m');
        $params = ['mesano' => $mesano];
        return $this->doRender('/Fiscal/notaFiscalZip.html.twig', $params);
    }

    /**
     *
     * @Route("/fis/notaFiscalZip/processar/", name="fis_notaFiscalZip_processar")
     * @param Request $request
     * @return Response
     */
    public function processar(Request $request)
    {
        try {
            $mesano = $request->get('mesano');
            if (!$mesano) {
                throw new \Exception('Mês/Ano deve ser informado.');
            }
            $zip = $this->notaFiscalBusiness->criarZip($mesano);
            $response = new Response($zip);
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $mesano . '.zip'
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao gerar arquivo zip');
            return $this->redirectToRoute('fis_notaFiscalZip_ini');
        }
    }

}