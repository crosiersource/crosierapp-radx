<?php

namespace App\Controller\Financeiro;

use App\Form\Financeiro\CarteiraType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CarteiraEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CarteiraRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * CRUD Controller para Carteira.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CarteiraController extends FormListController
{

    /**
     * @required
     * @param CarteiraEntityHandler $entityHandler
     */
    public function setEntityHandler(CarteiraEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['descricao'], 'LIKE', 'str', $params)
        ];
    }

    /**
     *
     * @Route("/fin/carteira/form2/{id}", name="carteira_form2", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Carteira|null $carteira
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form2(Request $request, Carteira $carteira = null)
    {
        $params = [
            'typeClass' => CarteiraType::class,
            'formView' => 'Financeiro/carteiraForm.html.twig',
            'formRoute' => 'carteira_form',
            'formPageTitle' => 'Carteira'
        ];
        return $this->doForm($request, $carteira, $params);
    }

    /**
     *
     * @Route("/fin/carteira/list2/", name="carteira_list2")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list2(Request $request): Response
    {
        $params = [
            'formRoute' => 'carteira_form',
            'listView' => 'Financeiro/carteiraList.html.twig',
            'listRoute' => 'carteira_list',
            'listRouteAjax' => 'carteira_datatablesJsList',
            'listPageTitle' => 'Carteiras',
            'listId' => 'carteiraList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/carteira/datatablesJsList/", name="carteira_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/carteira/delete/{id}/", name="carteira_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Carteira $carteira
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Carteira $carteira): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $carteira, []);
    }


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
     * @Route("/fin/carteira/form", name="fin_carteira_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Carteira/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    /**
     * @Route("/fin/carteira/list", name="fin_carteira_list")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Carteira/list'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
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