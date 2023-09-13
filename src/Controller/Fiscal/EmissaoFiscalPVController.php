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
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
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

    /** @required */
    public NotaFiscalBusiness $notaFiscalBusiness;

    /** @required */
    public NFeUtils $nfeUtils;


    /**
     * @Route("/fis/emissaofiscalpv/form/{venda}", name="fis_emissaofiscalpv_form", requirements={"venda"="\d+"})
     * @throws ViewException
     */
    public function form(Request $request, Venda $venda)
    {
        $notaFiscal = $this->notaFiscalBusiness->findNotaFiscalByVenda($venda);
        $notaFiscalId = null;
        if (!$notaFiscal) {
            $notaFiscal = new NotaFiscal();
            $notaFiscal->tipoNotaFiscal = 'NFCE';
            $notaFiscal->finalidadeNf = FinalidadeNF::NORMAL['key'];
        } else {
            $notaFiscalId = $notaFiscal->getId();
        }

        $tipoAnterior = $notaFiscal->tipoNotaFiscal;

        $form = $this->createForm(NotaFiscalType::class, $notaFiscal);
        $form->handleRequest($request);
        if ($notaFiscalId) {
            $notaFiscal->setId($notaFiscalId);
        }

        $tipoAtual = $notaFiscal->tipoNotaFiscal;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notaFiscal = $this->notaFiscalBusiness->saveNotaFiscalVenda($venda, $notaFiscal, $tipoAtual !== $tipoAnterior);
                try {
                    $notaFiscal->dtEmissao = new \DateTime();
                    $notaFiscal->dtSaiEnt = new \DateTime();
                    $this->notaFiscalBusiness->faturarNFe($notaFiscal);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
                return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
            }
            $form->getErrors(true, true);
        }

        $dadosEmitente = $this->nfeUtils->getNFeConfigsEmUso();

        return $this->doRender('/Fiscal/emissaoFiscalPV/form.html.twig', [
            'form' => $form->createView(),
            'venda' => $venda,
            'notaFiscal' => $notaFiscal,
            'permiteSalvar' => $notaFiscal->isPermiteSalvar(),
            'permiteFaturamento' => $notaFiscal->isPermiteFaturamento(),
            'permiteCancelamento' => $notaFiscal->isPermiteCancelamento(),
            'permiteReimpressao' => $notaFiscal->isPermiteReimpressao(),
            'permiteReimpressaoCancelamento' => $notaFiscal->isPermiteReimpressaoCancelamento(),
            'permiteCartaCorrecao' => $notaFiscal->isPermiteCartaCorrecao(),
            'dadosEmitente' => $dadosEmitente
        ]);
    }

    
    /**
     * @Route("/fis/emissaofiscalpv/cancelarForm/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_cancelarForm")
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

        return $this->doRender('/Fiscal/emissaoFiscalPV/cancelarForm.html.twig', [
            'form' => $form->createView(),
            'venda' => $venda,
            'notaFiscal' => $notaFiscal,
            'permiteCancelamento' => $notaFiscal->isPermiteCancelamento(),
            'permiteReimpressaoCancelamento' => $notaFiscal->isPermiteReimpressaoCancelamento(),
        ]);
    }


    /**
     * @Route("/fis/emissaofiscalpv/consultarStatus/{notaFiscal}/{venda}", name="fis_emissaofiscalpv_consultarStatus")
     * @throws ViewException
     */
    public function consultarStatus(NotaFiscal $notaFiscal, Venda $venda): RedirectResponse
    {
        $this->notaFiscalBusiness->consultarStatus($notaFiscal);
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $venda->getId()]);
    }


    /**
     * @Route("/fis/emissaofiscalpv/consultarCNPJ/{cnpj}/{uf}", name="fis_emissaofiscalpv_consultarCNPJ")
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
     * @Route("/fis/emissaofiscalpv/corrigirNCMs/{venda}", name="fis_emissaofiscalpv_corrigirNCMs")
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


    /**
     * @Route("/fis/emissaofiscalpv/corrigirNCM/{vendaItem}", name="fis_emissaofiscalpv_corrigirNCM")
     */
    public function corrigirNCM(VendaItem $vendaItem, VendaItemEntityHandler $vendaItemEntityHandler): Response
    {
        try {
            $rNcmPadrao = $this->notaFiscalBusiness->notaFiscalItemEntityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT valor FROM cfg_app_config WHERE chave = \'ncm_padrao\'');
            $ncmPadrao = $rNcmPadrao[0]['valor'] ?? null;
            $vendaItem->jsonData['ncm'] = $ncmPadrao;
            $vendaItemEntityHandler->save($vendaItem);
            $this->addFlash('success', 'NCM corrigido');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Ocorreu um erro ao corrigir o NCM');
        }
        return $this->redirectToRoute('fis_emissaofiscalpv_form', ['venda' => $vendaItem->venda->getId()]);
    }


}