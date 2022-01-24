<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CarteiraRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Carlos Eduardo Pauluk
 */
class CarteiraController extends BaseController
{


    /**
     *
     * @Route("/fin/carteira/select2json", name="carteira_select2json")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN", statusCode=403))
     */
    public function carteiraSelect2json(Request $request): Response
    {
        $fds = [];
        if ($request->get('cheque')) {
            $fds[] = FilterData::fromArray(['cheque', 'EQ', true]);
        }
        if ($request->get('caixa')) {
            $fds[] = FilterData::fromArray(['caixa', 'EQ', true]);
        }
        if ($request->get('cartao')) {
            $fds[] = FilterData::fromArray(['carteira', 'IS_NOT_NULL']);
        }
        if (!$request->get('todas')) {
            $fds[] = FilterData::fromArray(['atual', 'EQ', true]);
        }

        /** @var CarteiraRepository $carteiraRepo */
        $carteiraRepo = $this->getDoctrine()->getRepository(Carteira::class);
        $itens = $carteiraRepo->findByFilters($fds, ['e.codigo' => 'ASC'], 0, 0);

        $rs = array();
        /** @var Carteira $item */
        foreach ($itens as $item) {
            $r['id'] = $item->getId();
            $r['text'] = $item->getDescricaoMontada();
            // Adiciono estes campos para no casos de movimentacaoForm, onde os campos do cheque devem ser preenchidos no onChange da carteira
            $r['bancoId'] = $item->banco ? $item->banco->getId() : null;
            $r['agencia'] = $item->agencia;
            $r['conta'] = $item->conta;
            $r['cheque'] = $item->cheque;
            $r['caixa'] = $item->caixa;
            $rs[] = $r;
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($rs, 'json');

        return new Response($json);
    }

    /**
     * @Route("/fin/carteira/caixaOperacaoForm", name="fin_carteira_caixaOperacaoForm")
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function caixaOperacaoForm(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Carteira/caixaOperacaoForm'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }


}