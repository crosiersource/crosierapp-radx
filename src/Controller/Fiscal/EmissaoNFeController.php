<?php

namespace App\Controller\Fiscal;

use App\Form\Fiscal\NotaFiscalCartaCorrecaoType;
use App\Form\Fiscal\NotaFiscalItemType;
use App\Form\Fiscal\NotaFiscalType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\PessoaRepository;
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
use NFePHP\DA\NFe\Danfe;
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
 * Class EmissaoNFeController
 * @package App\Controller
 *
 * @author Carlos Eduardo Pauluk
 */
class EmissaoNFeController extends FormListController
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
     * @Route("/fis/emissaonfe/form/{id}", name="fis_emissaonfe_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param SessionInterface $session
     * @param Request $request
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function form(SessionInterface $session, Request $request, NotaFiscal $notaFiscal = null)
    {
        if (!$notaFiscal) {
            $notaFiscal = new NotaFiscal();
            $notaFiscal->setTipoNotaFiscal('NFE');
            $notaFiscal->setEntradaSaida('S');
            $notaFiscal->setDtEmissao(new \DateTime());
            $notaFiscal->setNaturezaOperacao('VENDA');
        }

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** @var NotaFiscal $notaFiscal */
                    if (!ValidaCPFCNPJ::valida($notaFiscal->getDocumentoDestinatario())) {
                        throw new ViewException('CPF/CNPJ inválido');
                    }
                    $notaFiscal->setTipoNotaFiscal('NFE');
                    $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscal($notaFiscal);
                    $this->addFlash('success', 'Registro salvo com sucesso!');
                    return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
                } catch (ViewException $e) {
                    $this->addFlash('error', $e->getMessage());
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erro ao salvar');
                }
            }
            // else {
            $this->logger->error('Erro ao cancelar nota');
            $errors = $form->getErrors(true, true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
                $this->logger->error($error->getMessage());
            }

        }

        $permiteFaturamento = $this->notaFiscalBusiness->permiteFaturamento($notaFiscal);
        $permiteSalvar = $this->notaFiscalBusiness->permiteSalvar($notaFiscal);
        $permiteReimpressao = $this->notaFiscalBusiness->permiteReimpressao($notaFiscal);
        $permiteReimpressaoCancelamento = $this->notaFiscalBusiness->permiteReimpressaoCancelamento($notaFiscal);
        $permiteCancelamento = $this->notaFiscalBusiness->permiteCancelamento($notaFiscal);
        $permiteCartaCorrecao = $this->notaFiscalBusiness->permiteCartaCorrecao($notaFiscal);

        return $this->doRender('/Fiscal/emissaoNFe/form.html.twig', [
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal,
            'permiteSalvar' => $permiteSalvar,
            'permiteFaturamento' => $permiteFaturamento,
            'permiteCancelamento' => $permiteCancelamento,
            'permiteReimpressao' => $permiteReimpressao,
            'permiteReimpressaoCancelamento' => $permiteReimpressaoCancelamento,
            'permiteCartaCorrecao' => $permiteCartaCorrecao,
            'itemCopiado' => $session->has('fis_emissaonfe_copiarNotaFiscalItem') ? $session->get('fis_emissaonfe_copiarNotaFiscalItem') : null,
        ]);
    }

    /**
     *
     * @Route("/fis/emissaonfe/faturar/{notaFiscal}", name="fis_emissaonfe_faturar", requirements={"notaFiscal"="\d+"})
     * @param Request $request
     * @param NotaFiscal|null $notaFiscal
     * @return RedirectResponse
     */
    public function faturar(Request $request, NotaFiscal $notaFiscal): RedirectResponse
    {
        try {
            $this->notaFiscalBusiness->faturarNFe($notaFiscal);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $route = $request->get('rtr') ?? 'fis_emissaonfe_form';
        return $this->redirectToRoute($route, ['id' => $notaFiscal->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaonfe/cancelarForm/{notaFiscal}", name="fis_emissaonfe_cancelarForm")
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function cancelarForm(Request $request, NotaFiscal $notaFiscal)
    {
        if (!$notaFiscal) {
            $this->addFlash('error', 'Nota Fiscal não encontrada!');
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
        }

        $dadosEmitente = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());
        if ($dadosEmitente['cnpj'] !== $notaFiscal->getDocumentoEmitente()) {
            $this->addFlash('error', 'Emitente da nota diferente do selecionado');
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
        }

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notaFiscal = $this->notaFiscalBusiness->cancelar($notaFiscal);
                return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
            } else {

                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $permiteCancelamento = $this->notaFiscalBusiness->permiteCancelamento($notaFiscal);
        $permiteReimpressaoCancelamento = $this->notaFiscalBusiness->permiteReimpressaoCancelamento($notaFiscal);

        $response = $this->doRender('/Fiscal/emissaoNFe/cancelarForm.html.twig', array(
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal,
            'permiteCancelamento' => $permiteCancelamento,
            'permiteReimpressaoCancelamento' => $permiteReimpressaoCancelamento,
            'dadosEmitente' => $dadosEmitente
        ));
        return $response;
    }

    /**
     *
     * @Route("/fis/emissaonfe/reimprimirCancelamento/{notaFiscal}", name="fis_emissaonfe_reimprimirCancelamento")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     */
    public function reimprimirCancelamento(NotaFiscal $notaFiscal): RedirectResponse
    {
        $this->notaFiscalBusiness->imprimirCancelamento($notaFiscal);
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaonfe/formCartaCorrecao/{notaFiscal}", name="fis_emissaonfe_formCartaCorrecao", defaults={"cartaCorrecao"=null}, requirements={"notaFiscal"="\d+"})
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @param NotaFiscalCartaCorrecao|null $cartaCorrecao
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function cartaCorrecaoForm(Request $request, NotaFiscal $notaFiscal, NotaFiscalCartaCorrecao $cartaCorrecao = null)
    {
        if (!$notaFiscal) {
            $this->addFlash('error', 'Nota Fiscal não encontrada!');
            return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()
            ]);
        }

        if (!$cartaCorrecao) {
            $cartaCorrecao = new NotaFiscalCartaCorrecao();
            $cartaCorrecao->setDtCartaCorrecao(new \DateTime());
        }

        $form = $this->createForm(NotaFiscalCartaCorrecaoType::class, $cartaCorrecao);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var NotaFiscalCartaCorrecao $cartaCorrecao */
                $cartaCorrecao = $form->getData();
                $cartaCorrecao->setNotaFiscal($notaFiscal);
                try {
                    $this->cartaCorrecaoEntityHandler->save($cartaCorrecao);
                    $notaFiscal = $this->notaFiscalBusiness->cartaCorrecao($cartaCorrecao);
                    return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId(), '_fragment' => 'cartasCorrecao']);
                } catch (ViewException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            // else
            $form->getErrors(true, true);
        }

        // Mantenho pois as regras pra cancelamento e pra carta de correção são as mesmas
        $permiteCancelamento = $this->notaFiscalBusiness->permiteCancelamento($notaFiscal);

        $response = $this->doRender('/Fiscal/emissaoNFe/formCartaCorrecao.html.twig', [
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal,
            'permiteCancelamento' => $permiteCancelamento
        ]);
        return $response;
    }


    /**
     *
     * @Route("/fis/emissaonfe/reenviarCartaCorrecao/{cartaCorrecao}", name="fis_emissaonfe_reenviarCartaCorrecao", requirements={"cartaCorrecao"="\d+"})
     * @param NotaFiscalCartaCorrecao|null $cartaCorrecao
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function reenviarCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao)
    {
        try {
            $this->notaFiscalBusiness->cartaCorrecao($cartaCorrecao);
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $cartaCorrecao->getNotaFiscal()->getId(), '_fragment' => 'cartasCorrecao']);
    }

    /**
     *
     * @Route("/fis/emissaonfe/consultarStatus/{notaFiscal}", name="fis_emissaonfe_consultarStatus")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     */
    public function consultarStatus(NotaFiscal $notaFiscal): RedirectResponse
    {
        try {
            $notaFiscal = $this->notaFiscalBusiness->consultarStatus($notaFiscal);
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao consultar status');
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaonfe/imprimir/{notaFiscal}", name="fis_emissaonfe_imprimir")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     */
    public function imprimir(NotaFiscal $notaFiscal): RedirectResponse
    {
        try {
            $xml = $notaFiscal->getXmlNota();
            $danfe = new Danfe($xml);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('');
            $danfe->monta();
            $pdf = $danfe->render();
            //o pdf porde ser exibido como view no browser
            //salvo em arquivo
            //ou setado para download forçado no browser
            //ou ainda gravado na base de dados
            header('Content-Type: application/pdf');
            echo $pdf;
        } catch (\InvalidArgumentException $e) {
            echo 'Ocorreu um erro durante o processamento :' . $e->getMessage();
        }
    }

    /**
     *
     * @Route("/fis/emissaonfe/reimprimirCartaCorrecao/{cartaCorrecao}", name="fis_emissaonfe_reimprimirCartaCorrecao")
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     * @return RedirectResponse
     */
    public function reimprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): RedirectResponse
    {
        $this->notaFiscalBusiness->imprimirCartaCorrecao($cartaCorrecao);
        $this->addFlash('success', 'Carta de Correção enviada para reimpressão!');
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $cartaCorrecao->getNotaFiscal()->getId(), '_fragment' => 'cartasCorrecao']);
    }

    /**
     *
     * @Route("/fis/emissaonfe/deleteItem/{item}", name="fis_emissaonfe_deleteItem", requirements={"item"="\d+"})
     * @param Request $request
     * @param NotaFiscalItem|null $item
     * @return RedirectResponse
     */
    public function deleteItem(Request $request, NotaFiscalItem $item)
    {
        $notaFiscalId = $item->getNotaFiscal()->getId();
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                $this->notaFiscalItemEntityHandler->delete($item);
                $this->addFlash('success', 'Item deletado com sucesso.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar item.');
            }
        }
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscalId, '_fragment' => 'itens']);
    }

    /**
     *
     * @Route("/fis/emissaonfe/formItem/{notaFiscal}/{item}", name="fis_emissaonfe_formItem", defaults={"item"=null}, requirements={"notaFiscal"="\d+","item"="\d+"})
     * @ParamConverter("item", class="CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalItem", options={"mapping": {"item": "id"}})
     *
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @param NotaFiscalItem|null $item
     * @return RedirectResponse|Response
     * @throws ViewException
     */
    public function formItem(Request $request, NotaFiscal $notaFiscal, NotaFiscalItem $item = null)
    {
        if (!$item) {
            $item = new NotaFiscalItem();
            $item->setNotaFiscal($notaFiscal);
            $item->setCsosn(103);
        }

        if (!$item->getOrdem()) {
            $item->setOrdem(0);
        }

        $form = $this->createForm(NotaFiscalItemType::class, $item);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = $form->getData();
                $this->notaFiscalItemEntityHandler->save($item);
                $this->addFlash('success', 'Registro salvo com sucesso!');
                $this->entityHandler->getDoctrine()->refresh($notaFiscal);
                return $this->redirectToRoute('fis_emissaonfe_form', [
                    'id' => $notaFiscal->getId(),
                    '_fragment' => 'itens'
                ]);
            }
            // else
            $form->getErrors(true, false);
        }
        return $this->doRender('/Fiscal/emissaoNFe/formItem.html.twig', array(
            'form' => $form->createView(),
            'notaFiscal' => $notaFiscal
        ));
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData('documentoEmitente', 'EQ', 'documentoEmitente', $params),
            new FilterData('serie', 'EQ', 'serie', $params),
            new FilterData('tipoNotaFiscal', 'EQ', 'tipoNotaFiscal', $params),
            new FilterData('numero', 'EQ', 'numero', $params),
            new FilterData('dtEmissao', 'BETWEEN_DATE', 'dtEmissao', $params),
            new FilterData('xNomeDestinatario', 'LIKE', 'xNomeDestinatario', $params)
        ];
    }

    /**
     *
     * @Route("/fis/emissaonfe/list/", name="fis_emissaonfe_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function list(Request $request)
    {
        $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsEmUso();
        $params =
            [
                'typeClass' => NotaFiscalType::class,
                'formView' => 'Fiscal/emissaoNFe/form.html.twig',
                'formRoute' => 'fis_emissaonfe_form',
                'formPageTitle' => 'NFe',
                'listView' => 'Fiscal/emissaoNFe/list.html.twig',
                'listRoute' => 'fis_emissaonfe_list',
                'listRouteAjax' => 'fis_emissaonfe_datatablesJsList',
                'listPageTitle' => 'NFe\'s Emitidas',
                'page_subTitle' => StringUtils::mascararCnpjCpf($nfeConfigsEmUso['cnpj']) . ' - ' . $nfeConfigsEmUso['razaosocial'],
                'listId' => 'emissaoNFeList',
            ];

        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fis/emissaonfe/datatablesJsList/", name="fis_emissaonfe_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     */
    public function datatablesJsList(Request $request)
    {
        $rParams = $request->request->all();
        parse_str($rParams['formPesquisar'], $formPesquisar);
        // fixos
        $defaultFilters['filter']['documentoEmitente'] = preg_replace("/[^0-9]/", '', $this->nfeUtils->getNFeConfigsEmUso()['cnpj']);
        $defaultFilters['filter']['tipoNotaFiscal'] = 'NFE';
        return $this->doDatatablesJsList($request, $defaultFilters);
    }

    /**
     * @Route("/fis/emissaonfe/clonar/{notaFiscal}", name="fis_emissaonfe_clonar")
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     * @throws ViewException
     */
    public function clonar(NotaFiscal $notaFiscal): RedirectResponse
    {
        $novaNotaFiscal = $this->entityHandler->doClone($notaFiscal);
        $this->addFlash('success', 'Nota Fiscal clonada!');
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $novaNotaFiscal->getId()]);
    }

    /**
     * @Route("/fis/emissaonfe/getPessoaByDocumento/{documento}", name="fis_emissaonfe_getPessoaByDocumento")
     * @param string $documento
     * @return JsonResponse
     * @throws ViewException
     */
    public function getPessoaByDocumento(string $documento): JsonResponse
    {
        if (!ValidaCPFCNPJ::valida($documento)) {
            return new JsonResponse(['result' => 'ERRO', 'msg' => 'CPF/CNPJ inválido']);
        }
        /** @var NotaFiscalRepository $repoNotaFiscal */
        $repoNotaFiscal = $this->getDoctrine()->getRepository(NotaFiscal::class);
        $dadosPessoa = $repoNotaFiscal->findUltimosDadosPessoa($documento);
        if ($dadosPessoa === []) {
            /** @var PessoaRepository $repoPessoa */
            $repoPessoa = $this->getDoctrine()->getRepository(Pessoa::class);
            $dadosPessoa = $repoPessoa->findPessoaMaisCompletaPorDocumento($documento);
        }
        return new JsonResponse(['result' => 'OK', 'dados' => $dadosPessoa]);
    }

    /**
     * @Route("/fis/emissaonfe/copiarNotaFiscalItem/{notaFiscalItem}", name="fis_emissaonfe_copiarNotaFiscalItem")
     * @param SessionInterface $session
     * @param NotaFiscalItem $notaFiscalItem
     * @return JsonResponse
     */
    public function copiarNotaFiscalItem(SessionInterface $session, NotaFiscalItem $notaFiscalItem): JsonResponse
    {
        $session->set('fis_emissaonfe_copiarNotaFiscalItem', $notaFiscalItem->getId());
        $this->addFlash('success', 'Item copiado');
        return new JsonResponse(['result' => 'OK']);
    }

    /**
     * @Route("/fis/emissaonfe/colarNotaFiscalItem/{notaFiscal}", name="fis_emissaonfe_colarNotaFiscalItem")
     * @param SessionInterface $session
     * @param NotaFiscal $notaFiscal
     * @return RedirectResponse
     * @throws ViewException
     */
    public function colarNotaFiscalItem(SessionInterface $session, NotaFiscal $notaFiscal): RedirectResponse
    {
        $notaFiscalItemId = $session->get('fis_emissaonfe_copiarNotaFiscalItem');
        /** @var NotaFiscalItem $notaFiscalItem */
        $notaFiscalItem = $this->getDoctrine()->getRepository(NotaFiscalItem::class)->find($notaFiscalItemId);
        $this->notaFiscalBusiness->colarItem($notaFiscal, $notaFiscalItem);
        return $this->redirectToRoute('fis_emissaonfe_form', ['id' => $notaFiscal->getId(), '_fragment' => 'itens']);
    }

    /**
     * @Route("/fis/emissaonfe/consultarCNPJ", name="fis_emissaonfe_consultarCNPJ")
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
     * @Route("/fis/emissaonfe/downloadXML/{nf}", name="fis_emissaonfe_downloadXML", requirements={"nf"="\d+"})
     *
     * @param NotaFiscal $nf
     * @return Response
     * @throws ViewException
     */
    public function downloadXML(NotaFiscal $nf): Response
    {
        $filename = $nf->getChaveAcesso() . '.xml';

        if (!$nf->getXMLDecoded() || $nf->getXMLDecoded()->getName() !== 'nfeProc') {
            $nf = $this->spedNFeBusiness->gerarXML($nf);
            $tools = $this->nfeUtils->getToolsEmUso();
            $tools->model($nf->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
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
     *
     * @Route("/fis/emissaonfe/downloadXMLsMesAno/", name="fis_emissaonfe_downloadXMLsMesAno/")
     *
     * @param Request $request
     * @return Response
     * @throws ViewException
     */
    public function downloadXMLsMesAno(Request $request): Response
    {
        $mesano = $request->get('mesano');

        $zip = new ZipArchive();
        $arquivo = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . $mesano . '.zip';
        @unlink($arquivo);

        if ($zip->open($arquivo, ZipArchive::CREATE) !== TRUE) {
            throw new \RuntimeException('Não foi possível escrever o arquivo zip');
        }

        $tools = $this->nfeUtils->getToolsEmUso();


        $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();

        /** @var NotaFiscalRepository $repoNotasFiscais */
        $repoNotasFiscais = $this->getDoctrine()->getRepository(NotaFiscal::class);

        $nfes = $repoNotasFiscais->findByFiltersSimpl([
            ['dtEmissao', 'BETWEEN_DATE', DateTimeUtils::getDatasMesAno($mesano)],
            ['documentoEmitente', 'EQ', $nfeConfigs['cnpj']],
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
        $problemas[] = 'CNPJ: ' . $nfeConfigs['cnpj'];

        /** @var NotaFiscal $nf */
        foreach ($nfes as $nf) {
            $this->logger->info($nf->getTipoNotaFiscal() . '. Série: ' . $nf->getSerie() . ', Número: ' . $nf->getNumero() . '.');
            if (!$nf->getNumero()) {
                $this->logger->info('Nota sem número. Continuando...');
                continue;
            }
            if (!$nf->getCStat()) {
                $this->logger->info('Nota sem "cstat". Continuando...');
                continue;
            }

            if ((int)$nf->getCStat() === -100) {
                $this->spedNFeBusiness->consultarStatus($nf);
            }

            if (((int)$nf->getCStat() === 100 || (int)$nf->getCStat() === 101) && !$nf->getXmlNota()) {
                if ((int)$nf->getCStatLote() === 217) {
                    $msg = 'NFE (Chave: ' . $nf->getChaveAcesso() . ') com statLote = 217 (NF-E NAO CONSTA NA BASE DE DADOS DA SEFAZ). Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
                $this->logger->info('XML não encontrado para nota ' . $nf->getChaveAcesso());
                $nf = $this->spedNFeBusiness->gerarXML($nf);
                $tools->model($nf->getTipoNotaFiscal() === 'NFE' ? '55' : '65');
                $fileContent = $tools->signNFe($nf->getXmlNota());
                $nf->setXmlNota($fileContent);
                $this->entityHandler->save($nf);
            }
            if (!$nf->getXMLDecoded()) {
                $this->logger->info('getXMLDecoded não encontrado para nota ' . $nf->getChaveAcesso());
            }
            if ($nf->getXMLDecoded()->getName() !== 'nfeProc') {
                $this->logger->info('XML sem o nfeProc. Consultando status...');
                $this->spedNFeBusiness->consultarStatus($nf);
                if ((int)$nf->getCStatLote() !== 104 && (int)$nf->getCStatLote() !== 100) {
                    $msg = $nf->getTipoNotaFiscal() . '. Série: ' . $nf->getSerie() . ', Número: ' . $nf->getNumero() . ': cStatLote: ' . $nf->getCStatLote() . ', xMotivoLote: ' . $nf->getXMotivoLote();
                    $this->logger->error($msg);
                    $problemas[] = $msg;
                    continue;
                }
            }

            if ($nf->getTipoNotaFiscal() === 'NFE') {
                if ((int)$nf->getCStat() === 100) {
                    $nfes100[] = $nf;
                } else if ((int)$nf->getCStat() === 101 || ((int)$nf->getCStat() === 135 && (int)$nf->getCStatLote() === 101)) {
                    $problemas[] = 'NFE ' . $nf->getNumero() . ' (Chave: ' . $nf->getChaveAcesso() . ') CANCELADA';
                    $nfes101[] = $nf;
                } else {
                    $msg = 'NFE ' . $nf->getNumero() . ' (Chave: ' . $nf->getChaveAcesso() . ') com status diferente de 100 ou 101. Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
            } else if ($nf->getTipoNotaFiscal() === 'NFCE') {
                if ((int)$nf->getCStat() === 100) {
                    $nfces100[] = $nf;
                } else if ((int)$nf->getCStat() === 101) {
                    $nfces101[] = $nf;
                } else {
                    $msg = 'NFE ' . $nf->getNumero() . ' (Chave: ' . $nf->getChaveAcesso() . ') com status diferente de 100 ou 101. Não será possível exportar para o zip.';
                    $problemas[] = $msg;
                    $this->logger->error($msg);
                    continue;
                }
            }
            $verifNumeros[$nf->getTipoNotaFiscal()][] = $nf->getNumero();
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
            if (!mkdir($concurrentDirectory = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/') && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        /** @var NotaFiscal $nfe100 */
        foreach ($nfes100 as $nfe100) {
            $nomeArquivo = $nfe100->getChaveAcesso() . '-' . $nfe100->getNumero() . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfe100->getXmlNota());
            touch($arquivoCompleto, $nfe100->getDtEmissao()->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFEs/homologadas/' . $nomeArquivo);
        }

        foreach ($nfes101 as $nfe101) {
            $nomeArquivo = $nfe101->getChaveAcesso() . '-' . $nfe101->getNumero() . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfe101->getXmlNota());
            touch($arquivoCompleto, $nfe101->getDtEmissao()->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFEs/canceladas/' . $nomeArquivo);
        }

        foreach ($nfces100 as $nfce100) {
            $nomeArquivo = $nfce100->getChaveAcesso() . '-' . $nfce100->getNumero() . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfce100->getXmlNota());
            touch($arquivoCompleto, $nfce100->getDtEmissao()->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFCEs/homologadas/' . $nomeArquivo);
        }

        foreach ($nfces101 as $nfce101) {
            $nomeArquivo = $nfce101->getChaveAcesso() . '-' . $nfce101->getNumero() . '.xml';
            $arquivoCompleto = $_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/' . $nomeArquivo;
            file_put_contents($arquivoCompleto, $nfce101->getXmlNota());
            touch($arquivoCompleto, $nfce101->getDtEmissao()->getTimestamp());
            $zip->addFile($arquivoCompleto, 'NFCEs/canceladas/' . $nomeArquivo);
        }

        $zip->addFromString('avisos.txt', implode(PHP_EOL, $problemas));

        $zip->status;
        $zip->close();

        // Return a response with a specific content
        $response = new Response(file_get_contents($arquivo));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-length', filesize($arquivo));

        // Set the content disposition
        $response->headers->set('Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $mesano . '.zip'
            )
        );
        @rmdir($_SERVER['FISCAL_PASTA_DOWNLOAD_XMLS'] . 'tmp/');
        // Dispatch request
        return $response;
    }


    /**
     *
     * @Route("/fis/emissaonfe/inutilizaNumeracao/", name="fis_emissaonfe_inutilizaNumeracao")
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
     * @Route("/fis/emissaonfe/imprimirDANFCE", name="fis_emissaonfe_imprimirDANFCE")
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

        $configs = $this->nfeUtils->getNFeConfigsByCNPJ($nf->getDocumentoEmitente());

        $primeiros = $nf->getChaveAcesso() . '|2|1|' . (int)$configs['CSCid_prod'];
        $codigoHash = sha1($primeiros . $configs['CSC_prod']);
        $qrcode = 'http://www.fazenda.pr.gov.br/nfce/qrcode?p=' . $primeiros . '|' . $codigoHash;

        $chaveAcesso =
            substr($nf->getChaveAcesso(), 0, 4) . ' ' .
            substr($nf->getChaveAcesso(), 4, 4) . ' ' .
            substr($nf->getChaveAcesso(), 8, 4) . ' ' .
            substr($nf->getChaveAcesso(), 12, 4) . ' ' .
            substr($nf->getChaveAcesso(), 16, 4) . ' ' .
            substr($nf->getChaveAcesso(), 24, 4) . ' ' .
            substr($nf->getChaveAcesso(), 28, 4) . ' ' .
            substr($nf->getChaveAcesso(), 32, 4) . ' ' .
            substr($nf->getChaveAcesso(), 36, 4) . ' ' .
            substr($nf->getChaveAcesso(), 40, 4);

        $params = [
            'xml' => $nf->getXMLDecoded(),
            'cancelada' => (int)$nf->getCStat() === 135,
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
     * @Route("/fis/emissaonfe/imprimirDANFCEhtml", name="fis_emissaonfe_imprimirDANFCEhtml")
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

        $configs = $this->nfeUtils->getNFeConfigsByCNPJ($nf->getDocumentoEmitente());

        $primeiros = $nf->getChaveAcesso() . '|2|1|' . (int)$configs['CSCid_prod'];
        $codigoHash = sha1($primeiros . $configs['CSC_prod']);
        $qrcode = 'http://www.fazenda.pr.gov.br/nfce/qrcode?p=' . $primeiros . '|' . $codigoHash;

        $nf->setChaveAcesso(
            substr($nf->getChaveAcesso(), 0, 4) . ' ' .
            substr($nf->getChaveAcesso(), 4, 4) . ' ' .
            substr($nf->getChaveAcesso(), 8, 4) . ' ' .
            substr($nf->getChaveAcesso(), 12, 4) . ' ' .
            substr($nf->getChaveAcesso(), 16, 4) . ' ' .
            substr($nf->getChaveAcesso(), 24, 4) . ' ' .
            substr($nf->getChaveAcesso(), 28, 4) . ' ' .
            substr($nf->getChaveAcesso(), 32, 4) . ' ' .
            substr($nf->getChaveAcesso(), 36, 4) . ' ' .
            substr($nf->getChaveAcesso(), 40, 4)
        );

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
     * @Route("/fis/emissaonfe/consultaRecibo/{notaFiscal}", name="fis_emissaonfe_consultaRecibo")
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
