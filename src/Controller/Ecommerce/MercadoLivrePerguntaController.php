<?php


namespace App\Controller\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\IntegradorMercadoLivre;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\MercadoLivreBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Ecommerce\MercadoLivrePergunta;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivrePerguntaController extends BaseController
{

    /**
     * @Route("/ecommerce/mercadoLivrePergunta/list", name="ecommerce_mercadoLivrePergunta_list")
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Ecommerce/MercadoLivrePergunta/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/api/ecommerce/mercadoLivrePergunta/responder/{id}", name="ecommerce_mercadoLivrePergunta_responder", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function responder(Request $request, MercadoLivreBusiness $business, MercadoLivrePergunta $mercadoLivrePergunta): JsonResponse
    {
        $resposta = $request->get('resposta');
        $business->responder($mercadoLivrePergunta, $resposta);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }

    /**
     * @Route("/api/ecommerce/mercadoLivrePergunta/getQuestionsGlobal", name="ecommerce_mercadoLivrePergunta_getQuestionsGlobal")
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     */
    public function getQuestionsGlobal(MercadoLivreBusiness $business): JsonResponse
    {
        $business->atualizar();

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @Route("/api/ecommerce/mercadoLivrePergunta/atualizarPergunta/{id}", name="ecommerce_mercadoLivrePergunta_atualizarPergunta", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ECOMM", statusCode=403)
     */
    public function atualizarPergunta(MercadoLivreBusiness $business, MercadoLivrePergunta $mercadoLivrePergunta): JsonResponse
    {
        $business->atualizarPergunta($mercadoLivrePergunta);

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }



    /**
     * @Route("/ecommerce/mercadolivre/authcallback", name="ecommerce_mercadolivre_authcallback")
     */
    public function authcallback(ClienteConfigEntityHandler $clienteConfigEntityHandler, Connection $conn, Request $request)
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

        $this->syslog->setApp('conecta')->setComponent(self::class);
        $this->syslog->info('ecommerce_mercadolivre_authcallback', implode(PHP_EOL, $r));

        $uuid = $request->get('state');
        $ml_code = $request->get('code');
        $rs = $conn->fetchAssociative('SELECT id FROM cnct_cliente_config WHERE uuid = :uuid', ['uuid' => $uuid]);
        if ($rs['id'] ?? false) {
            $repoClienteConfig = $this->getDoctrine()->getRepository(ClienteConfig::class);
            $clienteConfig = $repoClienteConfig->find($rs['id']);
            $clienteConfig->jsonData['mercadolivre']['token_tg'] = $ml_code;
            $clienteConfigEntityHandler->save($clienteConfig);
            return $this->redirectToRoute('ecommerce_clienteConfig_form', ['id' => $rs['id']]);
        } // else

        return new Response(implode('<br />', $r));
    }


}
