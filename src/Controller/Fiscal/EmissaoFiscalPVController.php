<?php

namespace App\Controller\Fiscal;

use App\Form\Fiscal\NotaFiscalType;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\FinalidadeNF;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
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

    private NotaFiscalBusiness $notaFiscalBusiness;

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
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }


    /**
     *
     * @Route("/fis/emissaofiscalpv/form/{venda}", name="fis_emissaofiscalpv_form", requirements={"venda"="\d+"})
     * @param Request $request
     * @param Venda $venda
     * @return RedirectResponse|Response
     * @throws ViewException
     */
    public function form(Request $request, Venda $venda)
    {
        $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);
        $notaFiscalId = null;
        if (!$notaFiscal) {
            $notaFiscal = new NotaFiscal();
            $notaFiscal->setTipoNotaFiscal('NFCE');
            $notaFiscal->setFinalidadeNf(FinalidadeNF::NORMAL['key']);
        } else {
            $notaFiscalId = $notaFiscal->getId();
        }

        $tipoAnterior = $notaFiscal->getTipoNotaFiscal();

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);
        if ($notaFiscalId) {
            $notaFiscal->setId($notaFiscalId);
        }

        $tipoAtual = $notaFiscal->getTipoNotaFiscal();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscalVenda($venda, $notaFiscal, $tipoAtual !== $tipoAnterior);
                try {
                    $notaFiscal->setDtEmissao(new \DateTime());
                    $notaFiscal->setDtSaiEnt(new \DateTime());
                    $this->notaFiscalBusiness->faturarNFe($notaFiscal);
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
     * @Route("/fis/emissaofiscalpv/cancelarForm/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_cancelarForm")
     *
     * @param Request $request
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse|Response
     * @throws ViewException
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

        return $this->doRender('/Fiscal/emissaoFiscalPV/cancelarForm.html.twig', array(
            'form' => $form->createView(),
            'venda' => $venda,
            'notaFiscal' => $notaFiscal,
            'permiteCancelamento' => $permiteCancelamento,
            'permiteReimpressaoCancelamento' => $permiteReimpressaoCancelamento
        ));
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/consultarStatus/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_consultarStatus")
     * @param NotaFiscal $notaFiscal
     * @param Venda $venda
     * @return RedirectResponse
     * @throws ViewException
     */
    public function consultarStatus(NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        $this->notaFiscalBusiness->consultarStatus($notaFiscal);
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
    }

    /**
     *
     * @Route("/fis/emissaofiscalpv/consultarCNPJ/{cnpj}/{uf}", name="fis_emissaofiscalpv_consultarCNPJ")
     * @param string $cnpj
     * @param string $uf
     * @return Response
     * @throws ViewException
     */
    public function consultarCNPJ(string $cnpj, string $uf): Response
    {
        $dados = $this->notaFiscalBusiness->consultarCNPJ($cnpj, $uf);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($dados, 'json');

        return new Response($json);
    }


    /**
     *
     * @Route("/fis/emissaofiscalpv/corrigirNCMs/{venda}", name="fis_emissaofiscalpv_corrigirNCMs")
     * @param Venda $venda
     * @return Response
     */
    public function corrigirNCMs(Venda $venda): Response
    {
        /** @var NotaFiscal $notaFiscal */
        try {
            $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);
            if (!$notaFiscal) {
                $this->addFlash('error', 'Nota fiscal não encontrada para a venda. Impossível corrigir NCMs');
            } else {
                try {
                    $this->notaFiscalBusiness->corrigirNCMs($notaFiscal);
                    $this->addFlash('success', 'NCMs corrigidos com sucesso');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Ocorreu um erro ao corrigir NCMs');
                }
            }
        } catch (ViewException $e) {
            $this->addFlash('error', 'Ocorreu um erro ao corrigir NCMs');
        }
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);

    }


}