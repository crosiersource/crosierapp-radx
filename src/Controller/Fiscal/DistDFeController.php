<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\DistDFe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DistDFeController
 *
 * @author Carlos Eduardo Pauluk
 */
class DistDFeController extends AbstractController
{

    private NFeUtils $nfeUtils;

    private DistDFeBusiness $distDFeBusiness;


    /**
     * @required
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }

    /**
     * @required
     * @param DistDFeBusiness $distDFeBusiness
     */
    public function setDistDFeBusiness(DistDFeBusiness $distDFeBusiness): void
    {
        $this->distDFeBusiness = $distDFeBusiness;
    }

    /**
     * @Route("/fis/distDFe/obterDistDFes/{primeiroNSU}", name="distDFe_obterDistDFes")
     */
    public function obterDistDFes(Request $request, int $primeiroNSU = null): JsonResponse
    {
        try {
            $ctes = (bool)($request->get('buscarParaCtes') ?? false);
            $cnpjEmUso = $request->get('documentoDestinatario') ?? $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            if ($primeiroNSU) {
                $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpjEmUso, $ctes);
            } else {
                $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpjEmUso, $ctes);
            }

            $this->distDFeBusiness->processarDistDFesObtidos($cnpjEmUso);
            return new JsonResponse([
                'RESULT' => 'OK',
                'MSG' => $q . ' registro(s) obtido(s)',
            ]);
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            return new JsonResponse([
                'RESULT' => 'ERR',
                'MSG' => $msg
            ]);
        }
    }

    /**
     * @Route("/fis/distDFe/obterDistDFesDeNSUsPulados/{cnpj}", name="distDFe_obterDistDFesDeNSUsPulados")
     */
    public function obterDistDFesDeNSUsPulados(?string $cnpj = null): Response
    {
        try {
            $cnpjEmUso = $cnpj ?? $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            $q = $this->distDFeBusiness->obterDistDFesDeNSUsPulados($cnpjEmUso);
            return new Response($q . ' DFe\'s obtidos');
        } catch (ViewException $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * @Route("/fis/distDFe/verificarNSUsPulados/{cnpj}", name="fis_distDFe_verificarNSUsPulados")
     */
    public function verificarNSUsPulados(string $cnpj): JsonResponse
    {
        $nsusPulados = $this->distDFeBusiness->getNSUsPulados($cnpj);
        return new JsonResponse($nsusPulados);
    }

    /**
     * @Route("/fis/distDFe/verificarNSUsNaSefaz/{cnpj}", name="fis_distDFe_verificarNSUsNaSefaz")
     */
    public function verificarNSUsNaSefaz(string $cnpj): JsonResponse
    {
        try {
            $r = $this->distDFeBusiness->verificarNSUsNaSefaz($cnpj);

            $rBase = $this->getDoctrine()->getConnection()
                ->fetchAssociative(
                    'select min(nsu), max(nsu) from fis_distdfe where documento = :cnpj order by nsu',
                    ['cnpj' => $cnpj]);

            return new JsonResponse(
                [
                    'dadosSefaz' => $r,
                    'dadosBase' => $rBase,
                ]);
        } catch (ViewException $e) {
            return CrosierApiResponse::viewExceptionError($e);
        }
    }

    /**
     * @Route("/fis/distDFe/obterDFePorNSU/{cnpj}/{nsu}", name="distDFe_obterDFePorNSU")
     */
    public function obterDFePorNSU(string $cnpj, int $nsu): JsonResponse
    {
        return $this->distDFeBusiness->obterDistDFeByNSU($nsu, $cnpj);
    }


    /**
     * @Route("/fis/distDFe/processarDistDFesObtidos", name="distDFe_processarDistDFesObtidos")
     */
    public function processarDistDFesObtidos(Request $request): ?Response
    {
        try {
            $cnpjEmUso = $request->get('documentoDestinatario') ?? $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            $this->distDFeBusiness->processarDistDFesObtidos($cnpjEmUso);
            $this->addFlash('info', 'DistDFe(s) processados');
            return $this->redirectToRoute('distDFe_list');
        } catch (ViewException $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * @Route("/fis/distDFe/reprocessarDistDFe/{distDFe}", name="distDFe_reprocessarDistDFe", requirements={"distDFe"="\d+"})
     */
    public function reprocessarDistDFe(DistDFe $distDFe): ?Response
    {
        try {
            $this->distDFeBusiness->reprocessarDistDFe($distDFe);
            $this->addFlash('info', 'DistDFe reprocessado');
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirect('/v/fis/notaFiscal/distdfe/list');
    }


    /**
     * @Route("/fis/distDFe/downloadXML/{distDFe}", name="distDFe_downloadXML", requirements={"distDFe"="\d+"})
     */
    public function downloadXML(DistDFe $distDFe): Response
    {
        // Provide a name for your file with extension
        $filename = $distDFe->chave . '.xml';

        // The dinamically created content of the file
        $fileContent = gzdecode(base64_decode($distDFe->xml));

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
     * @Route("/fis/distDFe/res2proc", name="fis_distDFe_res2proc")
     */
    public function res2proc(): JsonResponse
    {
        $this->distDFeBusiness->res2proc();
        return CrosierApiResponse::success();
    }


}