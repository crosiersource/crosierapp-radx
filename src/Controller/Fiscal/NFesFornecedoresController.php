<?php

namespace App\Controller\Fiscal;

use App\Form\Fiscal\NotaFiscalType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /** @var NotaFiscalEntityHandler $entityHandler  */
    protected $entityHandler;

    private NFeUtils $nfeUtils;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private SpedNFeBusiness $spedNFeBusiness;

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
     * @required
     * @param DistDFeBusiness $distDFeBusiness
     */
    public function setDistDFeBusiness(DistDFeBusiness $distDFeBusiness): void
    {
        $this->distDFeBusiness = $distDFeBusiness;
    }


    /**
     *
     * @Route("/fis/nfesFornecedores/form/{id}", name="nfesFornecedores_form", requirements={"id"="\d+"})
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function form(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getXMLDecoded() && $notaFiscal->getXMLDecoded()->getName() === 'resNFe') {
            $notaFiscal->resumo = true;
            $this->entityHandler->save($notaFiscal);
            return $this->redirectToRoute('nfesFornecedores_formResumo', ['id' => $notaFiscal->getId()]);
        }
        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        return $this->doRender('/Fiscal/nfeFornecedores/form.html.twig', [
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal
        ]);
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/formResumo/{id}", name="nfesFornecedores_formResumo", requirements={"id"="\d+"})
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function formResumo(NotaFiscal $notaFiscal)
    {
        return $this->doRender('/Fiscal/nfeFornecedores/formResumo.html.twig', ['notaFiscal' => $notaFiscal]);
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
    public function list(Request $request): Response
    {
        $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();
        $empresa = StringUtils::mascararCnpjCpf($nfeConfigs['cnpj']) . ' - ' . $nfeConfigs['razaosocial'];
        $params =
            [
                'listView' => 'Fiscal/nfeFornecedores/nfesFornecedoresList.html.twig',
                'listRoute' => 'nfesFornecedores_list',
                'listRouteAjax' => 'nfesFornecedores_datatablesJsList',
                'listPageTitle' => 'NFe Entrada',
                'listId' => 'nfesFornecedoresList',
                'page_subTitle' => $empresa
            ];


        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/datatablesJsList/", name="nfesFornecedores_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     */
    public function datatablesJsList(Request $request): Response
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
        $filename = $nf->chaveAcesso . '-' . strtolower($nf->tipoNotaFiscal) . '.xml';


        try {
            $fileContent = gzdecode(base64_decode($nf->getXmlNota()));
        } catch (\Exception $e) {
            $fileContent = $nf->getXmlNota();
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
     *
     * @Route("/fis/nfesFornecedores/manifestar/{nf}", name="nfesFornecedores_manifestar")
     *
     * @param Request $request
     * @param NotaFiscal $nf
     * @return Response
     */
    public function manifestar(Request $request, NotaFiscal $nf): Response
    {
        $codManifest = $request->get('codManifest') ?? '210210';
        try {
            $this->spedNFeBusiness->manifestar($nf, $codManifest);
            $this->addFlash('success', 'NF manifestada com sucesso.');
        } catch (\Exception $e) {
            $this->logger->error('Erro ao manifestar (nf.id = ' . $nf->getId() . ', codManifest = ' . $codManifest . ')');
            $this->addFlash('error', 'Erro ao manifestar a NF');
        }
        return $this->redirectToRoute('nfesFornecedores_list');
    }


    


    /**
     * @Route("/fis/nfesFornecedores/gerarFatura/{notaFiscal}", name="fis_nfesFornecedores_gerarFatura", requirements={"notaFiscal"="\d+"})
     * 
     * @param Request $request
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function gerarFatura(Request $request, NotaFiscal $notaFiscal)
    {
        try {
            if (!$this->isCsrfTokenValid('fis_nfesFornecedores_gerarFatura', $request->get('token'))) {
                throw new ViewException('Token inválido');
            }
            $this->notaFiscalBusiness->gerarFatura($notaFiscal);
            $this->addFlash('success', 'Fatura gerada com sucesso');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao gerar fatura');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('nfesFornecedores_form', ['id' => $notaFiscal->getId(), '_fragment' => 'duplicatas']);
    }

    /**
     *
     * @Route("/fis/nfesFornecedores/reparseDownloadedXML/{notaFiscal}", name="fis_nfesFornecedores_reparseDownloadedXML")
     *
     * @param NotaFiscal $notaFiscal
     * @return Response
     */
    public function reparseDownloadedXML(NotaFiscal $notaFiscal): ?Response
    {
        try {
            $this->distDFeBusiness->nfeProc2NotaFiscal($notaFiscal->documentoDestinatario, $notaFiscal->getXMLDecoded(), $notaFiscal);
            $this->addFlash('success', 'XML reprocessado com sucesso');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao reprocessar XML');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('nfesFornecedores_form', ['id' => $notaFiscal->getId()]);
    }


}