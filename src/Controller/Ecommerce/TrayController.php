<?php

namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\ClienteConfig;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Ecommerce\ClienteConfigRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class TrayController extends BaseController
{

    protected SyslogBusiness $syslog;

    private ClienteConfigEntityHandler $clienteConfigEntityHandler;

    /**
     * @required
     * @param SyslogBusiness $syslog
     */
    public function setSyslog(SyslogBusiness $syslog): void
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
    }

    /**
     * @required
     * @param ClienteConfigEntityHandler $clienteConfigEntityHandler
     */
    public function setClienteConfigEntityHandler(ClienteConfigEntityHandler $clienteConfigEntityHandler): void
    {
        $this->clienteConfigEntityHandler = $clienteConfigEntityHandler;
    }


    /**
     * @Route("/ecommerce/tray/endpoint", name="ecommerce_tray_endpoint")
     */
    public function trayEndpoint(Connection $conn, Request $request)
    {
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        $r[] = '<hr />';
        $r[] = 'Content:';
        $r[] = $request->getContent();
        $r[] = '--------------------------------';
        $r[] = 'Query';
        foreach ($request->query->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        $r[] = '--------------------------------';
        $r[] = 'Request';
        foreach ($request->request->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        $r[] = '--------------------------------';
        $r[] = 'Headers';
        foreach ($request->headers->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }

        $this->syslog->info('ecomm_tray_endpoint', implode(PHP_EOL, $r));

        $storeId = $request->get('store');
        $rs = $conn->fetchAssociative('SELECT id FROM ecomm_cliente_config WHERE json_data->>"$.tray.store_id" = :storeId', ['storeId' => $storeId]);
        if ($rs['id'] ?? false) {
            /** @var ClienteConfigRepository $repoClienteConfig */
            $repoClienteConfig = $this->getDoctrine()->getRepository(ClienteConfig::class);
            $clienteConfig = $repoClienteConfig->find($rs['id']);
            $clienteConfig->jsonData['tray']['code'] = $request->get('code');
            $this->clienteConfigEntityHandler->save($clienteConfig);
            return $this->redirectToRoute('ecommerce_clienteConfig_form', ['id' => $rs['id']]);
        } // else

        return new Response(implode('<br />', $r));
    }


}