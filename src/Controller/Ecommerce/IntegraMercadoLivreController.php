<?php

namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Messenger\Ecommerce\Message\MlNotification;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
     * @Route("/ecomm/mercadolivre/authcallbackrouter", name="ecomm_mercadoLivre_authcallbackrouter")
     */
    public function authcallbackrouter(Request $request): RedirectResponse
    {
        $mlCode = $request->query->get('code');
        $mlState = $request->query->get('state');

        $mlCode = $request->query->get('code'); // token_tg
        if (!$mlCode) {
            throw new ViewException('mlCode n/d');
        }
        $mlState = $request->query->get('state'); // clienteConfig.UUID
        if (!$mlState) {
            throw new ViewException('mlState n/d');
        }

        $mlStateDecoded = json_decode(base64_decode($mlState), true);

        if (!($mlStateDecoded['route'] ?? false)) {
            throw new ViewException('mlState.route n/d');
        }
        $route = $mlStateDecoded['route'];
        unset($mlStateDecoded['route']);

        $queryParams = '';
        foreach ($mlStateDecoded as $k => $v) {
            $queryParams .= $k . '=' . $v . '&';
        }
        
        $url = $route . '?' . $queryParams . 'mlCode=' . $mlCode;
        
        return new RedirectResponse($url);
    }

    /**
     * @Route("/ecomm/mercadolivre/endpoint", name="ecomm_mercadoLivre_endpoint")
     */
    public function mercadolivreEndpoint(Request $request, MessageBusInterface $bus): Response
    {
        $r = [];
        $r[] = 'Cliente IP: ' . $request->getClientIp();
        $r[] = 'Host: ' . $request->getHost();
        $r[] = 'Content:';
        $r[] = $request->getContent();
        $r[] = 'Headers';
        $r[] = json_encode($request->headers->all());


        try {
            $bus->dispatch(new MlNotification($request->getContent()));
        } catch (\Exception $e) {
            $this->syslog->err('Erro ao dispatch a MlNotification', $e->getMessage());
        }

        $this->syslog->info('ecomm_mercadoLivre_endpoint', implode(PHP_EOL, $r));
        
        return new Response(implode('<br />', $r));
    }

}