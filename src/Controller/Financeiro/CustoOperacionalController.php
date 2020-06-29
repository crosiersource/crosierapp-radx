<?php

namespace App\Controller\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CustoOperacionalController extends BaseController
{

    /**
     *
     * @Route("/fin/custoOperacional/relatorioMensal", name="fin_custoOperacional_relatorioMensal")
     * @param Request $request
     * @param Modo $modo
     * @return Response
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Connection $conn): Response
    {
        $dtIni = DateTimeUtils::parseDateStr($request->get('dtIni'));
        $dtFim = DateTimeUtils::parseDateStr($request->get('dtFim'));

        // As vendas são calculadas com um mês a menos
        $dtIni_anterior = DateTimeUtils::incMes($dtIni, -1);
        $dtFim_anterior = DateTimeUtils::incMes($dtFim, -1);
        $params['dts_vendas'] = 'Entre ' . $dtIni_anterior->format('d/m/Y') . ' e ' . $dtFim_anterior->format('t/m/Y');
        $dtIni_anterior = $dtIni_anterior->format('Y-m-d');
        $dtFim_anterior = $dtFim_anterior->format('Y-m-t');

        $params['dts'] = 'Entre ' . $dtIni->format('d/m/Y') . ' e ' . $dtFim->format('d/m/Y');
        $dtIni = $dtIni->format('Y-m-d');
        $dtFim = $dtFim->format('Y-m-d');


        $params['mascara'] = '0.00.000.000.0000.00000';

        $params['rTotalCustosFixos'] = $conn->fetchAll('select c.codigo, c.descricao, sum(m.valor_total) as valor_total
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND 
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'201___\' 
                    GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIni, 'dtFim' => $dtFim]);

        $params['rTotalCustosFixos_global'] = $conn->fetchAll('select sum(m.valor_total) as valor_total
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'201___\' ', ['dtIni' => $dtIni, 'dtFim' => $dtFim])[0];

        $params['rTotalCustosFixos_deptoPessoal'] = $conn->fetchAll('select c.codigo, c.descricao, sum(m.valor_total) as valor_total 
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'201100___\' 
            GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIni, 'dtFim' => $dtFim]);

        $params['rTotalCustosFixos_deptoPessoal_global'] = $conn->fetchAll('select sum(m.valor_total) as valor_total 
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'201100___\'', ['dtIni' => $dtIni, 'dtFim' => $dtFim])[0];

        $params['rTotalImpostos'] = $conn->fetchAll('select c.codigo, c.descricao, sum(m.valor_total) as valor_total
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'202002\' 
            GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIni, 'dtFim' => $dtFim]);

        $params['rTotalPagtoJurosTaxas'] = $conn->fetchAll('select c.codigo, c.descricao, sum(m.valor_total) as valor_total
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'203001\' 
            GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIni, 'dtFim' => $dtFim]);

        $params['rTotalCustoCartoes'] = $conn->fetchAll('select c.codigo, c.descricao, sum(m.valor_total) as valor_total 
            from fin_movimentacao m, fin_categoria c 
            where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                  m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                  c.codigo LIKE \'202005%\' 
            GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIni, 'dtFim' => $dtFim]);

        $params['rTotalVendas'] = $conn->fetchAll('select sum(valor_total) as valor_total from ven_venda WHERE dt_venda BETWEEN :dtIni AND :dtFim',
            ['dtIni' => $dtIni_anterior, 'dtFim' => $dtFim_anterior])[0];

        $params['rTotalCustos'] = $conn->fetchAll('select sum(vi.json_data->>"$.preco_custo") as valor_total from ven_venda_item vi, ven_venda v WHERE vi.venda_id = v.id AND v.dt_venda BETWEEN :dtIni AND :dtFim',
            ['dtIni' => $dtIni_anterior, 'dtFim' => $dtFim_anterior])[0];


        return $this->doRender('Financeiro/custoOperacional_relatorioMensal.html.twig', $params);


    }


}