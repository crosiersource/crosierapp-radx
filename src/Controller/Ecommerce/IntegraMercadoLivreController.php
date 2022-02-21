<?php

namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\MercadoLivreBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\ClienteConfig;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\MercadoLivreItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Ecommerce\MercadoLivreItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Messenger\Ecommerce\Message\MlNotification;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
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
        
        $mailDests = $mlStateDecoded['mailDests'] ?? null;
        if ($mailDests) {
            $nomeCliente = $mlStateDecoded['nomeCliente'] ?? null;
            $mailDests = explode(',', $mailDests);
            $body = 'O cliente ' . $nomeCliente . ' reativou o Crosier no MercadoLivre. Certifique-se de que está logado no Crosier e ' .
                'clique <a href="'. $url . '">aqui</a> para finalizar a configuração.';
            
            $email = (new Email())
                ->from('mailer@crosier.com.br')
                ->subject('Ativação de clientes ML' . ($nomeCliente ? ' (' . $nomeCliente . ')' : ''))
                ->html($body);
            
            foreach ($mailDests as $mailDest) {
                $email->addTo($mailDest);
            }
            
            $mailer->send($email);
        }

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


    /**
     * @Route("/corrigir", name="corrigir")
     */
    public function corrigir(Connection $conn, MercadoLivreBusiness $biz): Response
    {
        try {
            $itens = $this->getDoctrine()->getRepository(MercadoLivreItem::class)->findAll();
            foreach ($itens as $item) {
                if ($item->mercadolivreUserId) {
                    continue;
                }
                if (!($item->clienteConfig->jsonData['mercadolivre'][0]['me'] ?? false)) {
                    $biz->handleAccessToken($item->clienteConfig, 0);
                }
                if (!$item->mercadolivreUserId) {
                    if (count($item->clienteConfig->jsonData['mercadolivre']) > 1) {
                        $true = 1;
                    }
                    $conn->update('ecomm_ml_item', [
                        'mercadolivre_user_id' => $item->clienteConfig->jsonData['mercadolivre'][0]['me']['id'] 
                    ], ['id' => $item->getId()]);
                }
            }
        } catch (ViewException $e) {
            $erro = 1;
        }
        return new Response('ok');

    }

}