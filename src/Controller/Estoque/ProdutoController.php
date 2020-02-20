<?php

namespace App\Controller\Estoque;

use App\Business\Estoque\ProdutoBusiness;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoComposicao;
use App\Entity\Estoque\ProdutoImagem;
use App\EntityHandler\Estoque\ProdutoComposicaoEntityHandler;
use App\EntityHandler\Estoque\ProdutoEntityHandler;
use App\EntityHandler\Estoque\ProdutoImagemEntityHandler;
use App\Form\Estoque\ProdutoType;
use App\Repository\Estoque\ProdutoComposicaoRepository;
use App\Repository\Estoque\ProdutoImagemRepository;
use App\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoController extends FormListController
{

    private ProdutoComposicaoEntityHandler $produtoComposicaoEntityHandler;

    private ProdutoImagemEntityHandler $produtoImagemEntityHandler;

    private UploaderHelper $uploaderHelper;

    private ProdutoBusiness $produtoBusiness;

    /**
     * @required
     * @param ProdutoComposicaoEntityHandler $produtoComposicaoEntityHandler
     */
    public function setProdutoComposicaoEntityHandler(ProdutoComposicaoEntityHandler $produtoComposicaoEntityHandler): void
    {
        $this->produtoComposicaoEntityHandler = $produtoComposicaoEntityHandler;
    }

    /**
     * @required
     * @param ProdutoImagemEntityHandler $produtoImagemEntityHandler
     */
    public function setProdutoImagemEntityHandler(ProdutoImagemEntityHandler $produtoImagemEntityHandler): void
    {
        $this->produtoImagemEntityHandler = $produtoImagemEntityHandler;
    }

    /**
     * @required
     * @param UploaderHelper $uploaderHelper
     */
    public function setUploaderHelper(UploaderHelper $uploaderHelper): void
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @required
     * @param ProdutoEntityHandler $entityHandler
     */
    public function setEntityHandler(ProdutoEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param ProdutoBusiness $produtoBusiness
     */
    public function setProdutoBusiness(ProdutoBusiness $produtoBusiness): void
    {
        $this->produtoBusiness = $produtoBusiness;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['nome', 'titulo', 'id', 'codigoFrom'], 'LIKE', 'str', $params),
        ];
    }

    /**
     *
     * @Route("/est/produto/form/{id}", name="est_produto_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param Produto|null $produto
     * @return RedirectResponse|Response
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function form(Request $request, Produto $produto = null)
    {
        $params = [
            'listRoute' => 'est_produto_list',
            'typeClass' => ProdutoType::class,
            'formView' => 'Estoque/produto_form.html.twig',
            'formRoute' => 'est_produto_form',
            'formPageTitle' => 'Produto'
        ];

        if (!$produto) {
            $produto = new Produto();
        }

        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
        $params['jsonMetadata'] = json_decode($repoProduto->getJsonMetadata(), true);

        if ($produto->composicao === 'S') {
            $this->produtoBusiness->fillQtdeEmEstoqueComposicao($produto);
        }

        // Verifique o método handleRequestOnValid abaixo
        return $this->doForm($request, $produto, $params);
    }

    /**
     *
     * @Route("/est/produto/formImagemFileUpload/", name="est_produto_formImagemFileUpload")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formImagemFileUpload(Request $request): JsonResponse
    {
        try {
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->find($request->get('produto')['id']);
            /** @var UploadedFile $brochureFile */
            $imageFiles = $request->files->get('produto_imagem')['imageFile'];
            foreach ($imageFiles as $imageFile) {
                $produtoImagem = new ProdutoImagem();
                $produtoImagem->setProduto($produto);
                $produtoImagem->setImageFile($imageFile);
                $this->produtoImagemEntityHandler->save($produtoImagem);
            }
            $r = [
                'result' => 'OK',
                'filesUl' => $this->renderView('Estoque/produto_form_produto_filesUl.html.twig', ['e' => $produto])
            ];
        } catch (\Exception $e) {
            $r = ['result' => 'ERRO'];
        }
        return new JsonResponse($r);
    }

    /**
     *
     * @Route("/est/produto/formImagemSalvarDescricao/", name="est_produto_formImagemSalvarDescricao")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formImagemSalvarDescricao(Request $request): JsonResponse
    {
        try {
            /** @var ProdutoImagemRepository $repoProdutoImagem */
            $repoProdutoImagem = $this->getDoctrine()->getRepository(ProdutoImagem::class);
            /** @var ProdutoImagem $produtoImagem */
            $produtoImagem = $repoProdutoImagem->find($request->get('produtoImagemId'));
            $descricao = $request->get('descricao');
            $produtoImagem->setDescricao($descricao);
            $this->produtoImagemEntityHandler->save($produtoImagem);
            $r = [
                'result' => 'OK',
                'filesUl' => $this->renderView('Estoque/produto_form_produto_filesUl.html.twig', ['e' => $produto])
            ];
        } catch (\Exception $e) {
            $r = ['result' => 'ERRO'];
        }
        return new JsonResponse($r);
    }

    /**
     *
     * @Route("/est/produto/formImagemSaveOrdem/", name="est_produto_formImagemSaveOrdem")
     * @param Request $request
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formImagemSaveOrdem(Request $request)
    {
        try {
            $ids = $request->get('ids');
            $idsArr = explode(',', $ids);
            $ordens = $this->produtoImagemEntityHandler->salvarOrdens($idsArr);

            /** @var ProdutoImagemRepository $repoProdutoImagem */
            $repoProdutoImagem = $this->getDoctrine()->getRepository(ProdutoImagem::class);
            /** @var ProdutoImagem $produtoImagem */
            $produtoImagem = $repoProdutoImagem->find(array_key_first($ordens));

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produto */
            $produto = $repoProduto->findOneBy(['id' => $produtoImagem->getProduto()->getId()]);

            $this->entityHandler->save($produto);
            $r = ['result' => 'OK', 'ids' => $ordens];
            return new JsonResponse($r);
        } catch (ViewException $e) {
            return new JsonResponse(['result' => 'FALHA']);
        }
    }

    /**
     *
     * @Route("/est/produto/list/", name="est_produto_list")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'est_produto_form',
            'listView' => '@CrosierLibBase/list.html.twig',
            'listJS' => 'Estoque/produto_list.js',
            'listRoute' => 'est_produto_list',
            'listRouteAjax' => 'est_produto_datatablesJsList',
            'listPageTitle' => 'Produtos',
            'deleteRoute' => 'est_produto_delete',
            'listId' => 'produto_list'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/est/produto/datatablesJsList/", name="est_produto_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws ViewException
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    public function handleSerializedList(array &$r): void
    {
        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
        foreach ($r['data'] as $key => $p) {
            /** @var Produto $produto */
            $produto = $repoProduto->find($p['e']['id']);
            $r['data'][$key]['e']['imagem1'] = '';
            if ($produto->getImagens() && $produto->getImagens()->count() > 0) {
                /** @var ProdutoImagem $imagem1 */
                $imagem1 = $produto->getImagens()->get(0);
                $r['data'][$key]['e']['imagem1'] = $this->uploaderHelper->asset($imagem1, 'imageFile');
            }
        }
    }


    /**
     *
     * @Route("/est/produto/delete/{id}/", name="est_produto_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param Produto $produto
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function delete(Request $request, Produto $produto): RedirectResponse
    {
        $params['listRoute'] = 'est_produto_list';
        return $this->doDelete($request, $produto, $params);
    }

    /**
     *
     * @Route("/est/produtoImagem/delete/{produtoImagem}/", name="est_produtoImagem_delete", requirements={"produtoImagem"="\d+"})
     * @param ProdutoImagem $produtoImagem
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function produtoImagemDelete(ProdutoImagem $produtoImagem): RedirectResponse
    {
        try {
            $this->produtoImagemEntityHandler->delete($produtoImagem);
            $this->produtoImagemEntityHandler->reordenar($produtoImagem->getProduto());
            /** @var Produto $produto */
            $produto = $this->entityHandler->getDoctrine()->getRepository(Produto::class)->findOneBy(['id' => $produtoImagem->getProduto()->getId()]);
            $this->entityHandler->save($produto);
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao deletar imagem');
        }

        return $this->redirectToRoute('est_produto_form', ['id' => $produtoImagem->getProduto()->getId(), '_fragment' => 'fotos']);
    }


    /**
     *
     * @Route("/est/produto/formComposicao/{produto}", name="est_produto_formComposicao", requirements={"produto"="\d+"})
     * @param Request $request
     * @param Produto|null $produto
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formComposicao(Request $request, Produto $produto)
    {
        try {
            $produtoComposicaoArr = $request->get('produtoComposicao');
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
            /** @var Produto $produtoFilho */
            $produtoFilho = $repoProduto->find($produtoComposicaoArr['produtoFilho']);
            if ($produtoComposicaoArr['id']) {
                /** @var ProdutoComposicaoRepository $repoProdutoComposicao */
                $repoProdutoComposicao = $this->getDoctrine()->getRepository(ProdutoComposicao::class);
                $composicao = $repoProdutoComposicao->find($produtoComposicaoArr['id']);
            } else {
                $composicao = new ProdutoComposicao();
            }
            $composicao->setProdutoPai($produto);
            $composicao->setProdutoFilho($produtoFilho);
            $composicao->setQtde(DecimalUtils::parseStr($produtoComposicaoArr['qtde']));
            $composicao->setPrecoComposicao(DecimalUtils::parseStr($produtoComposicaoArr['precoComposicao']));

            $this->produtoComposicaoEntityHandler->save($composicao);

        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao salvar a composição');
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao salvar a composição');
        }
        return $this->redirectToRoute('est_produto_form', ['id' => $produto->getId(), '_fragment' => 'composicao']);
    }

    /**
     *
     * @Route("/est/produtoComposicao/delete/{produtoComposicao}/", name="est_produtoComposicao_delete", requirements={"produtoComposicao"="\d+"})
     * @param ProdutoComposicao $produtoComposicao
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function produtoComposicaoDelete(ProdutoComposicao $produtoComposicao): RedirectResponse
    {
        try {
            $this->produtoComposicaoEntityHandler->delete($produtoComposicao);
            $this->produtoComposicaoEntityHandler->reordenar($produtoComposicao->produtoPai);
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao deletar o item da composição');
        }

        return $this->redirectToRoute('est_produto_form', ['id' => $produtoComposicao->produtoPai->getId(), '_fragment' => 'composicao']);
    }

    /**
     *
     * @Route("/est/produto/findProdutoByIdNomeTituloJSON/", name="est_produto_findProdutoByIdNomeTituloJSON")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutoByIdNomeTituloJSON(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);

            if (ctype_digit($str)) {
                $produtos = $repoProduto->findByFiltersSimpl([['id', 'EQ', $str]]);
            } else {
                $produtos = $repoProduto->findByFiltersSimpl([[['nome', 'titulo'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            }
            $select2js = Select2JsUtils::toSelect2DataFn($produtos, function ($e) {
                /** @var Produto $e */
                return ($e->jsonData['titulo'] ?: $e->nome) . ' (' . $e->getId() . ')';
            });
            return new JsonResponse(
                ['results' => $select2js]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['results' => []]
            );
        }
    }


    /**
     *
     * @Route("/est/produto/formComposicaoSaveOrdem/", name="est_produto_formComposicaoSaveOrdem")
     * @param Request $request
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function formComposicaoSaveOrdem(Request $request)
    {
        try {
            $ids = $request->get('ids');
            $idsArr = explode(',', $ids);
            $ordens = $this->produtoComposicaoEntityHandler->salvarOrdens($idsArr);
            $r = ['result' => 'OK', 'ids' => $ordens];
            return new JsonResponse($r);
        } catch (ViewException $e) {
            return new JsonResponse(['result' => 'FALHA']);
        }
    }

}
