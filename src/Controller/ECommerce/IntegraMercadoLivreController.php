<?php

namespace App\Controller\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegraMercadoLivreController extends BaseController
{

    protected SyslogBusiness $syslog;

    /**
     * @required
     * @param SyslogBusiness $syslog
     */
    public function setSyslog(SyslogBusiness $syslog): void
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
    }


    /**
     * @Route("/ecomm/mercadoLivre/authCallback", name="ecomm_mercadoLivre_authCallback")
     */
    public function authCallback(Request $request): Response
    {
        $r = [
            'clienteIp: ' . $request->getClientIp(),
            'host: ' . $request->getHost(),
            'ml_code: ' . $request->query->get('code'),
            'ml_state: ' . $request->query->get('state'),
        ];
        
        $this->syslog->info('ecomm_mercadoLivre_authCallback', implode(PHP_EOL, $r));   
        return new Response('OK <hr /><pre>' . implode('<br />', $r) . '</pre>');
    }

    /**
     * @Route("/ecomm/mercadolivre/endpoint", name="ecomm_mercadoLivre_endpoint")
     */
    public function mercadolivreEndpoint(Request $request): Response
    {
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        $r[] = '<hr />';
        $r[] = 'Content:';
        $r[] = $request->getContent();
        $r[] = '<hr />';
        $r[] = 'Query';
        foreach ($request->query->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        
        $r[] = '<hr />';
        $r[] = 'Request';
        foreach ($request->request->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }

        $r[] = '<hr />';
        $r[] = 'Headers';
        foreach ($request->headers->all() as $k => $v) {
            $r[] = $k . ': ' . print_r($v, true);
        }
        
        $r[] = '<hr />';

        $this->syslog->info('ecomm_mercadoLivre_endpoint', implode(PHP_EOL, $r));
        
        return new Response(implode('<br />', $r));
    }

}