<?php


namespace App\Controller\Ecommerce;

use App\Business\Ecommerce\MercadoLivreBusiness;
use App\Entity\Ecommerce\MercadoLivrePergunta;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
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


}
