<?php

namespace App\Controller\Fiscal;

use App\Business\Fiscal\DistDFeBusiness;
use App\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\DistDFe;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\DistDFeEntityHandler;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    private SpedNFeBusiness $spedNFeBusiness;

    private NFeUtils $nfeUtils;

    private DistDFeBusiness $distDFeBusiness;

    private ParameterBagInterface $params;

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
     * @param ParameterBagInterface $params
     * @required
     */
    public function setParams(ParameterBagInterface $params): void
    {
        $this->params = $params;
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
     * @return Response
     */
    public function obterDistDFes(Request $request, int $primeiroNSU = null): Response
    {
        try {
            $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            if ($primeiroNSU) {
                $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpjEmUso);
            } else {
                $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpjEmUso);
            }
            $this->addFlash('info', $q ? $q . ' DistDFe(s) obtidos' : 'Nenhum DistDFe obtido');
            $this->distDFeBusiness->processarDistDFesObtidos();
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $route = $request->get('redirectToRoute') ?? 'distDFe_list';
        return $this->redirectToRoute($route);
    }

    /**
     *
     * @Route("/fis/distDFe/obterDistDFesDeNSUsPulados/", name="distDFe_obterDistDFesDeNSUsPulados")
     *
     * @return Response
     */
    public function obterDistDFesDeNSUsPulados(): Response
    {
        try {
            $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            $q = $this->distDFeBusiness->obterDistDFesDeNSUsPulados($cnpjEmUso);
            return new Response($q . ' DFe\'s obtidos');
        } catch (ViewException $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     *
     * @Route("/fis/distDFe/obterDFePorNSU/{nsu}", name="distDFe_obterDFePorNSU", requirements={"nsu"="\d+"})
     *
     * @param int $nsu
     * @return Response
     */
    public function obterDFePorNSU(int $nsu): Response
    {
        try {
            $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
            $r = $this->distDFeBusiness->obterDistDFeByNSU($nsu, $cnpjEmUso);
            if ($r) {
                $this->addFlash('success', 'DFe obtido');
            } else {
                $this->addFlash('warn', 'DFe já existente');
            }
            return $this->redirectToRoute('distDFe_list');
        } catch (ViewException $e) {
            return new Response($e->getMessage());
        }
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