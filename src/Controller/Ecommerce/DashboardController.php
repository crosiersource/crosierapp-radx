<?php

namespace App\Controller\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class DashboardController extends BaseController
{

    
    /**
     * @Route("/api/dashboard/melhoresVendedores", name="api_dashboard_melhoresVendedores")
     */
    public function dashboard(): Response
    {

        $sqlMelhoresVendedores = "
            SELECT sum(valor_total) AS valor_total,
                   cliente_config_id,
                   nome
            FROM ecomm_tray_venda v,
                 ecomm_cliente_config config,
                 crm_cliente cli
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 3 MONTH)
              AND v.cliente_config_id = config.id
              AND config.cliente_id = cli.id 
              AND v.status NOT LIKE '%CANCELADO%'
            GROUP BY cliente_config_id,
                     nome
            ORDER BY valor_total DESC
            LIMIT 15
        ";

        $sqlTotais = "
            SELECT sum(valor_total) as total_vendido
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 3 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
        ";

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsMelhoresVendedores = $conn->fetchAllAssociative($sqlMelhoresVendedores);
        $rsTotal = $conn->fetchAssociative($sqlTotais);

        foreach ($rsMelhoresVendedores as $k => $v) {
            $rsMelhoresVendedores[$k]['valor_total'] = (float)$rsMelhoresVendedores[$k]['valor_total'];
            $rsMelhoresVendedores[$k]['porcent'] = (float)bcmul(bcdiv($v['valor_total'], $rsTotal['total_vendido'], 4), 100, 2);
        }

        return CrosierApiResponse::success($rsMelhoresVendedores);
    }


    /**
     * @Route("/api/dashboard/melhoresPointSales", name="api_dashboard_melhoresPointSales")
     */
    public function melhoresPointSales(): Response
    {

        $sql = "
            SELECT sum(valor_total) AS valor_total,
                   point_sale
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 3 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
            GROUP BY point_sale
            ORDER BY valor_total DESC
        ";

        $sqlTotais = "
            SELECT sum(valor_total) as total_vendido
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 3 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
        ";

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsMelhores = $conn->fetchAllAssociative($sql);
        $rsTotal = $conn->fetchAssociative($sqlTotais);

        foreach ($rsMelhores as $k => $v) {
            $rsMelhores[$k]['valor_total'] = (float)$rsMelhores[$k]['valor_total'];
            $rsMelhores[$k]['porcent'] = (float)bcmul(bcdiv($v['valor_total'], $rsTotal['total_vendido'], 4), 100, 2);
        }

        return CrosierApiResponse::success($rsMelhores);
    }


    /**
     * @Route("/api/dashboard/totaisDeVendasUltimos12Meses", name="api_dashboard_totaisDeVendasUltimos12Meses")
     */
    public function totaisDeVendasUltimos12Meses(): Response
    {

        $sql = "
            SELECT sum(valor_total) AS valor_total
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 12 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
            GROUP BY DATE_FORMAT(v.dt_venda, '%Y-%m')
            ORDER BY DATE_FORMAT(v.dt_venda, '%Y-%m')
        ";


        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsTotal = $conn->fetchAllAssociative($sql);
        $data = [];
        foreach ($rsTotal as $r) {
            $data[] = (float)$r['valor_total'];
        }

        return CrosierApiResponse::success($data);
    }

    /**
     * @Route("/api/dashboard/totalizacoesGerais", name="api_dashboard_totalizacoesGerais")
     */
    public function totalizacoesGerais(): Response
    {
        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsTotalGeral = $conn->fetchAssociative(
            "SELECT sum(valor_total) AS valor_total
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 12 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
            ");

        $rsQtdeVendas = $conn->fetchAssociative(
            "SELECT count(*) AS qtde_vendas
            FROM ecomm_tray_venda v
            WHERE v.dt_venda > DATE_SUB(NOW(),INTERVAL 12 MONTH)
            AND v.status NOT LIKE '%CANCELADO%'
            ");

        $rsQtdePerguntas = $conn->fetchAssociative(
            "SELECT count(*) AS qtde_perguntas
            FROM ecomm_ml_pergunta
            WHERE dt_pergunta > DATE_SUB(NOW(),INTERVAL 12 MONTH)
            ");


        $data = [
            'totalGeral' => (float)$rsTotalGeral['valor_total'],
            'qtdeVendas' => (float)$rsQtdeVendas['qtde_vendas'],
            'qtdePerguntas' => (float)$rsQtdePerguntas['qtde_perguntas'],
        ];


        return CrosierApiResponse::success($data);
    }


}
