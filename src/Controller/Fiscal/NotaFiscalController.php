<?php

namespace App\Controller\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\ValidaCPFCNPJ;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\FaturaBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
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
class NotaFiscalController extends BaseController
{

    /** @required */
    public NotaFiscalEntityHandler $notaFiscalEntityHandler;

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
     * @Route("/api/fis/notaFiscal/bla/{notaFiscal}", name="api_fis_notaFiscal_bla")
     */
    public function bla(NotaFiscal $notaFiscal): JsonResponse
    {

        $notaFiscal->dtEmissao = $notaFiscal->dtEmissao->setTimezone(new \DateTimeZone('America/Fortaleza'));
        $teste = $notaFiscal->dtEmissao->format('Y-m-d\TH:i:sP');

        return CrosierApiResponse::success();

    }

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
     * @Route("/fis/notaFiscal/emitidas/inutilizaNumeracaoPorDados", name="fis_notaFiscal_emitidas_inutilizaNumeracaoPorDados")
     */
    public function inutilizaNumeracaoPorDados(Request $request): JsonResponse
    {
        try {
            $cnpjEmitente = $request->get('cnpjEmitente');
            $tipo = $request->get('tipo');
            $serie = $request->get('serie');
            $numero = $request->get('numero');
            $r = $this->spedNFeBusiness->inutilizaNumeracao($cnpjEmitente, $tipo, $serie, $numero);
            return CrosierApiResponse::success($r);
        } catch (\Exception $e) {
            return CrosierApiResponse::error('Erro ao inutilizar numeração');
        }
    }
    
    /**
     * @Route("/fis/notaFiscal/emitidas/inutilizaNumeracao/{notaFiscal}", name="fis_notaFiscal_emitidas_inutilizaNumeracao")
     */
    public function inutilizaNumeracao(NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $cnpjEmitente = $notaFiscal->documentoEmitente;
            $tipo = $notaFiscal->tipoNotaFiscal;
            $serie = $notaFiscal->serie;
            $numero = $notaFiscal->numero;
            $r = $this->spedNFeBusiness->inutilizaNumeracao($cnpjEmitente, $tipo, $serie, $numero);
            $notaFiscal->jsonData['retorno_inutilizacao_numeracao'] = $r;
            $this->notaFiscalEntityHandler->save($notaFiscal);
            return CrosierApiResponse::success($r);
        } catch (\Exception $e) {
            return CrosierApiResponse::error('Erro ao inutilizar numeração');
        }
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
     * @Route("/api/fis/notaFiscal/deleteItem/{item}", name="fis_emissaonfe_deleteItem", requirements={"item"="\d+"})
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
            $nova = $this->notaFiscalEntityHandler->doClone($notaFiscal);
            return CrosierApiResponse::success(['id' => $nova->getId()]);
        } catch (\Throwable $e) {
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao clonar');
        }
    }


    /**
     * @Route("/api/fis/notaFiscal/getPessoaByDocumento/{documento}", name="fis_emissaonfe_getPessoaByDocumento")
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


    /**
     * @Route("/api/fis/notaFiscal/lancarDuplicatas/{notaFiscal}/{carteira}/{categoria}", name="api_fis_notaFiscal_lancarDuplicatas")
     */
    public function lancarDuplicatas(
        FaturaBusiness $faturaBusiness,
        NotaFiscal     $notaFiscal,
        Carteira       $carteira,
        Categoria      $categoria
    ): JsonResponse
    {
        try {
            $faturaBusiness->lancarDuplicatasPorNotaFiscal($notaFiscal, $carteira, $categoria);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            $this->syslog->error('Erro ao lancarDuplicatas', $e->getMessage());
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao lançar duplicatas');
        }
    }

    /**
     * @Route("/api/fis/notaFiscal/enviarFaturaParaReprocessamento/{notaFiscal}", name="api_fis_notaFiscal_enviarFaturaParaReprocessamento")
     */
    public function enviarFaturaParaReprocessamento(
        FaturaBusiness $faturaBusiness,
        NotaFiscal     $notaFiscal
    ): JsonResponse
    {
        try {
            $faturaBusiness->enviarFaturaParaReprocessamento($notaFiscal);
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            $this->syslog->error('Erro ao enviarFaturaParaReprocessamento', $e->getMessage());
            return CrosierApiResponse::viewExceptionError($e, 'Erro ao enviar fatura para reprocessamento');
        }
    }


    /**
     * @Route("/fis/notaFiscal/findNumerosNfPulados/{cnpj}", name="fis_notaFiscal_findNumerosNfPulados")
     */
    public function findNumerosNfPulados(String $cnpj): JsonResponse
    {
        $r = $this->doctrine->getRepository(NotaFiscal::class)->findNumerosNfPulados($cnpj);
        return CrosierApiResponse::success($r);
    }


}
