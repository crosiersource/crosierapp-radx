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
class IntegraTrayController extends BaseController
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
     *
     * @Route("/ecomm/tray/endpoint", name="ecomm_tray_endpoint")
     *
     */
    public function authCallback(Request $request): Response
    {
        
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        $r[] = '<hr />';
        $r[] = 'Query';
        $r[] = implode('<br />', $request->query->all());
        $r[] = '<hr />';
        $r[] = 'Request';
        $r[] = implode('<br />', $request->request->all());
        $r[] = '<hr />';

        return new Response(implode('<br />', $r));
    }

}