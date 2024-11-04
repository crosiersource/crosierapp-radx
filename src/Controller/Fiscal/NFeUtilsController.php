<?php

namespace App\Controller\Fiscal;


use App\Form\Fiscal\ConfigToolsType;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use Doctrine\DBAL\Connection;
use NFePHP\Common\Certificate;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class NFeUtilsController extends BaseController
{
    /** @required */
    public SpedNFeBusiness $spedNFeBusiness;

    /** @required */
    public DistDFeBusiness $distDFeBusiness;

    /** @required */
    public NFeUtils $nfeUtils;

    /** @required */
    public LoggerInterface $logger;


    /**
     * @Route("/fis/nfeUtils/consultaChave/{notaFiscal}", name="nfeUtils_consultaChave")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     */
    public function consultaChave(NotaFiscal $notaFiscal): ?Response
    {
        try {
            $this->spedNFeBusiness->consultaChave($notaFiscal);
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    
    /**
     * @Route("/fis/nfeUtils/download/{notaFiscal}", name="nfeUtils_download")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     */
    public function download(NotaFiscal $notaFiscal): JsonResponse
    {
        try {
            $this->distDFeBusiness->downloadNFe($notaFiscal);
            return CrosierApiResponse::success();
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


    /**
     * @Route("/fis/nfeUtils/reparseDownloadedXML/{notaFiscal}", name="nfeUtils_reparseDownloadedXML")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     */
    public function reparseDownloadedXML(NotaFiscal $notaFiscal): ?Response
    {
        try {
            $this->distDFeBusiness->nfeProc2NotaFiscal($notaFiscal->documentoDestinatario, $notaFiscal->getXMLDecoded(), $notaFiscal);
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    
    /**
     * @Route("/fis/nfeUtils/clearCaches", name="fis_nfeUtils_clearCaches")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     * @throws \Exception
     */
    public function clearCaches(): ?Response
    {
        try {
            $this->nfeUtils->clearCaches();
            $this->addFlash('success', 'Os cachês foram limpados com sucesso');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao limpar cachês');
        }
        return $this->redirect('/');
    }


    /**
     * @Route("/fis/nfeUtils/configTools", name="nfeUtils_configTools")
     * @IsGranted("ROLE_FISCAL_ADMIN", statusCode=403)
     * @throws ViewException
     */
    public function configToolsForm(Request $request): ?Response
    {
        if ($request->get('id')) {
            $this->nfeUtils->saveNfeConfigsIdEmUso($request->get('id'));
            $this->nfeUtils->clearCaches();
        }
        $configs = $this->nfeUtils->getNFeConfigsEmUso();
        $form = $this->createForm(ConfigToolsType::class, $configs);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var UploadedFile $certificado */
                $certificado = $form['certificado']->getData();
                $configs = $form->getData();
                if ($certificado) {
                    $configs['certificado'] = base64_encode(file_get_contents($certificado->getPathname()));
                }
                try {
                    $configs['cnpj'] = preg_replace("/[^0-9]/", '', $configs['cnpj']);
                    $this->nfeUtils->saveNFeConfigs($configs);
                    $this->addFlash('success', 'Configurações salvas com sucesso!');
                } catch (\Exception|ViewException $e) {
                    $this->logger->error('Erro ao salvar as configurações');
                    $this->logger->error($e->getMessage());
                    $this->addFlash('error', 'Erro ao salvar as configurações');
                    if ($e instanceof ViewException) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            } else {
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $vParams['form'] = $form->createView();

        return $this->doRender('/Fiscal/configTools.html.twig', $vParams);
    }


    /**
     * @Route("/fis/nfeUtils/selecionarContribuinte", name="fis_nfeUtils_selecionarContribuinte")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     * @throws ViewException
     */
    public function selecionarContribuinte(Request $request, Connection $conn): ?Response
    {
        try {
            if ($request->get('btnSalvar')) {
                $contribuinteId = $request->get('contribuinteId');
                $conn->update('cfg_app_config',
                    ['valor' => $contribuinteId],
                    [
                        'app_uuid' => $_SERVER['CROSIERAPP_UUID'],
                        'chave' => 'nfeConfigsIdEmUso_' . $this->getUser()->getUsername()
                    ]
                );
            }

            $rContribuintes = $conn->fetchAllAssociative('SELECT id, valor FROM cfg_app_config WHERE chave LIKE \'nfeConfigs\\_%\'');

            $contribuintes = [];
            $idAtual = $this->nfeUtils->getNfeConfigsIdEmUso();
            foreach ($rContribuintes as $rContribuinte) {
                $nfeConfigs = json_decode($rContribuinte['valor'], true);
                $pfx = base64_decode($nfeConfigs['certificado']);
                $certificadoValidoAte = 'ERRO';
                try {
                    $certificate = Certificate::readPfx($pfx, $nfeConfigs['certificadoPwd']);
                    $certificadoValidoAte = $certificate->getValidTo()->format('d/m/Y H:i:s');
                } catch (\Throwable $e) {
                    $this->logger->error('Erro ao ler certificado', $e);
                    throw new ViewException('Erro ao ler certificado');
                }
                $contribuintes[] = [
                    'id' => $rContribuinte['id'],
                    'empresa' => StringUtils::mascararCnpjCpf($nfeConfigs['cnpj']) . ' - ' . $nfeConfigs['razaosocial'],
                    'checked' => $idAtual === (int)$rContribuinte['id'] ? 'checked' : '',
                    'certificadoValidoAte' => $certificadoValidoAte,
                ];
            }
            $params['contribuintes'] = $contribuintes;
            $params['page_title'] = 'Selecionar Contribuinte';

            return $this->doRender('Fiscal/selecionarContribuinte.html.twig', $params);
        } catch (\Throwable $e) {
            $msg = 'Erro ao alternarNfeConfigsIdEmUso';
            if ($e instanceof ViewException) {
                $msg .= ' (' . $e->getMessage() . ')';
            }
            $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsEmUso();
            return new JsonResponse(['result' => 'ERR', 'msg' => $msg, 'nfeConfigsEmUso' => $nfeConfigsEmUso]);
        }
    }


    /**
     * @Route("/api/fis/nfeUtils/getContribuintes", name="fis_nfeUtils_getContribuintes")
     * @IsGranted("ROLE_FISCAL", statusCode=403)
     */
    public function getContribuintes(Connection $conn): JsonResponse
    {
        try {
            $rContribuintes = $conn->fetchAllAssociative("SELECT id, valor FROM cfg_app_config WHERE chave LIKE 'nfeConfigs\_%'");

            $contribuintes = [];
            $idAtual = $this->nfeUtils->getNfeConfigsIdEmUso();
            foreach ($rContribuintes as $rContribuinte) {
                $nfeConfigs = json_decode($rContribuinte['valor'], true);
                $contribuintes[] = [
                    'id' => $rContribuinte['id'],
                    'cnpj' => $nfeConfigs['cnpj'],
                    'empresa' => StringUtils::mascararCnpjCpf($nfeConfigs['cnpj']) . ' - ' . $nfeConfigs['razaosocial'],
                    'checked' => $idAtual === (int)$rContribuinte['id'] ? 'checked' : ''
                ];
            }

            return new JsonResponse(
                [
                    'RESULT' => 'OK',
                    'MSG' => count($contribuintes) . ' registro(s)',
                    'DATA' => $contribuintes
                ]
            );
        } catch (\Throwable $e) {
            $msg = ExceptionUtils::treatException($e);
            if (!$msg) {
                $msg = 'Erro - getContribuintes';
            }
            $r = [
                'RESULT' => 'ERRO',
                'MSG' => $msg
            ];
            return new JsonResponse($r);
        }
    }


}
