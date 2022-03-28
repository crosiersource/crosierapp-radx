<?php

namespace App\Controller\CRM;


use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class ClienteController extends FormListController
{

    /**
     * @required
     * @param ClienteEntityHandler $entityHandler
     */
    public function setEntityHandler(ClienteEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }


    /**
     * @Route("/crm/cliente/findClienteByStr/", name="crm_cliente_findClienteByStr")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function findClienteByStr(Request $request): JsonResponse
    {
        $str = $request->get('term') ?? '';

        $rs = $this->entityHandler->getDoctrine()->getConnection()
            ->fetchAllAssociative('SELECT id, documento, nome, json_data FROM crm_cliente WHERE documento = :documento OR nome LIKE :nome LIMIT 30',
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
     * @Route("/crm/cliente/findClienteByDocumento/", name="crm_cliente_findClienteByDocumento")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
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


    /**
     * @Route("/api/est/cliente/findProxCodigo/", name="api_est_cliente_findProxCodigo")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_CRM", statusCode=403)
     */
    public function findProxCodigo(Connection $conn): JsonResponse
    {
        try {
            $rsProxCodigo = $conn->fetchAssociative('SELECT max(codigo)+1 as prox FROM crm_cliente WHERE codigo < 2147483647');
            $proxCodigo = $rsProxCodigo['prox'] ?? 1;
            return CrosierApiResponse::success(['prox' => (string)$proxCodigo]);
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


}