<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\DistDFe;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\DistDFeEntityHandler;
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
class DistDFeController extends FormListController
{

    /** @var DistDFeEntityHandler */
    protected $entityHandler;

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
     * @required
     * @param DistDFeEntityHandler $entityHandler
     * @return DistDFeController
     */
    public function setEntityHandler(DistDFeEntityHandler $entityHandler): DistDFeController
    {
        $this->entityHandler = $entityHandler;
        return $this;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData('documento', 'EQ', 'documento', $params),
        ];
    }

    /**
     *
     * @Route("/fis/distDFe/list/", name="distDFe_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function list(Request $request): Response
    {
        $params =
            [
                'listView' => 'Fiscal/distDFeList.html.twig',
                'listRoute' => 'distDFe_list',
                'listRouteAjax' => 'distDFe_datatablesJsList',
                'listPageTitle' => 'DistDFes',
                'listId' => 'distDFeList',
            ];
        $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsEmUso();
        $params['page_subTitle'] = StringUtils::mascararCnpjCpf($nfeConfigsEmUso['cnpj']) . ' - ' . $nfeConfigsEmUso['razaosocial'];

        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fis/distDFe/datatablesJsList/", name="distDFe_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     */
    public function datatablesJsList(Request $request): Response
    {
        $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
        return $this->doDatatablesJsList($request, ['filter' => ['documento' => $cnpjEmUso]]);
    }


    /**
     *
     * @Route("/fis/distDFe/obterDistDFes/{primeiroNSU}", name="distDFe_obterDistDFes")
     *
     * @param Request $request
     * @param int|null $primeiroNSU
     * @return JsonResponse
     */
    public function obterDistDFes(Request $request, int $primeiroNSU = null): JsonResponse
    {
        try {
            $cnpjEmUso = $request->get('documentoDestinatario') ?? $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            if ($primeiroNSU) {
                $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpjEmUso);
            } else {
                $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpjEmUso);
            }

            $this->distDFeBusiness->processarDistDFesObtidos();
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
     *
     * @Route("/fis/distDFe/obterDistDFesDeNSUsPulados/{cnpj}", name="distDFe_obterDistDFesDeNSUsPulados")
     *
     * @return Response
     */
    public function obterDistDFesDeNSUsPulados(string $cnpj): Response
    {
        try {
            $q = $this->distDFeBusiness->obterDistDFesDeNSUsPulados($cnpj);
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
     *
     * @Route("/fis/distDFe/verificarNSUsNaSefaz/{cnpj}", name="fis_distDFe_verificarNSUsNaSefaz")
     *
     * @param int $nsu
     * @return Response
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
            return new Response($e->getMessage());
        }
    }

    /**
     *
     * @Route("/fis/distDFe/obterDFePorNSU/{cnpj}/{nsu}", name="distDFe_obterDFePorNSU")
     *
     * @param int $nsu
     * @return Response
     */
    public function obterDFePorNSU(string $cnpj, int $nsu): JsonResponse
    {
        return $this->distDFeBusiness->obterDistDFeByNSU($nsu, $cnpj);
    }


    /**
     *
     * @Route("/fis/distDFe/processarDistDFesObtidos", name="distDFe_processarDistDFesObtidos")
     *
     * @return Response
     */
    public function processarDistDFesObtidos(): ?Response
    {
        try {
            $this->distDFeBusiness->processarDistDFesObtidos();
            $this->addFlash('info', 'DistDFe(s) processados');
            return $this->redirectToRoute('distDFe_list');
        } catch (ViewException $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     *
     * @Route("/fis/distDFe/reprocessarDistDFe/{distDFe}", name="distDFe_reprocessarDistDFe", requirements={"distDFe"="\d+"})
     *
     * @param DistDFe $distDFe
     * @return Response
     */
    public function reprocessarDistDFe(DistDFe $distDFe): ?Response
    {
        try {
            $this->distDFeBusiness->reprocessarDistDFe($distDFe);
            $this->addFlash('info', 'DistDFe reprocessado');
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('distDFe_list');
    }


    /**
     *
     * @Route("/fis/distDFe/downloadXML/{distDFe}", name="distDFe_downloadXML", requirements={"distDFe"="\d+"})
     *
     * @param DistDFe $distDFe
     * @return Response
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


}