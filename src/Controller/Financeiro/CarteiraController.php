<?php

namespace App\Controller\Financeiro;

use App\Entity\Financeiro\Carteira;
use App\EntityHandler\Financeiro\CarteiraEntityHandler;
use App\Form\Financeiro\CarteiraType;
use App\Repository\Financeiro\CarteiraRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
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
            new FilterData(['descricao'], 'LIKE', 'descricao', $params)
        ];
    }

    /**
     *
     * @Route("/carteira/form/{id}", name="carteira_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Carteira|null $carteira
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, Carteira $carteira = null)
    {
        $params = [
            'typeClass' => CarteiraType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'carteira_form',
            'formPageTitle' => 'Carteira'
        ];
        return $this->doForm($request, $carteira, $params);
    }

    /**
     *
     * @Route("/carteira/list/", name="carteira_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'carteira_form',
            'listView' => 'carteiraList.html.twig',
            'listRoute' => 'carteira_list',
            'listRouteAjax' => 'carteira_datatablesJsList',
            'listPageTitle' => 'Carteiras',
            'listId' => 'carteiraList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/carteira/datatablesJsList/", name="carteira_datatablesJsList")
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
     * @Route("/carteira/delete/{id}/", name="carteira_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Carteira $carteira
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Carteira $carteira): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $carteira);
    }


    /**
     *
     * @Route("/carteira/select2json", name="carteira_select2json")
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
            $r['bancoId'] = $item->getBanco() ? $item->getBanco()->getId() : null;
            $r['agencia'] = $item->getAgencia();
            $r['conta'] = $item->getConta();
            $r['cheque'] = $item->getCheque();
            $r['caixa'] = $item->getCaixa();
            $rs[] = $r;
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($rs, 'json');

        return new Response($json);
    }


}