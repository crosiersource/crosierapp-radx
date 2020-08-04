<?php

namespace App\Controller\Estoque;

use App\Form\Estoque\FornecedorType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\FornecedorEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class FornecedorController extends FormListController
{

    private AppConfigEntityHandler $appConfigEntityHandler;

    /**
     * @required
     * @param AppConfigEntityHandler $appConfigEntityHandler
     */
    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }

    /**
     * @required
     * @param FornecedorEntityHandler $entityHandler
     */
    public function setEntityHandler(FornecedorEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['nome', 'nomeFantasia', 'documento'], 'LIKE', 'str', $params),
        ];
    }

    /**
     *
     * @Route("/est/fornecedor/form/{id}", name="est_fornecedor_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Fornecedor|null $fornecedor
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function form(Request $request, Fornecedor $fornecedor = null)
    {
        $params = [
            'typeClass' => FornecedorType::class,
            'formView' => 'Estoque/fornecedor_form.html.twig',
            'formRoute' => 'est_fornecedor_form',
        ];

        /** @var FornecedorRepository $repoProduto */
        $repoFornecedor = $this->getDoctrine()->getRepository(Fornecedor::class);
        $params['jsonMetadata'] = json_decode($repoFornecedor->getJsonMetadata(), true);

        $params['enderecoTipos'] = json_encode(Select2JsUtils::arrayToSelect2Data($params['jsonMetadata']['enderecoTipos'] ?? []));

        if (($endereco = $request->get('endereco')) && ($endereco['logradouro'] ?? false)) {
            if (!$this->isCsrfTokenValid('tokenSalvarEndereco', $request->request->get('tokenSalvarEndereco'))) {
                $this->addFlash('error', 'Não foi possível salvar o endereço. Token inválido.');
            }

            $endereco['tipo'] = is_array($endereco['tipo'] ?? null) ? implode(',', $endereco['tipo']) : ($endereco['tipo'] ?? '');

            foreach ($endereco as $k => $v) {
                $endereco[$k] = strtoupper($v);
            }
            if ($endereco['i'] >= 0) {
                $fornecedor->jsonData['enderecos'][$endereco['i']] = $endereco;
            } else {
                $fornecedor->jsonData['enderecos'][] = $endereco;
            }
            unset($fornecedor->jsonData['enderecos'][$endereco['i']]['i']);
            $fornecedor->jsonData['enderecos'] = array_values($fornecedor->jsonData['enderecos']);
            $this->entityHandler->save($fornecedor);

            $tiposArr = explode(',', $endereco['tipo']);
            $alterarEnderecosTipos = false;
            foreach ($tiposArr as $tipoEndereco) {
                if (!in_array($tipoEndereco, ($params['jsonMetadata']['enderecoTipos'] ?? []))) {
                    $alterarEnderecosTipos = true;
                    $params['jsonMetadata']['enderecoTipos'][$tipoEndereco] = $tipoEndereco;
                }
            }
            if ($alterarEnderecosTipos) {
                /** @var AppConfigRepository $repoAppConfig */
                $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $fornecedorJsonMetadata */
                $fornecedorJsonMetadata = $repoAppConfig->findOneBy(
                    [
                        'appUUID' => $_SERVER['CROSIERAPP_UUID'],
                        'chave' => 'est_fornecedor_json_metadata'
                    ]);
                $valor = json_decode($fornecedorJsonMetadata->getValor(), true);
                ksort($params['jsonMetadata']['enderecoTipos']);
                $valor['enderecoTipos'] = $params['jsonMetadata']['enderecoTipos'];
                $fornecedorJsonMetadata->setValor(json_encode($valor));
                $this->appConfigEntityHandler->save($fornecedorJsonMetadata);
            }

            $this->addFlash('success', 'Endereço salvo com sucesso');
            return $this->redirectToRoute('est_fornecedor_form', ['id' => $fornecedor->getId(), '_fragment' => 'enderecos']);
        }

        return $this->doForm($request, $fornecedor, $params);
    }

    /**
     *
     * @Route("/est/fornecedor/list/", name="est_fornecedor_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_fornecedor_form',
            'listView' => '@CrosierLibBase/list.html.twig',
            'listJS' => 'Estoque/fornecedor_list.js',
            'listRoute' => 'est_fornecedor_list',
            'listRouteAjax' => 'est_fornecedor_datatablesJsList',
            'listPageTitle' => 'Fornecedores',
            'deleteRoute' => 'est_fornecedor_delete',
            'listId' => 'fornecedor_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/est/fornecedor/datatablesJsList/", name="est_fornecedor_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/est/fornecedor/delete/{id}/", name="est_fornecedor_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Fornecedor $fornecedor
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Fornecedor $fornecedor): RedirectResponse
    {
        $params['listRoute'] = 'est_fornecedor_list';
        return $this->doDelete($request, $fornecedor, $params);
    }

    /**
     *
     * @Route("/est/fornecedor/findByStr", name="est_fornecedor_findByStr")
     * @param Request $request
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findByStr(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var FornecedorRepository $repoFornecedor */
            $repoFornecedor = $this->getDoctrine()->getRepository(Fornecedor::class);

            $fornecedores = $repoFornecedor->findByFiltersSimpl([[['nome', 'nomeFantasia'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            $select2js = Select2JsUtils::toSelect2DataFn($fornecedores, function ($e) {
                /** @var Fornecedor $e */
                return $e->nome . ' [Id.: ' . str_pad($e->getId(), 7, '0', STR_PAD_LEFT) . ']';
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
