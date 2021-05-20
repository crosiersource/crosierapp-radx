<?php

namespace App\Controller\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Fatura;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\FaturaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class FaturaController extends FormListController
{

    private MovimentacaoBusiness $movimentacaoBusiness;

    /**
     * @required
     * @param MovimentacaoBusiness $movimentacaoBusiness
     */
    public function setMovimentacaoBusiness(MovimentacaoBusiness $movimentacaoBusiness): void
    {
        $this->movimentacaoBusiness = $movimentacaoBusiness;
    }

    /**
     * @required
     * @param FaturaEntityHandler $entityHandler
     */
    public function setEntityHandler(FaturaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     *
     * @Route("/fin/fatura/visualizarFatura/{fatura}", name="fin_fatura_visualizarFatura", requirements={"fatura"="\d+"})
     * @param Fatura $fatura
     * @return Response
     *
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function visualizarFatura(Fatura $fatura): Response
    {
        $params = [
            'e' => $fatura
        ];

        try {
            $conn = $this->getEntityHandler()->getDoctrine()->getConnection();
            $rsMovsIds = $conn->fetchAllAssociative('SELECT mov.id FROM fin_movimentacao mov, fin_categoria cat, fin_carteira carteira WHERE mov.carteira_id = carteira.id AND mov.categoria_id = cat.id AND fatura_id = :faturaId ORDER BY id', ['faturaId' => $fatura->getId()]);
            $movs = [];
            $repoMovimentacao = $this->getDoctrine()->getRepository(Movimentacao::class);

            foreach ($rsMovsIds as $rMovId) {
                $movs[] = $repoMovimentacao->find($rMovId['id']);
            }
            $params['movs'] = $movs;
            $params['total'] = $this->movimentacaoBusiness->somarMovimentacoes($movs);
            if ($fatura->jsonData['venda_id'] ?? false) {
                $venda = $this->getDoctrine()->getRepository(Venda::class)->find($fatura->jsonData['venda_id']);
                $params['venda'] = $venda;
                return $this->doRender('Financeiro/fatura_venda.html.twig', $params);
            } else {
                return $this->doRender('Financeiro/fatura_movimentacoes.html.twig', $params);
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erro ao pesquisar movimentações da fatura');
        }
    }

    
    /**
     *
     * @Route("/fin/fatura/list/", name="fatura_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'fatura_form',
            'listView' => 'Financeiro/faturaList.html.twig',
            'listRoute' => 'fatura_list',
            'listRouteAjax' => 'fatura_datatablesJsList',
            'listPageTitle' => 'Faturas',
            'listId' => 'faturaList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/fin/fatura/datatablesJsList/", name="fatura_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/fin/fatura/delete/{id}/", name="fatura_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Fatura $fatura
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Fatura $fatura): RedirectResponse
    {
        return $this->doDelete($request, $fatura, null);
    }


}