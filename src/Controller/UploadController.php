<?php

namespace App\Controller;


use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @author Carlos Eduardo Pauluk
 */
class UploadController extends BaseController
{


    protected SyslogBusiness $syslog;

    
    public function __construct(SyslogBusiness $syslog)
    {
        $this->syslog = $syslog->setApp('radx')->setComponent('UploadController');
    }

    /**
     *
     * @Route("/api/upload", name="upload")
     * @IsGranted("ROLE_UPLOAD", statusCode=403)
     */
    public function upload(Request $request): JsonResponse
    {
        $this->syslog->debug('Iniciando o upload...');
        $output = ['uploaded' => false];

        $tipoArquivo = $request->get('tipoArquivo');
        if (!$tipoArquivo) {
            return CrosierApiResponse::error(null, null, 'tipoArquivo n/d');
        }

        $filename = $request->get('filename');
        if (!$filename) {
            return CrosierApiResponse::error(null, null, 'filename n/d');
        }

        if (!($_SERVER['PASTA_UPLOADS'] ?? false)) {
            return CrosierApiResponse::error(null, null, 'PASTA_UPLOADS n/d');
        }

        $dir_fila = $_SERVER['PASTA_UPLOADS'] . $tipoArquivo . '/fila/';
        $dir_ok = $_SERVER['PASTA_UPLOADS'] . $tipoArquivo . '/fila/';
        $dir_erro = $_SERVER['PASTA_UPLOADS'] . $tipoArquivo . '/fila/';
        @mkdir($dir_fila, 0777, true);
        @mkdir($dir_ok, 0777, true);
        @mkdir($dir_erro, 0777, true);
        
        if (!is_dir($dir_fila)) {
            return CrosierApiResponse::error(null, null, 'path n/d (' . $dir_fila . ')');
        }
        if (!is_dir($dir_ok)) {
            return CrosierApiResponse::error(null, null, 'path n/d (' . $dir_ok . ')');
        }
        if (!is_dir($dir_erro)) {
            return CrosierApiResponse::error(null, null, 'path n/d (' . $dir_erro . ')');
        }

        $rFile = $request->get('file');
        if (!$rFile) {
            return CrosierApiResponse::error(null, null, 'file n/d');
        }

        /** @var UploadedFile $fileData */
        $fileData = gzdecode(base64_decode($rFile));

        $extensao = pathinfo($filename, PATHINFO_EXTENSION);
        $novoNome = ($request->get('substitutivo') ? 'ULTIMO' : StringUtils::guidv4()) . '.' . $extensao;
        $nomeArquivo = $dir_fila . $novoNome;
        file_put_contents($nomeArquivo, $fileData);
        

        return CrosierApiResponse::success();
    }

    

}