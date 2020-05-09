<?php

namespace App\Controller\Fiscal;


use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use App\Form\Fiscal\ConfigToolsType;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class NFeUtilsController extends BaseController
{

    private SpedNFeBusiness $spedNFeBusiness;

    private DistDFeBusiness $distDFeBusiness;

    private NFeUtils $nfeUtils;

    private LoggerInterface $logger;

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
     * @required
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
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
     * @Route("/fis/nfeUtils/consultaChave/{notaFiscal}", name="nfeUtils_consultaChave")
     *
     * @param NotaFiscal $notaFiscal
     * @return Response
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
     *
     * @Route("/fis/nfeUtils/download/{notaFiscal}", name="nfeUtils_download")
     *
     * @param NotaFiscal $notaFiscal
     * @return Response
     */
    public function download(NotaFiscal $notaFiscal): ?Response
    {
        try {
            $this->distDFeBusiness->downloadNFe($notaFiscal);
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }


    /**
     *
     * @Route("/fis/nfeUtils/reparseDownloadedXML/{notaFiscal}", name="nfeUtils_reparseDownloadedXML")
     *
     * @param NotaFiscal $notaFiscal
     * @return Response
     */
    public function reparseDownloadedXML(NotaFiscal $notaFiscal): ?Response
    {
        try {
            $this->distDFeBusiness->nfeProc2NotaFiscal($notaFiscal->getXMLDecoded(), $notaFiscal);
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }


    /**
     *
     * @Route("/fis/nfeUtils/arrumarCNPJs", name="nfeUtils_arrumarCNPJs")
     *
     * @return Response
     */
    public function arrumarCNPJs(): ?Response
    {
        try {
            $this->spedNFeBusiness->arrumarCNPJs();
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     *
     * @Route("/fis/nfeUtils/clearCaches", name="fis_nfeUtils_clearCaches")
     *
     * @return Response
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
     *
     * @Route("/fis/nfeUtils/configTools", name="nfeUtils_configTools")
     *
     * @param Request $request
     * @return Response
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
                } catch (\Exception | ViewException $e) {
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
     *
     * @Route("/fis/nfeUtils/selecionarContribuinte", name="fis_nfeUtils_selecionarContribuinte")
     *
     * @param Request $request
     * @param Connection $conn
     * @return Response
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

            $rContribuintes = $conn->fetchAll('SELECT id, valor FROM cfg_app_config WHERE chave LIKE \'nfeConfigs\\_%\'');

            $contribuintes = [];
            $idAtual = $this->nfeUtils->getNfeConfigsIdEmUso();
            foreach ($rContribuintes as $rContribuinte) {
                $nfeConfigs = json_decode($rContribuinte['valor'], true);
                $contribuintes[] = [
                    'id' => $rContribuinte['id'],
                    'empresa' => StringUtils::mascararCnpjCpf($nfeConfigs['cnpj']) . ' - ' . $nfeConfigs['razaosocial'],
                    'checked' => $idAtual === (int)$rContribuinte['id'] ? 'checked' : ''
                ];
            }
            $params['contribuintes'] = $contribuintes;
            $params['page_title'] = 'Selecionar Contribuinte';

            return $this->doRender('Fiscal/selecionarContribuinte.html.twig', $params);
        } catch (DBALException | ViewException $e) {
            $msg = 'Erro ao alternarNfeConfigsIdEmUso';
            if ($e instanceof ViewException) {
                $msg .= ' (' . $e->getMessage() . ')';
            }
            $nfeConfigsEmUso = $this->nfeUtils->getNFeConfigsEmUso();
            return new JsonResponse(['result' => 'ERR', 'msg' => $msg, 'nfeConfigsEmUso' => $nfeConfigsEmUso]);
        }
    }


}
