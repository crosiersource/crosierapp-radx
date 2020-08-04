<?php

namespace App\Controller\Estoque;

use App\Form\Estoque\EntradaType;
use Cassandra\Decimal;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
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
        }

        return $this->doForm($request, $entrada, $params);
    }

    /**
     *
     * @Route("/est/entrada/formItem/{entrada}/", name="est_entrada_formItem", defaults={"entrada"=null}, requirements={"entrada"="\d+"})
     * @param Request $request
     * @param Entrada|null $entrada
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formItem(Request $request, Entrada $entrada = null): RedirectResponse
    {
        try {
            if ($entrada->status !== 'ABERTO') {
                throw new ViewException('Status difere de "ABERTO"');
            }
            $entradaItem = [];
            $entradaItem['entrada_id'] = $entrada->getId();
            $item = $request->get('item');
            $entradaItem['qtde'] = DecimalUtils::parseStr($item['qtde']);
            $entradaItem['produto_id'] = $item['produto'];
            $entradaItem['unidade_id'] = $item['unidade'];
            $entradaItem['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
            $entradaItem['inserted'] = (new \DateTime())->format('Y-m-d H:i:s');
            $entradaItem['estabelecimento_id'] = 1;
            $entradaItem['user_inserted_id'] = 1;
            $entradaItem['user_updated_id'] = 1;
            $conn = $this->entityHandler->getDoctrine()->getConnection();
            $conn->insert('est_entrada_item', $entradaItem);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao inserir item');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        return $this->redirectToRoute('est_entrada_form', ['id' => $entrada->getId()]);
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


}