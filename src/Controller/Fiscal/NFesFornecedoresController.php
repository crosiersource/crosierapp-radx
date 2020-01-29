<?php

namespace App\Controller\Fiscal;

use App\Business\Fiscal\NotaFiscalBusiness;
use App\Business\Fiscal\SpedNFeBusiness;
use App\Entity\Fiscal\NotaFiscal;
use App\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use App\Form\Fiscal\NotaFiscalType;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class NFesFornecedoresController extends FormListController
{

    /** @var NotaFiscalEntityHandler */
    protected $entityHandler;

    /** @var NFeUtils */
    private $nfeUtils;

    /** @var NotaFiscalBusiness */
    private $notaFiscalBusiness;

    /** @var SpedNFeBusiness */
    private $spedNFeBusiness;

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
     * @param NotaFiscalEntityHandler $entityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

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
     *
     * @Route("/fis/nfesFornecedores/form/{id}", name="nfesFornecedores_form", requirements={"id"="\d+"})
     * @param Request $request
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function form(Request $request, NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getXMLDecoded()->getName() === 'resNFe') {
            $notaFiscal->setResumo(true);
            $this->entityHandler->save($notaFiscal);
            return $this->redirectToRoute('nfesFornecedores_formResumo', ['id' => $notaFiscal->getId()]);
        }
        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $response = $this->doRender('/Fiscal/nfeFornecedores/form.html.twig', [
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal
        ]);
        return $response;
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/formResumo/{id}", name="nfesFornecedores_formResumo", requirements={"id"="\d+"})
     * @param Request $request
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function formResumo(Request $request, NotaFiscal $notaFiscal)
    {
        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $response = $this->doRender('/Fiscal/nfeFornecedores/formResumo.html.twig', [
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal
        ]);
        return $response;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData('documentoDestinatario', 'EQ', 'documentoDestinatario', $params),
            new FilterData('documentoEmitente', 'EQ', 'documentoEmitente', $params),
            new FilterData('tipoNotaFiscal', 'EQ', 'tipoNotaFiscal', $params),
            new FilterData('numero', 'EQ', 'numero', $params),
            new FilterData('dtEmissao', 'BETWEEN_DATE', 'dtEmissao', $params),
            new FilterData('xNomeEmitente', 'LIKE', 'xNome', $params)
        ];
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/list/", name="nfesFornecedores_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function list(Request $request)
    {
        $params =
            [
                'listView' => 'nfeFornecedores/nfesFornecedoresList.html.twig',
                'listRoute' => 'nfesFornecedores_list',
                'listRouteAjax' => 'nfesFornecedores_datatablesJsList',
                'listPageTitle' => 'NFe - Fornecedores',
                'listId' => 'nfesFornecedoresList',
            ];


        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/datatablesJsList/", name="nfesFornecedores_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function datatablesJsList(Request $request)
    {
        $rParams = $request->request->all();
        if (isset($rParams['formPesquisar'])) {
            parse_str($rParams['formPesquisar'], $formPesquisar);
        }
        $defaultFilters['filter']['documentoDestinatario'] = preg_replace("/[^0-9]/", '', $this->nfeUtils->getNFeConfigsEmUso()['cnpj']);
        // $defaultFilters['filter']['tipoNotaFiscal'] = 'NFE';
        return $this->doDatatablesJsList($request, $defaultFilters);
    }


    /**
     *
     * @Route("/fis/nfesFornecedores/downloadXML/{nf}", name="nfesFornecedores_downloadXML")
     *
     * @param NotaFiscal $nf
     * @return Response
     */
    public function downloadXML(NotaFiscal $nf): Response
    {
        // Provide a name for your file with extension
        $filename = $nf->getChaveAcesso() . '.xml';

        // The dinamically created content of the file
        $fileContent = gzdecode(base64_decode($nf->getXmlNota()));

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
     *
     * @Route("/fis/nfesFornecedores/manifestar/{nf}", name="nfesFornecedores_manifestar")
     *
     * @param Request $request
     * @param NotaFiscal $nf
     * @return Response
     */
    public function manifestar(Request $request, NotaFiscal $nf): Response
    {
        $codManifest = $request->get('codManifest');
        try {
            $this->spedNFeBusiness->manifestar($nf, $codManifest);
            $this->addFlash('success', 'NF manifestada com sucesso.');
        } catch (\Exception $e) {
            $this->logger->error('Erro ao manifestar (nf.id = ' . $nf->getId() . ', codManifest = ' . $codManifest . ')');
            $this->addFlash('error', 'Erro ao manifestar a NF');
        }
        return $this->redirectToRoute('nfesFornecedores_formResumo', ['id' => $nf->getId()]);
    }


}