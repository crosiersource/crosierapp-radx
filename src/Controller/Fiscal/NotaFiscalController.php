<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalController extends FormListController
{

    /** @var NotaFiscalEntityHandler */
    protected $entityHandler;

    /** @required */
    public NotaFiscalBusiness $notaFiscalBusiness;

    /** @required */
    public SpedNFeBusiness $spedNFeBusiness;

    /** @required */
    public NFeUtils $nfeUtils;

    /** @required */
    public NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler;

    /** @required */
    public NotaFiscalCartaCorrecaoEntityHandler $cartaCorrecaoEntityHandler;


    /**
     * @Route("/api/fis/notaFiscal/consultarCNPJ", name="api_fis_notaFiscal_consultarCNPJ")
     */
    public function consultarCNPJ(Request $request): JsonResponse
    {
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $request->get('cnpj'));
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
     * @Route("/fis/notaFiscal/emitidas/inutilizaNumeracao", name="fis_notaFiscal_emitidas_inutilizaNumeracao")
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
     * @Route("/fis/notaFiscal/emitidas/consultaRecibo/{notaFiscal}", name="fis_notaFiscal_emitidas_consultaRecibo")
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


    /**
     * @Route("/api/fis/notaFiscal/faturar/{notaFiscal}", name="api_fis_notaFiscal_faturar", requirements={"notaFiscal"="\d+"})
     */
    public function faturar(Request $request, NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $gerarXML = StringUtils::parseBoolStr($request->get('gerarXML'));
            $this->notaFiscalBusiness->faturarNFe($notaFiscal, $gerarXML);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao faturar');
        }
    }


    /**
     * @Route("/api/fis/notaFiscal/cancelar/{notaFiscal}", name="api_fis_notaFiscal_cancelar", requirements={"notaFiscal"="\d+"}, methods={"POST"})
     * @throws \Exception
     */
    public function cancelar(Request $request, NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $this->notaFiscalBusiness->consultarStatus($notaFiscal);
            $dadosEmitente = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->documentoEmitente);
            if ($dadosEmitente['cnpj'] !== $notaFiscal->documentoEmitente) {
                throw new ViewException('Emitente da nota diferente do selecionado');
            }
            $json = json_decode($request->getContent(), true);
            $motivoCancelamento = $json['motivoCancelamento'] ?? null;
            if (!$motivoCancelamento) {
                throw new ViewException('É necessário informar um motivo para o cancelamento');
            }
            $notaFiscal->motivoCancelamento = $motivoCancelamento;
            $this->notaFiscalBusiness->cancelar($notaFiscal);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao cancelar');
        }
    }


    /**
     * @Route("/api/fis/notaFiscal/reenviarCartaCorrecao/{cartaCorrecao}", name="api_fis_notaFiscal_reenviarCartaCorrecao", requirements={"cartaCorrecao"="\d+"})
     */
    public function reenviarCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao): JsonResponse
    {
        try {
            $this->notaFiscalBusiness->cartaCorrecao($cartaCorrecao);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao reenviar a carta de correção');
        }
    }


    /**
     * @Route("/api/fis/notaFiscal/consultarStatus/{notaFiscal}", name="api_fis_notaFiscal_consultarStatus")
     */
    public function consultarStatus(NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $this->notaFiscalBusiness->consultarStatus($notaFiscal);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao consultar status');
        }
    }


    /**
     * @Route("/fis/emissaonfe/deleteItem/{item}", name="fis_emissaonfe_deleteItem", requirements={"item"="\d+"})
     */
    public function deleteItem(Request $request, NotaFiscalItem $item): RedirectResponse
    {
        $notaFiscalId = $item->notaFiscal->getId();
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
     * @Route("/api/fis/notaFiscal/clonar/{notaFiscal}", name="api_fis_notaFiscal_clonar")
     */
    public function clonar(NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $nova = $this->notaFiscalBusiness->clonar($notaFiscal);
            return CrosierApiResponse::success(['id' => $nova->getId()]);
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao clonar');
        }
    }


    /**
     * @Route("/fis/emissaonfe/getPessoaByDocumento/{documento}", name="fis_emissaonfe_getPessoaByDocumento")
     */
    public function getPessoaByDocumento(string $documento): JsonResponse
    {
        if (!ValidaCPFCNPJ::valida($documento)) {
            return new JsonResponse(['result' => 'ERRO', 'msg' => 'CPF/CNPJ inválido']);
        }
        /** @var NotaFiscalRepository $repoNotaFiscal */
        $repoNotaFiscal = $this->doctrine->getRepository(NotaFiscal::class);
        $dadosPessoa = $repoNotaFiscal->findUltimosDadosPessoa($documento);
        return new JsonResponse(['result' => 'OK', 'dados' => $dadosPessoa]);
    }


}
