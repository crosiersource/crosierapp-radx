<?php

namespace App\Controller\RH;

use App\Entity\RH\Colaborador;
use App\EntityHandler\RH\ColaboradorEntityHandler;
use App\Form\RH\ColaboradorType;
use App\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ColaboradorController extends FormListController
{

    private UploaderHelper $uploaderHelper;

    /**
     * @required
     * @param ColaboradorEntityHandler $entityHandler
     */
    public function setEntityHandler(ColaboradorEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param UploaderHelper $uploaderHelper
     */
    public function setUploaderHelper(UploaderHelper $uploaderHelper): void
    {
        $this->uploaderHelper = $uploaderHelper;
    }


    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['nome', 'cpf'], 'LIKE', 'str', $params),
        ];
    }


    /**
     *
     * @Route("/rh/colaborador/form/{id}", name="rh_colaborador_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param ParameterBagInterface $parameterBag
     * @param Colaborador|null $colaborador
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function form(Request $request, ParameterBagInterface $parameterBag, Colaborador $colaborador = null)
    {
        $params = [
            'listRoute' => 'rh_colaborador_list',
            'typeClass' => ColaboradorType::class,
            'formView' => 'RH/colaborador_form.html.twig',
            'formRoute' => 'rh_colaborador_form',
            'formPageTitle' => 'Colaborador'
        ];

        if (!$colaborador) {
            $colaborador = new Colaborador();
        }

        /** @var ColaboradorRepository $repoColaborador */
        $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);
        $params['jsonMetadata'] = json_decode($repoColaborador->getJsonMetadata(), true);
        if ($request->get('btnDeletarImagem')) {
            $arquivo = $parameterBag->get('kernel.project_dir') . '/public' . $this->uploaderHelper->asset($colaborador, 'imageFile');
            $colaborador->imageName = null;
            $colaborador->setImageFile(null);
            @unlink($arquivo);
        }
        // Verifique o método handleRequestOnValid abaixo
        return $this->doForm($request, $colaborador, $params);
    }


    /**
     *
     * @Route("/rh/colaborador/list/", name="rh_colaborador_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'rh_colaborador_form',
            'listView' => 'RH/colaborador_list.html.twig',
            'listRoute' => 'rh_colaborador_list',
            'listPageTitle' => 'Colaboradores',
            'listId' => 'colaborador_list'
        ];
        return $this->doListSimpl($request, $params);
    }


    public function handleSerializedList(array &$r): void
    {
        /** @var ColaboradorRepository $repoColaborador */
        $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);
        foreach ($r['data'] as $key => $p) {
            /** @var Colaborador $colaborador */
            $colaborador = $repoColaborador->find($p['e']['id']);
            $r['data'][$key]['e']['imagem1'] = '';
            if ($colaborador->getImageFile()) {
                $r['data'][$key]['e']['imagem1'] = $this->uploaderHelper->asset($colaborador, 'imageFile');
            }
        }
    }


    /**
     *
     * @Route("/rh/colaborador/delete/{id}/", name="rh_colaborador_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Colaborador $colaborador
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Colaborador $colaborador): RedirectResponse
    {
        $params['listRoute'] = 'rh_colaborador_list';
        return $this->doDelete($request, $colaborador, $params);
    }


    /**
     *
     * @Route("/rh/colaborador/findColaboradorByIdNomeTituloJSON/", name="rh_colaborador_findColaboradorByIdNomeTituloJSON")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findColaboradorByIdNomeTituloJSON(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);

            if (ctype_digit($str)) {
                $colaboradors = $repoColaborador->findByFiltersSimpl([['id', 'EQ', $str]]);
            } else {
                $colaboradors = $repoColaborador->findByFiltersSimpl([[['nome', 'jsonData'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            }
            $select2js = Select2JsUtils::toSelect2DataFn($colaboradors, function ($e) {
                /** @var Colaborador $e */
                return ($e->jsonData['titulo'] ?: $e->nome) . ' (' . $e->getId() . ')';
            });
            return new JsonResponse(
                ['results' => $select2js]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }


    /**
     *
     * @Route("/rh/colaborador/findById/{colaborador}", name="rh_colaborador_findById", requirements={"colaborador"="\d+"})
     * @param Colaborador $colaborador
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findById(Colaborador $colaborador): JsonResponse
    {
        try {
            $colaboradorJson = EntityIdUtils::serialize($colaborador);
            return new JsonResponse(
                [
                    'result' => 'OK',
                    'colaborador' => $colaboradorJson
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['result' => 'ERRO']
            );
        }
    }


    /**
     *
     * @Route("/rh/colaborador/findColaboradorByIdOuNome/", name="rh_colaborador_findColaboradorByIdOuNome")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findColaboradorByIdOuNome(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->getDoctrine()->getRepository(Colaborador::class);

            if (ctype_digit($str)) {
                $colaboradors = $repoColaborador->findByFiltersSimpl([['id', 'EQ', $str]]);
            } else {
                $colaboradors = $repoColaborador->findByFiltersSimpl([[['nome'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            }
            $select2js = Select2JsUtils::toSelect2DataFn($colaboradors, function ($e) {
                /** @var Colaborador $e */
                return ($e->jsonData['titulo'] ?: $e->nome) . ' (' . $e->getId() . ')';
            });
            return new JsonResponse(
                ['results' => $select2js]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }

}
