<?php

namespace App\Controller\Fiscal;

use App\Business\Fiscal\NotaFiscalBusiness;
use App\Entity\Fiscal\FinalidadeNF;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalVenda;
use App\Entity\Vendas\Venda;
use App\Form\Fiscal\NotaFiscalType;
use App\Repository\Fiscal\NotaFiscalVendaRepository;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\APIClient\CrosierEntityIdAPIClient;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class EmissaoFiscalPVController
 * @package App\Controller
 *
 * @author Carlos Eduardo Pauluk
 */
class EmissaoFiscalPVController extends BaseController
{

    /** @var NotaFiscalBusiness */
    private $notaFiscalBusiness;

    /** @var NFeUtils */
    private $nfeUtils;

    /** @var CrosierEntityIdAPIClient */
    private $crosierEntityIdAPIClient;

    /** @var LoggerInterface */
    private $logger;

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
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }

    /**
     * @required
     * @param CrosierEntityIdAPIClient $crosierEntityIdAPIClient
     */
    public function setCrosierAPIClient(CrosierEntityIdAPIClient $crosierEntityIdAPIClient): void
    {
        $this->crosierEntityIdAPIClient = $crosierEntityIdAPIClient;
    }

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/form/{venda}", name="fis_emissaofiscalpv_form", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda $venda
     * @return RedirectResponse|Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function form(Request $request, Venda $venda)
    {
        // Verifica se a venda já tem uma NotaFiscal associada
        /** @var NotaFiscalVendaRepository $repoNotaFiscalVenda */
        $repoNotaFiscalVenda = $this->getDoctrine()->getRepository(NotaFiscalVenda::class);
        /** @var NotaFiscal $notaFiscal */
        $notaFiscal = $repoNotaFiscalVenda->findNotaFiscalByVenda($venda);
        if (!$notaFiscal) {
            $notaFiscal = new NotaFiscal();
            $notaFiscal->setTipoNotaFiscal('NFCE');
            $notaFiscal->setFinalidadeNf(FinalidadeNF::NORMAL['key']);
        }

        $tipoAnterior = $notaFiscal->getTipoNotaFiscal();

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);

        $tipoAtual = $notaFiscal->getTipoNotaFiscal();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscalVenda($venda, $notaFiscal, $tipoAtual !== $tipoAnterior);
                try {
                    $notaFiscal->setDtEmissao(new \DateTime());
                    $notaFiscal->setDtSaiEnt(new \DateTime());
                    $notaFiscal = $this->notaFiscalBusiness->faturarNFe($notaFiscal);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
                return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
            }
            $form->getErrors(true, true);
        }

        if (!$notaFiscal->getId()) {
            $permiteFaturamento = true;
        } else {
            $permiteFaturamento = $this->notaFiscalBusiness->permiteFaturamento($notaFiscal);
        }
        $permiteReimpressao = $this->notaFiscalBusiness->permiteReimpressao($notaFiscal);
        $permiteReimpressaoCancelamento = $this->notaFiscalBusiness->permiteReimpressaoCancelamento($notaFiscal);
        $permiteCancelamento = $this->notaFiscalBusiness->permiteCancelamento($notaFiscal);

        $dadosEmitente = $this->nfeUtils->getNFeConfigsEmUso();

        return $this->doRender('/Fiscal/emissaoFiscalPV/form.html.twig', [
            'form' => $form->createView(),
            'venda' => $venda,
            'notaFiscal' => $notaFiscal,
            'permiteFaturamento' => $permiteFaturamento,
            'permiteCancelamento' => $permiteCancelamento,
            'permiteReimpressao' => $permiteReimpressao,
            'permiteReimpressaoCancelamento' => $permiteReimpressaoCancelamento,
            'dadosEmitente' => $dadosEmitente
        ]);
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/reimprimir/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_reimprimir")
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse
     */
    public function reimprimir(NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        if (!$notaFiscal->getXmlNota()) {
            $this->addFlash('error', 'XML não encontrado.');
        } else {
            $this->notaFiscalBusiness->imprimir($notaFiscal);
        }
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/reimprimirCancelamento/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_reimprimirCancelamento")
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse
     */
    public function reimprimirCancelamento(NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        $this->notaFiscalBusiness->imprimirCancelamento($notaFiscal);
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/cancelarForm/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_cancelarForm")
     *
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse|Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function cancelarForm(Request $request, NotaFiscal $notaFiscal, Venda $venda)
    {

        if (!$notaFiscal) {
            $this->addFlash('error', 'Venda não encontrada!');
            return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
        }

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // $notaFiscal->setMotivoCancelamento($data['cancelamento_motivo']);
                $this->notaFiscalBusiness->cancelar($notaFiscal);
                return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
            }

            $form->getErrors(true, true);
        }

        $permiteCancelamento = $this->notaFiscalBusiness->permiteCancelamento($notaFiscal);
        $permiteReimpressaoCancelamento = $this->notaFiscalBusiness->permiteReimpressaoCancelamento($notaFiscal);

        $response = $this->doRender('/Fiscal/emissaoFiscalPV/cancelarForm.html.twig', array(
            'form' => $form->createView(),
            'venda' => $venda,
            'notaFiscal' => $notaFiscal,
            'permiteCancelamento' => $permiteCancelamento,
            'permiteReimpressaoCancelamento' => $permiteReimpressaoCancelamento
        ));
        return $response;
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/consultarStatus/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_consultarStatus")
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function consultarStatus(NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        $notaFiscal = $this->notaFiscalBusiness->consultarStatus($notaFiscal);
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/consultarCNPJ/{cnpj}", name="fis_emissaofiscalpv_consultarCNPJ")
     * @param $cnpj
     * @return Response
     * @throws \Exception
     */
    public function consultarCNPJ($cnpj): Response
    {
        $dados = $this->notaFiscalBusiness->consultarCNPJ($cnpj);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($dados, 'json');

        return new Response($json);
    }


//    /**
//     *
//     * @Route("/fis/getXmls", name="fis_getXmls")
//     * @return Response
//     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
//     */
//    public function getXmls(NotaFiscalEntityHandler $eh)
//    {
//        $mesano = '201910';
//        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
//
//        $r = $mesano . '<hr>';
//
//        /** @var Connection $conn */
//        $conn = $eh->getDoctrine()->getConnection();
//
//        $files = scandir($pastaUnimake . '/enviado/Autorizados/' . $mesano, SCANDIR_SORT_NONE);
//        foreach ($files as $file) {
//            if (strpos($file, 'procNFe') === FALSE || substr($file, -3) !== 'xml') {
//                continue;
//            }
//            $contents = file_get_contents($pastaUnimake . '/enviado/Autorizados/' . $mesano . '/' . $file);
//            $nfeLoaded = simplexml_load_string($contents);
//
//            /** @var NotaFiscal $nfe */
//            $nfe = $this->getDoctrine()->getRepository(NotaFiscal::class)
//                ->findOneBy([
//                    'serie' => $nfeLoaded->NFe->infNFe->ide->serie,
//                    'numero' => $nfeLoaded->NFe->infNFe->ide->nNF,
//                    'ambiente' => 'PROD',
//                ]);
//
//            if ($nfe) {
//                if (!$nfe->getXmlNota()) {
//
//                    $r .= $nfe->getId() . ' atualizada! <br>';
//
//                    $conn->update('fis_nf', ['xml_nota' => $contents], ['id' => $nfe->getId()]);
//
////                    $nfe->setXmlNota($contents);
////                    $eh->save($nfe);
//                }
//            } else {
//                $r .= '<b>' . $mesano . '/' . $file . ' não tem na base! </b><br>';
//            }
//
//        }
//
//        return new Response($r);
//    }


}