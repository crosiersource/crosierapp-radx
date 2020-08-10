<?php

namespace App\Controller\Estoque;

use App\Form\Estoque\EntradaType;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Entrada;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\EntradaItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\EntradaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\EntradaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\EntradaItemRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\UnidadeRepository;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package Cliente\Controller\Crediario
 * @author Carlos Eduardo Pauluk
 */
class EntradaController extends FormListController
{

    private Pdf $knpSnappyPdf;

    private EntradaItemEntityHandler $entradaItemEntityHandler;

    /**
     * @required
     * @param Pdf $knpSnappyPdf
     */
    public function setKnpSnappyPdf(Pdf $knpSnappyPdf): void
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    /**
     * @required
     * @param EntradaEntityHandler $entityHandler
     */
    public function setEntityHandler(EntradaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param EntradaItemEntityHandler $entradaItemEntityHandler
     */
    public function setEntradaItemEntityHandler(EntradaItemEntityHandler $entradaItemEntityHandler): void
    {
        $this->entradaItemEntityHandler = $entradaItemEntityHandler;
    }

    /**
     * @return SyslogBusiness
     */
    public function getSyslog(): SyslogBusiness
    {
        return $this->syslog->setApp('radx')->setComponent(self::class);
    }

    /**
     *
     * @Route("/est/entrada/list/", name="est_entrada_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function listSimpl(Request $request): Response
    {
        $params = [
            'formUrl' => '/est/entrada/form',
            'listRoute' => 'est_entrada_list',
            'listView' => 'Estoque/entrada_list.html.twig',
        ];

        $params['colunas'] = [
            'id',
            'dtLote',
            'descricao',
            'status',
        ];

//        $fnGetFilterDatas = function (array $params) use ($request) : array {
//            $filterDatas = [
//                new FilterData(['id'], 'EQ', 'id', $params),
//                new FilterData(['nome'], 'LIKE', 'nome', $params),
//                new FilterData(['depto'], 'EQ', 'depto', $params),
//                new FilterData(['grupo'], 'EQ', 'grupo', $params),
//                new FilterData(['subgrupo'], 'EQ', 'subgrupo', $params),
//                new FilterData(['fornecedor_nomeFantasia', 'fornecedor_nome'], 'LIKE', 'fornecedor', $params, null, true),
//            ];
//
//            return $filterDatas;
//        };


        $params['limit'] = 200;


        $fnHandleDadosList = function (array &$dados, int $totalRegistros) use ($params) {
            if (count($dados) >= $params['limit'] && $totalRegistros > $params['limit']) {
                $this->addFlash('warn', 'Retornando apenas ' . $params['limit'] . ' registros de um total de ' . $totalRegistros . '. Utilize os filtros!');
            }
        };

        return $this->doListSimpl($request, $params, null, $fnHandleDadosList);
    }

    /**
     *
     * @Route("/est/entrada/form/{id}", name="est_entrada_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Entrada|null $entrada
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function form(Request $request, Entrada $entrada = null)
    {
        $params = [
            'listRoute' => 'est_entrada_listPorDia',
            'typeClass' => EntradaType::class,
            'formView' => 'Estoque/entrada_form.html.twig',
            'formRoute' => 'est_entrada_form',
            'formPageTitle' => 'Entrada'
        ];

        /** @var UnidadeRepository $repoUnidade */
        $repoUnidade = $this->getDoctrine()->getRepository(Unidade::class);
        $params['unidades'] = json_encode($repoUnidade->findUnidadesAtuaisSelect2JS());

        if (!$entrada) {
            $entrada = new Entrada();
            $entrada->dtLote = new \DateTime();
            $entrada->status = 'ABERTO';
            $entrada->responsavel = $this->getUser()->getNome();
        } else {
            $this->preencherDadosPrecosProdutos($entrada);
        }

        return $this->doForm($request, $entrada, $params);
    }

    private function preencherDadosPrecosProdutos(Entrada $entrada): void
    {
        $sqlPrecos = 'select lista.descricao as lista, u.label as unidade, preco.preco_prazo from est_produto_preco preco, est_unidade u, est_lista_preco lista where preco.produto_id = :produtoId and preco.lista_id = lista.id and preco.unidade_id = u.id and preco.atual IS TRUE';
        $stmtPrecos = $this->entityHandler->getDoctrine()->getConnection()->prepare($sqlPrecos);
        foreach ($entrada->itens as $item) {
            $stmtPrecos->bindValue('produtoId', $item->produto->getId());
            $stmtPrecos->execute();
            $rPrecos = $stmtPrecos->fetchAll();
            $helpText = '';
            foreach ($rPrecos as $preco) {
                if ($preco['unidade'] === $item->unidade->label) {
                    $helpText .= $preco['lista'] . ': R$ ' . number_format($preco['preco_prazo'], 2, ',', '.') . ' . ';
                }
            }

            $item->produto->precos_helpText = $helpText;
        }
    }

    /**
     * @Route("/est/entrada/formItem/{entrada}/", name="est_entrada_formItem", defaults={"entrada"=null}, requirements={"entrada"="\d+"})
     *
     * @param Request $request
     * @param Entrada|null $entrada
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formItem(Request $request, Entrada $entrada = null): JsonResponse
    {
        $r = [];
        try {
            if ($entrada->status !== 'ABERTO') {
                throw new ViewException('Status difere de "ABERTO"');
            }
            $conn = $this->entityHandler->getDoctrine()->getConnection();
            $item = $request->get('item');
            $entradaItem = [];
            $entradaItem['entrada_id'] = $entrada->getId();
            $entradaItem['produto_id'] = $item['produto'];
            $entradaItem['unidade_id'] = $item['unidade'];

            $entradaItem['updated'] = (new \DateTime())->format('Y-m-d H:i:s');

            if ($rs = $conn->fetchAll('SELECT * FROM est_entrada_item WHERE entrada_id = :entradaId AND produto_id = :produtoId AND unidade_id = :unidadeId',
                [
                    'entradaId' => $entradaItem['entrada_id'],
                    'produtoId' => $entradaItem['produto_id'],
                    'unidadeId' => $entradaItem['unidade_id']
                ])) {
                $entradaItem['qtde'] = $rs[0]['qtde'] + DecimalUtils::parseStr($item['qtde']);
                $conn->update('est_entrada_item', $entradaItem, ['id' => $rs[0]['id']]);
            } else {
                $entradaItem['qtde'] = DecimalUtils::parseStr($item['qtde']);
                $entradaItem['inserted'] = (new \DateTime())->format('Y-m-d H:i:s');
                $entradaItem['estabelecimento_id'] = 1;
                $entradaItem['user_inserted_id'] = 1;
                $entradaItem['user_updated_id'] = 1;
                $conn->insert('est_entrada_item', $entradaItem);
            }
            $r['result'] = 'OK';
        } catch (\Exception $e) {
            $r['result'] = 'ERR';
            $this->addFlash('error', 'Erro ao inserir item');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        $this->preencherDadosPrecosProdutos($entrada);
        $r['divTbItens'] = $this->renderView('Estoque/entrada_form_divTbItens.html.twig', ['e' => $entrada]);
        return new JsonResponse($r);
    }

    /**
     *
     * @Route("/est/entrada/integrar/{id}", name="est_entrada_integrar", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Entrada $entrada
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function integrar(Request $request, Entrada $entrada)
    {
        try {
            if ($this->isCsrfTokenValid('est_entrada_integrar', $request->get('token'))) {
                $this->entityHandler->integrar($entrada);
            } else {
                $this->addFlash('error', 'Token inválido');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao integrar lote');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('est_entrada_form', ['id' => $entrada->getId()]);
    }

    /**
     * @param Request $request
     * @param $entrada
     */
    public function handleRequestOnValid(Request $request, /** @var Entrada @entrada */ $entrada): void
    {
        if ($request->get('item')) {

            $itemArr = $request->get('item');

            if (!isset($itemArr['produto'])) {
                return;
            }

            /** @var EntradaItem $entradaItem */
            if ($itemArr['id'] ?? null) {
                /** @var EntradaItemRepository $repoEntradaItem */
                $repoEntradaItem = $this->getDoctrine()->getRepository(EntradaItem::class);
                $entradaItem = $repoEntradaItem->find($itemArr['id']);
            } else {
                $entradaItem = new EntradaItem();
            }

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->find($itemArr['produto']);

            $entradaItem->produto = $produto;

            $entradaItem->qtde = DecimalUtils::parseStr($itemArr['qtde']);

            $entradaItem->entrada = $entrada;

            try {
                $this->entradaItemEntityHandler->save($entradaItem);
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    }

    /**
     *
     * @Route("/est/entrada/deleteItem/{item}", name="est_entrada_deleteItem", requirements={"item"="\d+"})
     * @param Request $request
     * @param EntradaItem $item
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function deleteItem(Request $request, EntradaItem $item): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('est_entrada_deleteItem', $request->request->get('token'))) {
            $this->addFlash('error', 'Erro interno do sistema.');
        } else {
            try {
                $this->entradaItemEntityHandler->delete($item);
                $this->addFlash('success', 'Registro deletado com sucesso.');
            } catch (ViewException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erro ao deletar registro.');
            }
        }

        return $this->redirectToRoute('est_entrada_form', ['id' => $item->entrada->getId()]);
    }


    /**
     *
     * @Route("/est/entrada/findProdutos/", name="est_entrada_findProdutos")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_VENDAS", statusCode=403)
     */
    public function findProdutos(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');

            $sql = 'SELECT prod.id, prod.codigo, prod.nome, u.label as unidade_label, u.casas_decimais as unidade_casas_decimais ' .
                'FROM est_produto prod, est_unidade u ' .
                'WHERE prod.unidade_padrao_id = u.id AND (prod.id = :id OR ' .
                'prod.nome LIKE :nome OR ' .
                'prod.codigo LIKE :codigo) ORDER BY prod.nome LIMIT 30';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAll($sql,
                [
                    'id' => (int)$str,
                    'nome' => '%' . $str . '%',
                    'codigo' => '%' . $str
                ]);
            $results = [];

            $sqlUnidades = 'SELECT u.id, u.label as text, u.casas_decimais FROM est_produto_preco preco, est_unidade u WHERE preco.unidade_id = u.id AND preco.atual IS TRUE AND preco.produto_id = :produtoId GROUP BY u.id, u.label, u.casas_decimais';
            $stmtUnidades = $this->entityHandler->getDoctrine()->getConnection()->prepare($sqlUnidades);

            $sqlPrecos = 'select lista.descricao as lista, u.label as unidade, preco.preco_prazo from est_produto_preco preco, est_unidade u, est_lista_preco lista where preco.produto_id = :produtoId and preco.lista_id = lista.id and preco.unidade_id = u.id and preco.atual IS TRUE';
            $stmtPrecos = $this->entityHandler->getDoctrine()->getConnection()->prepare($sqlPrecos);

            foreach ($rs as $r) {
                $codigo = str_pad($r['codigo'], 9, '0', STR_PAD_LEFT);
                $stmtUnidades->bindValue('produtoId', $r['id']);
                $stmtUnidades->execute();
                $rUnidades = $stmtUnidades->fetchAll();

                $stmtPrecos->bindValue('produtoId', $r['id']);
                $stmtPrecos->execute();
                $rPrecos = $stmtPrecos->fetchAll();

                $results[] = [
                    'id' => $r['id'],
                    'text' => '(' . $r['id'] . ') ' . $codigo . ' - ' . $r['nome'] . '(' . $r['unidade_label'] . ')',
                    'unidade_label' => $r['unidade_label'],
                    'unidade_casas_decimais' => $r['unidade_casas_decimais'],
                    'unidades' => $rUnidades,
                    'precos' => $rPrecos,
                ];
            }

            return new JsonResponse(
                ['results' => $results]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }


}