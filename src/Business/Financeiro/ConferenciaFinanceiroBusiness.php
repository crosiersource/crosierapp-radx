<?php


namespace App\Business\Financeiro;

use App\Entity\Financeiro\Carteira;
use App\Entity\Financeiro\Categoria;
use App\Entity\Financeiro\GrupoItem;
use App\Entity\Financeiro\Modo;
use App\Entity\Financeiro\Movimentacao;
use App\Entity\Financeiro\OperadoraCartao;
use App\Entity\Financeiro\RegistroConferencia;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ConferenciaFinanceiroBusiness.
 * Agrega as funcionalidades para o ConferenciaFinanceiroController.
 *
 * @package App\Business\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ConferenciaFinanceiroBusiness
{

    private $doctrine;

    private $movimentacaoBusiness;

    /**
     * ConferenciaFinanceiroBusiness constructor.
     * @param EntityManagerInterface $doctrine
     * @param MovimentacaoBusiness $movimentacaoBusiness
     */
    public function __construct(EntityManagerInterface $doctrine, MovimentacaoBusiness $movimentacaoBusiness)
    {
        $this->doctrine = $doctrine;
        $this->movimentacaoBusiness = $movimentacaoBusiness;
    }

    /**
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @return array
     * @throws \Exception
     */
    public function buildLists(\DateTime $dtIni, \DateTime $dtFim)
    {
        $listCaixaVista = $this->buildListCaixaVista($dtIni, $dtFim);
        $listCaixaPrazo = $this->buildListCaixaPrazo($dtIni, $dtFim);

        $lists = [];
        $lists['caixaVista'] = ['titulo' => 'Caixa a vista', 'itens' => $listCaixaVista];
        $lists['caixaPrazo'] = ['titulo' => 'Caixa a prazo', 'itens' => $listCaixaPrazo];

        $lists['credito_cieloCTPL'] = ['titulo' => 'Cielo CTPL - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 30, 'TOTAL CIELO CTPL - CRÉDITOS', 'CIELO')];
        $lists['credito_cieloMSP'] = ['titulo' => 'Cielo MSP - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 32, 'TOTAL CIELO MSP - CRÉDITOS', 'CIELO MSP')];
        $lists['credito_moderninha'] = ['titulo' => 'Moderninha - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 33, 'TOTAL PAGSEGURO MODERNINHA IPÊ - CRÉDITOS', 'MODERNINHA')];
        $lists['credito_stoneCTPL'] = ['titulo' => 'Stone CTPL - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 34, 'TOTAL STONE - CRÉDITOS', 'STONE')];
        $lists['credito_stoneMSP'] = ['titulo' => 'Stone MSP - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 35, 'TOTAL STONE MSP - CRÉDITOS', 'STONE MSP')];
        $lists['credito_rdcard'] = ['titulo' => 'Redecard - Créditos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 9, 31, 'TOTAL RDCARD - CRÉDITOS', 'REDECARD')];


        $lists['debito_cieloCTPL'] = ['titulo' => 'Cielo CTPL - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 30, 'TOTAL CIELO CTPL - DÉBITOS', 'CIELO')];
        $lists['debito_cieloMSP'] = ['titulo' => 'Cielo MSP - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 32, 'TOTAL CIELO MSP - DÉBITOS', 'CIELO MSP')];
        $lists['debito_moderninha'] = ['titulo' => 'Moderninha - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 33, 'TOTAL PAGSEGURO MODERNINHA IPÊ - DÉBITOS', 'MODERNINHA')];
        $lists['debito_stoneCTPL'] = ['titulo' => 'Stone CTPL - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 34, 'TOTAL STONE - DÉBITOS', 'STONE')];
        $lists['debito_stoneMSP'] = ['titulo' => 'Stone MSP - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 35, 'TOTAL STONE MSP - DÉBITOS', 'STONE MSP')];
        $lists['debito_rdcard'] = ['titulo' => 'Redecard - Débitos', 'itens' => $this->buildListCartao($dtIni, $dtFim, 10, 31, 'TOTAL RDCARD - DÉBITOS', 'REDECARD')];


        $lists['grupos'] = ['titulo' => 'Grupos de Movimentações', 'itens' => $this->buildListGrupos($dtFim)];

        $lists['transfs199e299'] = ['titulo' => 'Transferências entre Carteiras', 'itens' => $this->buildList199e299($dtIni, $dtFim)];

        $listsComItens = [];

        foreach ($lists as $list) {
            if ($list['itens'] and count($list['itens']) > 0) {
                $listsComItens[] = $list;
            }
        }


        return $listsComItens;
    }

    /**
     * @param $rcDescricao
     * @param $totalComparado
     * @param \DateTime $dt
     * @return array
     */
    public function addLinhaRegistroConferencia($rcDescricao, $totalComparado, \DateTime $dt)
    {
        $totalComparado = $totalComparado ? $totalComparado : 0.0;
        $dt->setTime(0, 0, 0, 0);
        $registroConferencia = $this->doctrine->getRepository(RegistroConferencia::class)->findOneBy(['descricao' => $rcDescricao, 'dtRegistro' => $dt]);

        if (!$registroConferencia or $registroConferencia->getValor() <= 0.00) { // is_nan($registroConferencia->getValor())) {
            return null;
//            return ['titulo' => '*** ' . $rcDescricao . ' (INFORMADO)',
//                'valor' => 0,
//                'icon' => $this->chooseIcon($totalComparado, null)];
        } else {
            $dif = number_format($registroConferencia->getValor() - $totalComparado, 2, ',', '.');
            return ['titulo' => $rcDescricao . ' (INFORMADO)',
                'valor' => $registroConferencia->getValor(),
                'icon' => $this->chooseIcon($totalComparado, $registroConferencia),
                'obs' => '(DIF: ' . $dif . ')'];
        }
    }


    /**
     * @param $valor
     * @param RegistroConferencia $rc
     * @return mixed
     */
    public function chooseIcon($valor, ?RegistroConferencia $rc)
    {
        $icone = null;

        if ($valor and $rc) {
            if ($valor == $rc->getValor()) {
                $icone = 'fas fa-thumbs-up';
            } else {
                if ($rc->getObs() != null) {
                    $icone = 'fas fa-exclamation-triangle';
                } else {
                    $icone = 'fas fa-thumbs-down';
                }
            }
        } else {
            $icone = 'fas fa-exclamation-triangle';
        }

        return $icone;
    }

    /**
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @return mixed
     */
    private function buildListCaixaVista(\DateTime $dtIni, \DateTime $dtFim)
    {
        $list = [];
        $c101 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 101]);
        $c102 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 102]);

        // ------- CAIXA A VISTA (BONSUCESSO) -------
        $caixaAVista = $this->doctrine->getRepository(Carteira::class)->findOneBy(['descricao' => 'CAIXA A VISTA']);

        $tCaixaAvista101 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAVista, $c101);

        $list[] = ['titulo' => 'TOTAL ENTRADAS (1.01) - CAIXA A VISTA',
            'valor' => $tCaixaAvista101];

        $tCaixaAvista102 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAVista, $c102);

        if ($tCaixaAvista102) {
            $list[] = ['titulo' => 'TOTAL ENTRADAS (1.02) - CAIXA A VISTA',
                'valor' => $tCaixaAvista102];
        }

        // Linha para Registro de Conferência
        $list[] = $this->addLinhaRegistroConferencia('TOTAL CAIXA A VISTA (BONSUCESSO)', $tCaixaAvista101, $dtFim);

        $cAjustesDeCaixaPos = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 151]);
        $cAjustesDeCaixaNeg = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 251]);

        $tAjustesCaixaAvistaPos = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAVista, $cAjustesDeCaixaPos);
        $tAjustesCaixaAvistaNeg = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAVista, $cAjustesDeCaixaNeg);

        $obs = $tAjustesCaixaAvistaPos . '(+) . ' . $tAjustesCaixaAvistaNeg . '(-)';

        $tAjustesAvista = $tAjustesCaixaAvistaPos - $tAjustesCaixaAvistaNeg;
        $icone = $tAjustesAvista == 0 ? 'fas fa-thumbs-up' : 'fas fa-exclamation';

        $list[] = ['titulo' => 'TOTAL AJUSTES - CAIXA A VISTA',
            'valor' => $tAjustesAvista,
            'icone' => $icone,
            'obs' => $obs];

        $tVendasEKT = 0.0; // $this->doctrine->getRepository(Venda::class)->findTotalAVistaEKT($dtIni, $dtFim, true);

        if ($tVendasEKT) {
            $dif = $tCaixaAvista101 - $tVendasEKT;
            $icone = $tVendasEKT == $tCaixaAvista101 ? 'fas fa-thumbs-up' : 'fas fa-exclamation';
            $obs = '(DIF: ' . $dif . ')';
            $list[] = ['titulo' => 'TOTAL EKT - CAIXA A VISTA',
                'valor' => $tVendasEKT,
                'icone' => $icone,
                'obs' => $obs];
        }

        return $list;
    }

    /**
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @return mixed
     */
    private function buildListCaixaPrazo(\DateTime $dtIni, \DateTime $dtFim)
    {
        $list = [];
        $c101 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 101]);
        $c102 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 102]);

        // ------- CAIXA A PRAZO -------

        $caixaAPrazo = $this->doctrine->getRepository(Carteira::class)->findOneBy(['descricao' => 'CAIXA A PRAZO']);

        $tCaixaAprazo = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAPrazo, $c101);
        $tCaixaAprazoExternas = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAPrazo, $c102);

        $list[] = ['titulo' => 'TOTAL ENTRADAS (1.01) - CAIXA A PRAZO',
            'valor' => $tCaixaAprazo];

        $list[] = $this->addLinhaRegistroConferencia('TOTAL CAIXA A PRAZO - SERVIPA', $tCaixaAprazo, $dtFim);

        $list[] = ['titulo' => 'TOTAL ENTRADAS (1.02) - CAIXA A PRAZO',
            'valor' => $tCaixaAprazoExternas];

        $list[] = $this->addLinhaRegistroConferencia('TOTAL CAIXA A PRAZO - OUTROS RECEB', $tCaixaAprazoExternas, $dtFim);

        $cAjustesDeCaixaPos = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 151]);
        $cAjustesDeCaixaNeg = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 251]);

        $tAjustesCaixaAprazoPos = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAPrazo, $cAjustesDeCaixaPos);
        $tAjustesCaixaAprazoNeg = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $caixaAPrazo, $cAjustesDeCaixaNeg);

        $obsAjustesCaixaPrazo = $tAjustesCaixaAprazoPos . '(+) . ' . $tAjustesCaixaAprazoNeg . '(-)';


        $tAjustesAprazo = $tAjustesCaixaAprazoPos - $tAjustesCaixaAprazoNeg;
        $icone = $tAjustesAprazo == 0.0 ? 'fas fa-thumbs-up' : 'fas fa-exclamation';

        $list[] = ['titulo' => 'TOTAL AJUSTES - CAIXA A PRAZO',
            'valor' => $tAjustesAprazo,
            'icone' => $icone,
            'obs' => $obsAjustesCaixaPrazo];

        return $list;

    }


    /**
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @param $modoCodigo
     * @param $carteiraCodigo
     * @param $titulo
     * @param $operadoraCartaoDescricao
     * @return array
     * @throws \Exception
     */
    public function buildListCartao(\DateTime $dtIni, \DateTime $dtFim, $modoCodigo, $carteiraCodigo, $titulo, $operadoraCartaoDescricao)
    {
        $operadoraCartao = $this->doctrine->getRepository(OperadoraCartao::class)->findOneBy(['descricao' => $operadoraCartaoDescricao]);
        $carteira = $this->doctrine->getRepository(Carteira::class)->findOneBy(['codigo' => $carteiraCodigo]);
        if (!$carteira) {
            throw new \Exception('Carteira não encontrada para código $carteiraCodigo');
        }

        $modo = $this->doctrine->getRepository(Modo::class)->findOneBy(['codigo' => $modoCodigo]);


        $c101 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 101]);
        $c102 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 102]);

        // Nos casos de CARTÕES DE DÉBITO, a carteira a ser realizada a totalização é sempre o CAIXA A VISTA.
        if (strpos($modo->getDescricao(), 'DÉBITO') !== FALSE) {
            $carteiraTotal = $this->doctrine->getRepository(Carteira::class)->findOneBy(['codigo' => 2]);
            $debito = true;
        } else {
            $carteiraTotal = $carteira;
            $debito = false;
            $operadoraCartao = null; // só precisa (e só funciona) no caso do débito (que precisa distinguir no caixa a vista)
        }

        $t101 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $carteiraTotal, $c101, $modo, $operadoraCartao);
        $t102 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, $carteiraTotal, $c102, $modo, $operadoraCartao);

        $total = bcadd($t101, $t102, 2);

        $list[] = ['titulo' => $titulo, 'valor' => $total];
        $linhaRegConf = $this->addLinhaRegistroConferencia($titulo, $total, $dtFim);
        if (!$linhaRegConf) {
            return null;
        } else {
            $list[] = $linhaRegConf;
        }

        $taxa = $this->movimentacaoBusiness->calcularTaxaCartao($carteira, $debito, $total, $dtIni, $dtFim);

        $icone = $taxa > 0.0001 ? 'fas fa-thumbs-up' : 'cancel';

        $list[] = ['titulo' => 'TAXA',
            'valor' => $taxa,
            'icone' => $icone];

        return $list;
    }

    public function buildListGrupos($dtFim)
    {
        $gruposItens = $this->doctrine->getRepository(GrupoItem::class)->findByMesAno($dtFim);

        $list = [];

        foreach ($gruposItens as $gi) {
            if (!$gi) {
                continue;
            }
            $valorLanctos = $gi->getValorLanctos();
            $valorInformado = $gi->getValorInformado();

            $icone = bcsub($valorLanctos, $valorInformado,2) == 0.00 ? 'fas fa-thumbs-up' : 'fas fa-thumbs-down';

            $list[] = ['titulo' => 'TOTAL LANÇADO - ' . $gi->getPai()->getDescricao(), 'valor' => $valorLanctos];
            $list[] = ['titulo' => '*** TOTAL INFORMADO - ' . $gi->getPai()->getDescricao(), 'valor' => $valorInformado, 'icon' => $icone];
            $list[] = [];
        }

        return $list;
    }

    /**
     * Resumo de TRANSFERÊNCIAS ENTRE CARTEIRAS.
     *
     * @param \DateTime $dtIni
     * @param \DateTime $dtFim
     * @return array
     */
    public function buildList199e299(\DateTime $dtIni, \DateTime $dtFim)
    {
        $c199 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 199]);
        $c299 = $this->doctrine->getRepository(Categoria::class)->findOneBy(['codigo' => 299]);

        $t299 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, null, $c299);
        $t199 = $this->doctrine->getRepository(Movimentacao::class)->findTotal($dtIni, $dtFim, null, $c199);

        $icon = bcsub($t299, $t199,2) == 0.00 ? 'fas fa-thumbs-up' : 'fas fa-thumbs-down';

        $list = [];
        $list[] = ['titulo' => 'TOTAL - ' . $c299->getPai()->getDescricao(), 'valor' => $t299, 'icon' => $icon];
        $list[] = ['titulo' => 'TOTAL - ' . $c199->getPai()->getDescricao(), 'valor' => $t199, 'icon' => $icon];

        return $list;
    }


}