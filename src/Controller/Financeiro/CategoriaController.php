<?php

namespace App\Controller\Financeiro;

use App\Form\Financeiro\CategoriaType;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\CategoriaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * CRUD Controller para Categoria.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CategoriaController extends FormListController
{


    /**
     * @Route("/fin/categoria/form", name="fin_categoria_form")
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(): Response
    {
        $params = [
            'jsEntry' => 'Financeiro/Categoria/form'
        ];
        return $this->doRender('@CrosierLibBase/vue-app-page.html.twig', $params);
    }

    
    /**
     *
     * @Route("/fin/categoria/select2json", name="categoria_select2json")
     * @param Request $request
     * @return Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function categoriaSelect2json(Request $request): Response
    {
        $params = [];

        // regra: para TRANSF_PROPRIA, a categoria deve ser sempre 299
        if ($request->get('tipoLancto') === 'TRANSF_PROPRIA') {
            $params['codigo'] = 299;
        }


        $somenteFolhas = filter_var($request->get('somenteFolhas'), FILTER_VALIDATE_BOOLEAN);

        $itens = $this->getDoctrine()->getRepository(Categoria::class)->findBy($params, ['codigoOrd' => 'ASC']);

        $rs = array();
        /** @var Categoria $item */
        foreach ($itens as $item) {
            $r = [];
            $r['id'] = $item->getId();
            $r['text'] = $item->getDescricaoMontada();
            if ($somenteFolhas and $item->getSubCategs() and !$item->getSubCategs()->isEmpty()) {
                $r['disabled'] = true;
            }
            $rs[] = $r;
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($rs, 'json');

        return new Response($json);
    }


}