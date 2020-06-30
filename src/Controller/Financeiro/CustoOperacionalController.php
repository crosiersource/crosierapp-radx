<?php

namespace App\Controller\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\DiaUtil;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\DiaUtilRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
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
     * @return SyslogBusiness
     */
    public function getSyslog(): SyslogBusiness
    {
        return $this->syslog->setApp('radx')->setComponent(self::class);
    }


    /**
     *
     * @Route("/fin/custoOperacional/relatorioMensal", name="fin_custoOperacional_relatorioMensal")
     * @param Request $request
     * @param Connection $conn
     * @return Response
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     * @throws \Exception
     */
    public function relatorioMensal(Request $request, Connection $conn): Response
    {
        $params['filter'] = $request->get('filter');

        if (!($params['filter']['dts'] ?? false)) {
            $dtIniStr = '01/' . DateTimeUtils::incMes(new \DateTime(), -12)->format('m/Y');
            $dtFimStr = DateTimeUtils::incMes(new \DateTime(), -1)->format('t/m/Y');
            $params['filter']['dts'] = $dtIniStr . ' - ' . $dtFimStr;
        }

        $dtIni = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 0, 10));
        $dtIniSQL = $dtIni->format('Y-m-d');
        $dtFim = DateTimeUtils::parseDateStr(substr($params['filter']['dts'], 13, 10));
        $dtFimSQL = $dtFim->format('Y-m-d');

        /** @var DiaUtilRepository $repoDiaUtil */
        $repoDiaUtil = $this->getDoctrine()->getRepository(DiaUtil::class);
        $prox = $repoDiaUtil->incPeriodo($dtIni, $dtFim, true);
        $ante = $repoDiaUtil->incPeriodo($dtIni, $dtFim, false);
        $params['antePeriodoI'] = $ante['dtIni'];
        $params['antePeriodoF'] = $ante['dtFim'];
        $params['proxPeriodoI'] = $prox['dtIni'];
        $params['proxPeriodoF'] = $prox['dtFim'];

        try {

            $params['mascara'] = Categoria::MASK;

            $params['rTotalCustosFixos'] = $conn->fetchAll('select c.id, c.codigo, c.descricao, sum(m.valor_total) as valor_total
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND 
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'201___\' 
                        GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL]);
            $params['rTotalCustosFixos_global'] = $conn->fetchAll('select sum(m.valor_total) as valor_total
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'201___\' ', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL])[0];

            $params['rTotalCustosFixos_deptoPessoal'] = $conn->fetchAll('select c.id, c.codigo, c.descricao, sum(m.valor_total) as valor_total 
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'201100___\' 
                GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL]);
            $params['rTotalCustosFixos_deptoPessoal_global'] = $conn->fetchAll('select sum(m.valor_total) as valor_total 
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'201100___\'', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL])[0];

            $params['rTotalImpostos'] = $conn->fetchAll('select c.id, c.codigo, c.descricao, sum(m.valor_total) as valor_total
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                    m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'202002\' 
                GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL]);

            $params['rTotalPagtoJurosTaxas'] = $conn->fetchAll('select c.id, c.codigo, c.descricao, sum(m.valor_total) as valor_total
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'203001\' 
                GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL]);

            $params['rTotalCustoCartoes'] = $conn->fetchAll('select c.id, c.codigo, c.descricao, sum(m.valor_total) as valor_total 
                from fin_movimentacao m, fin_categoria c 
                where m.categoria_id = c.id AND m.centrocusto_id = 1 AND
                      m.dt_pagto BETWEEN :dtIni AND :dtFim AND 
                      c.codigo LIKE \'202005%\' 
                GROUP BY c.codigo, c.descricao', ['dtIni' => $dtIniSQL, 'dtFim' => $dtFimSQL]);

            // As vendas são calculadas com um mês a menos
            $dtIni_anterior = DateTimeUtils::incMes($dtIni, -1)->format('Y-m-d');
            $dtFim_anterior = DateTimeUtils::incMes($dtFim, -1)->format('Y-m-t');

            $params['rTotalVendas'] = $conn->fetchAll(
                'select sum(valor_total) as valor_total from ven_venda WHERE dt_venda BETWEEN :dtIni AND :dtFim',
                ['dtIni' => $dtIni_anterior, 'dtFim' => $dtFim_anterior])[0];

            $params['rTotalCustos'] = $conn->fetchAll(
                'select sum(vi.json_data->>"$.preco_custo") as valor_total from ven_venda_item vi, ven_venda v WHERE vi.venda_id = v.id AND v.dt_venda BETWEEN :dtIni AND :dtFim',
                ['dtIni' => $dtIni_anterior, 'dtFim' => $dtFim_anterior])[0];

        } catch (\Exception $e) {
            $this->getSyslog()->err('Erro ao calcular', $e->getTraceAsString());
            $this->addFlash('error', 'Erro ao calcular');
        }


        return $this->doRender('Financeiro/custoOperacional_relatorioMensal.html.twig', $params);


    }


}