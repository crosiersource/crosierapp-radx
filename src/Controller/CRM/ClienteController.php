<?php

namespace App\Controller\CRM;


use App\Form\CRM\ClienteType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD Controller para Cliente.
 *
 * @package App\Controller\CRM
 * @author Carlos Eduardo Pauluk
 */
class ClienteController extends FormListController
{

    private AppConfigEntityHandler $appConfigEntityHandler;

    /**
     * @required
     * @param ClienteEntityHandler $entityHandler
     */
    public function setEntityHandler(ClienteEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param AppConfigEntityHandler $appConfigEntityHandler
     */
    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['documento'], 'LIKE', 'documento', $params),
            new FilterData(['nome'], 'LIKE', 'nome', $params)
        ];
    }

    /**
     *
     * @Route("/crm/cliente/form/{id}", name="crm_cliente_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Cliente|null $cliente
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function form(Request $request, Cliente $cliente = null)
    {
        $params = [
            'typeClass' => ClienteType::class,
            'formView' => 'CRM/cliente_form.html.twig',
            'formRoute' => 'crm_cliente_form',
        ];

        /** @var ClienteRepository $repoProduto */
        $repoCliente = $this->getDoctrine()->getRepository(Cliente::class);
        $params['jsonMetadata'] = json_decode($repoCliente->getJsonMetadata(), true);

        $params['enderecoTipos'] = json_encode(Select2JsUtils::arrayToSelect2Data($params['jsonMetadata']['enderecoTipos'] ?? []));
        $endereco = $request->get('endereco');
        if ((strlen($endereco['logradouro'] ?? '') > 0) ||
            (strlen($endereco['bairro'] ?? '') > 0) ||
            (strlen($endereco['cidade'] ?? '') > 0) ||
            (strlen($endereco['estado'] ?? '') > 0) ||
            (strlen($endereco['cep'] ?? '') > 0)) {

            if (!$this->isCsrfTokenValid('tokenSalvarEndereco', $request->request->get('tokenSalvarEndereco'))) {
                $this->addFlash('error', 'Não foi possível salvar o endereço. Token inválido.');
            }

            $endereco['tipo'] = is_array($endereco['tipo'] ?? null) ? implode(',', $endereco['tipo']) : ($endereco['tipo'] ?? '');

            foreach ($endereco as $k => $v) {
                $endereco[$k] = strtoupper($v);
            }
            if ($endereco['i'] >= 0) {
                $cliente->jsonData['enderecos'][$endereco['i']] = $endereco;
            } else {
                $cliente->jsonData['enderecos'][] = $endereco;
            }
            unset($cliente->jsonData['enderecos'][$endereco['i']]['i']);
            $cliente->jsonData['enderecos'] = array_values($cliente->jsonData['enderecos']);
            $this->entityHandler->save($cliente);

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
                /** @var AppConfig $clienteJsonMetadata */
                $clienteJsonMetadata = $repoAppConfig->findOneBy(
                    [
                        'appUUID' => $_SERVER['CROSIERAPP_UUID'],
                        'chave' => 'crm_cliente_json_metadata'
                    ]);
                $valor = json_decode($clienteJsonMetadata->getValor(), true);
                ksort($params['jsonMetadata']['enderecoTipos']);
                $valor['enderecoTipos'] = $params['jsonMetadata']['enderecoTipos'];
                $clienteJsonMetadata->setValor(json_encode($valor));
                $this->appConfigEntityHandler->save($clienteJsonMetadata);
            }

            $this->addFlash('success', 'Endereço salvo com sucesso');
            return $this->redirectToRoute('crm_cliente_form', ['id' => $cliente->getId(), '_fragment' => 'enderecos']);
        }

        return $this->doForm($request, $cliente, $params);
    }

    /**
     *
     * @Route("/crm/cliente/list/", name="crm_cliente_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'crm_cliente_form',
            'listView' => 'CRM/cliente_list.html.twig',
            'listRoute' => 'crm_cliente_list',
            'listRouteAjax' => 'crm_cliente_datatablesJsList',
            'listPageTitle' => 'Clientes',
            'listId' => 'cliente_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/crm/cliente/datatablesJsList/", name="crm_cliente_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/crm/cliente/delete/{id}/", name="crm_cliente_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Cliente $cliente
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_CRM_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Cliente $cliente): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $cliente, []);
    }

    /**
     *
     * @Route("/crm/cliente/deleteEndereco/{cliente}/{i}", name="crm_cliente_deleteEndereco", requirements={"cliente"="\d+", "i"="\d+"})
     * @param Request $request
     * @param Cliente $cliente
     * @param int $i
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function deleteEndereco(Request $request, Cliente $cliente, int $i): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->isCsrfTokenValid('crm_cliente_deleteEndereco', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                unset($cliente->jsonData['enderecos'][$i]);
                $cliente->jsonData['enderecos'] = array_values($cliente->jsonData['enderecos']); // resetar índices para iniciar em 0 novamente
                $this->entityHandler->save($cliente);
                $this->addFlash('success', 'Endereço deletado com sucesso.');
            } catch (\Exception $e) {
                if ($e instanceof ViewException) {
                    $this->addFlash('error', $e->getMessage());
                }
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }
        return $this->redirectToRoute('crm_cliente_form', ['id' => $cliente->getId(), '_fragment' => 'enderecos']);
    }


    /**
     *
     * @Route("/crm/cliente/findClienteByStr/", name="crm_cliente_findClienteByStr")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function findClienteByStr(Request $request): JsonResponse
    {
        $str = $request->get('term') ?? '';

        $rs = $this->entityHandler->getDoctrine()->getConnection()
            ->fetchAll('SELECT id, documento, nome, json_data FROM crm_cliente WHERE documento = :documento OR nome LIKE :nome LIMIT 30',
                [
                    'documento' => preg_replace("/[^0-9]/", "", $str),
                    'nome' => '%' . $str . '%'
                ]);

        $clientes = [];

        foreach ($rs as $r) {
            $clientes[] = [
                'id' => $r['id'],
                'text' => StringUtils::mascararCnpjCpf($r['documento']) . ' - ' . $r['nome'],
                'nome' => $r['nome'],
                'json_data' => json_decode($r['json_data'], true)
            ];
        }

        return new JsonResponse(
            ['results' => $clientes]
        );
    }


    /**
     *
     * @Route("/crm/cliente/findClienteByDocumento/", name="crm_cliente_findClienteByDocumento")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function findClienteByDocumento(Request $request): JsonResponse
    {
        $str = $request->get('term') ?? '';

        $rs = $this->entityHandler->getDoctrine()->getConnection()
            ->fetchAllAssociative('SELECT id, documento, nome, json_data FROM crm_cliente WHERE documento = :documento LIMIT 1',
                [
                    'documento' => preg_replace("/[^G^0-9]/", "", $str),
                ]);

        $clientes = [];

        if ($rs[0]['documento'] ?? false) {
            $clientes[] = [
                'id' => $rs[0]['id'],
                'text' => $rs[0]['nome'],
                'json_data' => json_decode($rs[0]['json_data'], true)
            ];
        }

        return new JsonResponse(
            ['results' => $clientes]
        );
    }


}