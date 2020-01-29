<?php

namespace App\Controller\Estoque\API;

use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 *
 * @author Carlos Eduardo Pauluk
 */
class UploadAPIController extends AbstractController
{

    /** @var LoggerInterface */
    private $logger;

    /**
     * RelCtsPagReg01APIController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/api/uploads/upload", name="api_uploads_upload")
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $this->logger->debug('Iniciando o upload...');
        $output = ['uploaded' => false];

        $tipoUpload = $request->get('tipoUpload');
        if (!$tipoUpload || !in_array($tipoUpload, ['ESTOQUE'])) {
            $output['msg'] = 'tipoUpload inexistente: "' . $tipoUpload . '"';
            return new JsonResponse($output);
        }
        $dir = $_SERVER['PASTA_UPLOAD_' . $tipoUpload] . 'fila/';

        if (!($arquivo = $request->files->get('arquivo')) || $arquivo->getError() !== 0 || !($arquivo instanceof UploadedFile)) {
            $output['msg'] = 'arquivo não informado';
            $this->logger->debug('"arquivo" não informado');
            $this->logger->debug('arquivo: ' . $arquivo);
            if ($arquivo instanceof UploadedFile) {
                $this->logger->debug('error: ' . $arquivo->getError() . ' (' . $arquivo->getErrorMessage());
            }
            return new JsonResponse($output);
        }

        /** @var UploadedFile $arquivo */
        $uuid = StringUtils::guidv4();
        $extensao = pathinfo($arquivo->getClientOriginalName(), PATHINFO_EXTENSION);
        $novoNome = $uuid . '.' . $extensao;
        $nomeArquivo = $dir . $novoNome;
        copy($arquivo->getPathname(), $nomeArquivo);
        $output['uploaded'] = true;
        $output['nomeArquivo'] = $novoNome;

        return new JsonResponse($output);
    }


}
