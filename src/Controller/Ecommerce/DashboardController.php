<?php

namespace App\Controller\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class DashboardController extends BaseController
{


    /**
     * @Route("/api/dashboard/tray-e-ml/totaisDeVendasPorPeriodo", name="api_dashboard_totaisDeVendasPorPeriodo")
     */
    public function totaisDeVendasPorPeridoo(Request $request): Response
    {
        $periodo = $request->get('periodo');
        
        $msg = 'mesano';

        $dtIni = DateTimeUtils::parseDateStr($periodo[0]);
        $dtFim = DateTimeUtils::parseDateStr($periodo[1]);

        $primeiroDiaMes = DateTimeUtils::getPrimeiroDiaMes($dtIni);
        $ultimoDiaMes = DateTimeUtils::getUltimoDiaMes($dtIni);
        
        $ehMesAtualeCompleto = DateTimeUtils::ehMesmoDia($dtIni, $primeiroDiaMes) &&
            DateTimeUtils::ehMesmoDia($dtFim, $ultimoDiaMes);

        $ehMesCorrente = $dtIni->format('mm/YYYY') === (new \DateTime())->format('mm/YYYY'); 

        $mostrarEmDias = DateTimeUtils::diffInDias($dtFim, $dtIni) < 62;

        $group = $mostrarEmDias ? '%Y-%m-%d' : '%Y-%m';

        $sql = "
            SELECT 
                DATE_FORMAT(v.dt_venda, :group) as dt,
                sum(valor_total) AS valor_total
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim
            AND v.status NOT LIKE '%CANCELADO%'
            GROUP BY DATE_FORMAT(v.dt_venda, :group)
            ORDER BY DATE_FORMAT(v.dt_venda, :group)
        ";


        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsTotal = $conn->fetchAllAssociative($sql, [
            'group' => $group,
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);

        if ($mostrarEmDias) {
            $msg = 'mostrarEmDias';
            $somatorio = 0.0;
            foreach ($rsTotal as $k => $r) {
                $rsTotal[$k]['somatorio'] = $somatorio += $r['valor_total'];
            }
        }

        if ($ehMesAtualeCompleto) {
            $msg = 'ehMesAtualeCompleto';
            $numUltimoDiaMes = (int)$ultimoDiaMes->format('d');
            $mes = $ultimoDiaMes->format('m');
            $ano = $ultimoDiaMes->format('Y');

            
            if ($ehMesCorrente) {
                $media = bcdiv($somatorio, DateTimeUtils::diffInDias(new \DateTime(), $dtIni) + 1, 2);
            } else {
                $media = bcdiv($somatorio, $numUltimoDiaMes, 2);
            }
            
            function getValorTotal($dt, $rsTotal) {
                foreach ($rsTotal as $r) {
                    if (DateTimeUtils::ehMesmoDia(DateTimeUtils::parseDateStr($r['dt']), $dt)) {
                        return $r['valor_total'];
                    }  
                }
                return 0;
            }
            $rsComProjecao = [];
            $somatorio = 0;
            $valorTotal = 0;
            $projecao = 0;
            $metaAcum = 0;
            $metaMensal = $this->getMetaMensal($dtIni);
            $meta = bcdiv($metaMensal, $numUltimoDiaMes, 2);
            for ($i=1 ; $i<=$numUltimoDiaMes ; $i++) {
                $dt = DateTimeUtils::parseDateStr($i . '/' . $mes . '/' . $ano);
                $valorTotal = getValorTotal($dt, $rsTotal);
                $somatorio += $valorTotal;
                $rsComProjecao[] = [
                    'dt' => $dt->format('Y-m-d'),
                    'valor_total' => $valorTotal,
                    'somatorio' => $somatorio,
                    'projecao' => $projecao += $media,
                    'meta' => $metaAcum += $meta,
                ];
            }
            $rsTotal = $rsComProjecao;
        }


        return CrosierApiResponse::success($rsTotal, $msg);
    }
    
    private function getMetaMensal(\DateTime $mesAno): float {
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        /** @var AppConfig $appConfig */
        $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'conecta.metaVendasGlobalMensal'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
        
        $json = json_decode($appConfig->valor, true);
        
        $fMesAno = $mesAno->format('m-Y');
        
        return $json[$fMesAno] ?? 180000.0;
    }

    /**
     * @Route("/api/dashboard/melhoresVendedores", name="api_dashboard_melhoresVendedores")
     */
    public function melhoresVendedores(Request $request): Response
    {
        $periodo = $request->get('periodo');

        $dtIni = DateTimeUtils::parseDateStr($periodo[0]);
        $dtFim = DateTimeUtils::parseDateStr($periodo[1]);

        $sqlMelhoresVendedores = "
            SELECT sum(valor_total) AS valor_total,
                   cliente_config_id,
                   nome
            FROM ecomm_tray_venda v,
                 ecomm_cliente_config config,
                 crm_cliente cli
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
              AND v.cliente_config_id = config.id
              AND config.cliente_id = cli.id 
              AND v.status NOT LIKE '%CANCELADO%'
              AND cli.ativo 
            GROUP BY cliente_config_id,
                     nome
            ORDER BY valor_total DESC
            LIMIT 15
        ";

        $sqlTotais = "
            SELECT sum(valor_total) as total_vendido
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
            AND v.status NOT LIKE '%CANCELADO%'
        ";

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsMelhoresVendedores = $conn->fetchAllAssociative($sqlMelhoresVendedores, [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);
        $rsTotal = $conn->fetchAssociative($sqlTotais, [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);

        foreach ($rsMelhoresVendedores as $k => $v) {
            $rsMelhoresVendedores[$k]['valor_total'] = (float)$rsMelhoresVendedores[$k]['valor_total'];
            $rsMelhoresVendedores[$k]['porcent'] = (float)bcmul(bcdiv($v['valor_total'], $rsTotal['total_vendido'], 4), 100, 2);
        }

        return CrosierApiResponse::success($rsMelhoresVendedores);
    }


    /**
     * @Route("/api/dashboard/melhoresPointSales", name="api_dashboard_melhoresPointSales")
     */
    public function melhoresPointSales(Request $request): Response
    {
        $periodo = $request->get('periodo');

        $dtIni = DateTimeUtils::parseDateStr($periodo[0]);
        $dtFim = DateTimeUtils::parseDateStr($periodo[1]);

        $sql = "
            SELECT sum(valor_total) AS valor_total,
                   point_sale
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
            AND v.status NOT LIKE '%CANCELADO%'
            GROUP BY point_sale
            ORDER BY valor_total DESC
        ";

        $sqlTotais = "
            SELECT sum(valor_total) as total_vendido
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
            AND v.status NOT LIKE '%CANCELADO%'
        ";

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsMelhores = $conn->fetchAllAssociative($sql, [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);
        $rsTotal = $conn->fetchAssociative($sqlTotais, [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);

        foreach ($rsMelhores as $k => $v) {
            $rsMelhores[$k]['valor_total'] = (float)$rsMelhores[$k]['valor_total'];
            $rsMelhores[$k]['porcent'] = (float)bcmul(bcdiv($v['valor_total'], $rsTotal['total_vendido'], 4), 100, 2);
        }

        return CrosierApiResponse::success($rsMelhores);
    }


    /**
     * @Route("/api/dashboard/totalizacoesGerais", name="api_dashboard_totalizacoesGerais")
     */
    public function totalizacoesGerais(Request $request): Response
    {
        $periodo = $request->get('periodo');

        $dtIni = DateTimeUtils::parseDateStr($periodo[0]);
        $dtFim = DateTimeUtils::parseDateStr($periodo[1]);

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $rsTotalGeral = $conn->fetchAssociative(
            "SELECT sum(valor_total) AS valor_total
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
            AND v.status NOT LIKE '%CANCELADO%'
            ", [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);

        $rsQtdeVendas = $conn->fetchAssociative(
            "SELECT count(*) AS qtde_vendas
            FROM ecomm_tray_venda v
            WHERE v.dt_venda BETWEEN :dtIni AND :dtFim 
            AND v.status NOT LIKE '%CANCELADO%'
            ", [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);

        $rsQtdePerguntas = $conn->fetchAssociative(
            "SELECT count(*) AS qtde_perguntas
            FROM ecomm_ml_pergunta
            WHERE dt_pergunta BETWEEN :dtIni AND :dtFim
            ", [
            'dtIni' => $dtIni->format('Y-m-d'),
            'dtFim' => $dtFim->format('Y-m-d'),
        ]);


        $data = [
            'totalGeral' => (float)$rsTotalGeral['valor_total'],
            'qtdeVendas' => (float)$rsQtdeVendas['qtde_vendas'],
            'qtdePerguntas' => (float)$rsQtdePerguntas['qtde_perguntas'],
        ];


        return CrosierApiResponse::success($data);
    }


}
