<?php

namespace App\Controller\Estoque;

use App\Form\Estoque\ProdutoType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ImageUtils\ImageUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\IntegradorEcommerceFactory;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\IntegradorWebStorm;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ListaPreco;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoComposicao;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoImagem;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoPreco;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoComposicaoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoImagemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoPrecoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ListaPrecoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoComposicaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoImagemRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoPrecoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\UnidadeRepository;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoController extends FormListController
{

    private ProdutoComposicaoEntityHandler $produtoComposicaoEntityHandler;

    private ProdutoImagemEntityHandler $produtoImagemEntityHandler;

    private ProdutoPrecoEntityHandler $produtoPrecoEntityHandler;

    private UploaderHelper $uploaderHelper;

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
     * @param ProdutoPrecoEntityHandler $produtoPrecoEntityHandler
     */
    public function setProdutoPrecoEntityHandler(ProdutoPrecoEntityHandler $produtoPrecoEntityHandler): void
    {
        $this->produtoPrecoEntityHandler = $produtoPrecoEntityHandler;
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
        } else {
            // Para a tabela na aba Estoques/Preços
            $listasPrecos = [];
            foreach ($produto->precos as $preco) {
                $preco->lista->descricao;
                $listasPrecos[$preco->lista->getId()]['lista'] = $preco->lista;
                $listasPrecos[$preco->lista->getId()]['precos'][] = $preco;
            }
            $params['listasPrecos'] = $listasPrecos;
        }

        /** @var UnidadeRepository $repoUnidade */
        $repoUnidade = $this->getDoctrine()->getRepository(Unidade::class);
        $params['unidades'] = json_encode($repoUnidade->findUnidadesAtuaisSelect2JS());

        /** @var ListaPrecoRepository $repoListaPreco */
        $repoListaPreco = $this->getDoctrine()->getRepository(ListaPreco::class);
        $params['listasPrecos_options'] = json_encode($repoListaPreco->findAllSelect2JS());

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getDoctrine()->getRepository(AppConfig::class);
        $params['custoOperacionalPadrao'] = $repoAppConfig->findByChave('estoque.precos.custoOperacionalPadrao');
        $params['custoFinanceiroPadrao'] = $repoAppConfig->findByChave('estoque.precos.custoFinanceiroPadrao');
        $params['margemPadrao'] = $repoAppConfig->findByChave('estoque.precos.margemPadrao');


        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
        $params['jsonMetadata'] = json_decode($repoProduto->getJsonMetadata(), true);

        $fnHandleRequestOnValid = function (Request $request, Produto $produto) {
            $produto->jsonData['ecommerce_desatualizado'] = 1;
        };

        // Verifique o método handleRequestOnValid abaixo
        return $this->doForm($request, $produto, $params, false, $fnHandleRequestOnValid);
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
            $imageFiles = $request->files->get('produto_imagem')['imageFile'];
            /** @var UploadedFile $imageFile */
            foreach ($imageFiles as $imageFile) {
                $this->logger->info('Salvando ' . $imageFile->getFilename());
                $produtoImagem = new ProdutoImagem();
                $produtoImagem->setProduto($produto);
                $produtoImagem->setImageFile($imageFile);
                $this->produtoImagemEntityHandler->save($produtoImagem);
                $this->logger->info('OK');
            }
            $r = [
                'result' => 'OK',
                'filesUl' => $this->renderView('Estoque/produto_form_produto_filesUl.html.twig', ['e' => $produto])
            ];
        } catch (\Exception $e) {
            $this->logger->error('Erro no formImagemFileUpload() - ' . $e->getMessage());
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
                'filesUl' => $this->renderView('Estoque/produto_form_produto_filesUl.html.twig', ['e' => $produtoImagem->getProduto()])
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
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     * @return JsonResponse
     */
    public function formComposicao(Request $request, Produto $produto): JsonResponse
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
            $composicao->produtoPai = $produto;
            $composicao->produtoFilho = $produtoFilho;
            $composicao->qtde = DecimalUtils::parseStr($produtoComposicaoArr['qtde']);
            $composicao->precoComposicao = DecimalUtils::parseStr($produtoComposicaoArr['precoComposicao']);
            $this->produtoComposicaoEntityHandler->save($composicao);
            $produto->composicoes->add($composicao);
            $this->entityHandler->save($produto);

            $r = [
                'result' => 'OK',
                'divTbComposicao' => $this->renderView('Estoque/produto_form_composicao_divTbComposicao.html.twig', ['e' => $produto])
            ];
        } catch (ViewException | \Exception $e) {
            $this->logger->error('Erro - formComposicao()');
            $this->logger->error($e->getMessage());
            $msg = $e instanceof ViewException ? $e->getMessage() : 'Erro ao salvar item da composição';
            $r = [
                'result' => 'ERRO',
                'msg' => $msg
            ];
        }
        return new JsonResponse($r);

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
     * @Route("/est/produto/findProdutoParaComposicao/", name="est_produto_findProdutoParaComposicao")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutoParaComposicao(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);

            if (ctype_digit($str)) {
                $produtos = $repoProduto->findByFiltersSimpl([['id', 'EQ', $str], ['composicao', 'EQ', 'N']]);
            } else {
                $filterData_titulo = new FilterData('titulo', 'LIKE');
                $filterData_titulo->jsonDataField = true;
                $filterData_titulo->val = $str;

                $filterData_nomeOUtitulo = new FilterData('nome', 'LIKE');
                $filterData_nomeOUtitulo->val = $str;
                $filterData_nomeOUtitulo->setOrFilterData($filterData_titulo);

                $produtos = $repoProduto->findByFiltersSimpl([$filterData_nomeOUtitulo, ['composicao', 'EQ', 'N']], ['nome' => 'ASC'], 0, 50);
            }

            $results = [];
            /** @var Produto $produto */
            foreach ($produtos as $produto) {
                $results[] = [
                    'id' => $produto->getId(),
                    'text' => $produto->jsonData['titulo'] ?? $produto->nome,
                    'preco_tabela' => $produto->jsonData['preco_tabela']
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

    /**
     * Resolvendo problema para quando foram alterados depto/grupo/subgrupo de produto, mas isso não refletiu no path das imagens.
     *
     * @Route("/est/produto/moveImages/", name="est_produto_moveImages")
     * @param Connection $conn
     * @param ParameterBagInterface $parameterBag
     * @return Response
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function moveImages(Connection $conn, ParameterBagInterface $parameterBag)
    {

        $pasta = $parameterBag->get('kernel.project_dir') . '/public/images/produtos';
        exec('find ' . $pasta, $output);

        $arquivos = [];
        foreach ($output as $arq) {
            $arquivos[basename($arq)] = $arq;
        }

        $imagens = $conn->fetchAllAssociative('select i.id, i.produto_id, depto_id, grupo_id, subgrupo_id, image_name from est_produto p, est_produto_imagem i where p.id = i.produto_id');

        $result = [];

        foreach ($imagens as $imagem) {

            if (!isset($arquivos[$imagem['image_name']])) {
                $result[] = 'ERRO: ' . $imagem['image_name'] . ' não existe no disco do ' . $imagem['produto_id'];
                continue;
            }

            $caminhoQueDeveriaSer = $parameterBag->get('kernel.project_dir') .
                '/public/images/produtos/' .
                $imagem['depto_id'] . '/' .
                $imagem['grupo_id'] . '/' .
                $imagem['subgrupo_id'] . '/' . $imagem['image_name'];

            if ($arquivos[$imagem['image_name']] !== $caminhoQueDeveriaSer) {
                @mkdir($parameterBag->get('kernel.project_dir') .
                    '/public/images/produtos/' .
                    $imagem['depto_id'] . '/' .
                    $imagem['grupo_id'] . '/' .
                    $imagem['subgrupo_id'] . '/', 0777, true);

                rename($arquivos[$imagem['image_name']], $caminhoQueDeveriaSer);
                $result[] = 'ERRO: ' . $imagem['image_name'] . ' deveria estar em ' . $caminhoQueDeveriaSer . ' (e não em ' . $arquivos[$imagem['image_name']] . ') do ' . $imagem['produto_id'];
            }
        }

        return new Response(implode('<br>', $result));
    }


    /**
     *
     * @Route("/est/produto/findById/{produto}", name="est_produto_findById", requirements={"produto"="\d+"})
     * @param Produto $produto
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findById(Produto $produto): JsonResponse
    {
        try {
            $produtoJson = EntityIdUtils::serialize($produto);
            return new JsonResponse(
                [
                    'result' => 'OK',
                    'produto' => $produtoJson
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['result' => 'ERRO']
            );
        }
    }

    /**
     *
     * @Route("/est/produto/findProdutoByIdOuNome/", name="est_produto_findProdutoByIdOuNome")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutoByIdOuNome(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);

            if (ctype_digit($str)) {
                $produtos = $repoProduto->findByFiltersSimpl([['id', 'EQ', $str]]);
            } else {
                $produtos = $repoProduto->findByFiltersSimpl([[['nome'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            }
            $select2js = Select2JsUtils::toSelect2DataFn($produtos, function ($e) {
                /** @var Produto $e */
                return str_pad($e->getId(), 9, '0', STR_PAD_LEFT) . ' - ' . $e->nome;
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
     * @Route("/est/produto/findProdutosByIdOuNomeJson/", name="est_produto_findProdutosByIdOuNomeJson")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutosByIdOuNomeJson(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');

            $sql = 'SELECT prod.id, prod.nome, prod.json_data, preco.preco_prazo FROM est_produto prod LEFT JOIN est_produto_preco preco ON prod.id = preco.produto_id ' .
                'WHERE preco.atual AND (' .
                'prod.id LIKE :str OR ' .
                'prod.nome LIKE :str OR ' .
                'json_data->>"$.codigo" LIKE :str) ORDER BY prod.nome LIMIT 20';

            $rs = $this->entityHandler->getDoctrine()->getConnection()->fetchAllAssociative($sql, ['str' => '%' . $str . '%']);
            $results = [];
            foreach ($rs as $r) {
                $jsonData = json_decode($r['json_data'], true);
                $precoEntrada = $r['preco_prazo'] ?? $jsonData['preco_tabela'] ?? 0.0;
                $codigo = str_pad($jsonData['codigo'] ?? $r['id'], 6, '0', STR_PAD_LEFT);
                $results[] = [
                    'id' => $r['id'],
                    'nome' => $codigo . ' - ' . $r['nome'],
                    'preco_entrada' => $precoEntrada,
                    'unidade' => $jsonData['unidade'] ?? 'UN'
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


    /**
     *
     * @Route("/est/produto/corrigirPrecosAtuais/", name="est_produto_corrigirPrecosAtuais")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function corrigirPrecosAtuais(): Response
    {
        try {
            $produtosIds = $this->entityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT id FROM est_produto');
            foreach ($produtosIds as $produtoId) {
                $precoAtual = $precos = $this->entityHandler->getDoctrine()->getConnection()
                    ->fetchAllAssociative('SELECT id FROM est_produto_preco WHERE produto_id = :produtoId ORDER BY preco_prazo DESC LIMIT 1', ['produtoId' => $produtoId['id']]);
                if ($precoAtual) {
                    $this->entityHandler->getDoctrine()->getConnection()
                        ->executeQuery('UPDATE est_produto_preco SET atual = true WHERE id = :id', ['id' => $precoAtual[0]['id']]);
                }
            }
            return new Response('OK');
        } catch (\Exception $e) {
            return new Response('ERRO');
        }
    }


    /**
     *
     * @Route("/est/produto/corrigeThumbnails", name="est_produto_corrigeThumbnails")
     * @param Request $request
     * @param ParameterBagInterface $params
     * @return Response
     *
     * @throws \Doctrine\DBAL\DBALException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function corrigeThumbnails(Request $request, ParameterBagInterface $params): Response
    {
        $limit = $request->get('limit') ?? 1;
        $conn = $this->entityHandler->getDoctrine()->getConnection();

        $sql = 'select id, depto_id, grupo_id, subgrupo_id, json_data FROM est_produto WHERE NOT JSON_IS_NULL_OR_EMPTY(json_data, \'imagem1\') AND json_data->>"$.imagem1" NOT LIKE \'%thumbnail%\' LIMIT ' . (int)$limit;
        $rProdutosComImagem1 = $conn->fetchAllAssociative($sql);

        foreach ($rProdutosComImagem1 as $produtoComImagem1) {
            echo 'Corrigindo ' . $produtoComImagem1['id'] . '<br>';
            $jsonData = json_decode($produtoComImagem1['json_data'], true);

            $url = $_SERVER['CROSIERAPP_URL'] . '/images/produtos/' . $produtoComImagem1['depto_id'] . '/' . $produtoComImagem1['grupo_id'] . '/' . $produtoComImagem1['subgrupo_id'] . '/' . $jsonData['imagem1'];

            $imgUtils = new ImageUtils();
            $imgUtils->load($url);

            if ($imgUtils->getWidth() !== 50 && $imgUtils->getHeight() !== 50) {

                $pathinfo = pathinfo($url);
                $parsedUrl = parse_url($url);

                $imgUtils->resizeToWidth(50);

                // '%kernel.project_dir%/public/images/produtos'
                $thumbnail = $params->get('kernel.project_dir') . '/public' .
                    str_replace($pathinfo['basename'], '', $parsedUrl['path']) .
                    $pathinfo['filename'] . '_thumbnail.' . $pathinfo['extension'];
                $imgUtils->save($thumbnail);

                $jsonData['imagem1'] = $pathinfo['filename'] . '_thumbnail.' . $pathinfo['extension'];
                $conn->update('est_produto', ['json_data' => json_encode($jsonData)], ['id' => $produtoComImagem1['id']]);
            }
        }
        return new Response('<hr>OK');
    }

    /**
     *
     * @Route("/est/produto/findProdutosByNomeOuFinalCodigo", name="est_produto_findProdutosByNomeOuFinalCodigo")
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProdutosByNomeOuFinalCodigo(Request $request): JsonResponse
    {
        $str = $request->get('term');
        $repoProduto = $this->getDoctrine()->getRepository(Produto::class);
        return new JsonResponse(['results' => $repoProduto->findProdutosByNomeOuFinalCodigo_select2js($str)]);
    }


    /**
     *
     * @Route("/est/produto/listSimpl/", name="est_produto_listSimpl")
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function listSimpl(Request $request): Response
    {
        $params = [
            'formUrl' => '/est/produto/form',
            'listRoute' => 'est_produto_listSimpl',
            'listView' => 'Estoque/produto_listSimpl.html.twig',
        ];

        $params['colunas'] = [
            'id',
            'nome',
            'jsonData.fornecedor_nomeFantasia',
            'jsonData.depto_nome',
        ];

        $fnGetFilterDatas = function (array $params) use ($request): array {
            return [
                new FilterData(['id'], 'EQ', 'id', $params),
                new FilterData(['codigo'], 'LIKE', 'codigo', $params),
                new FilterData(['nome'], 'LIKE', 'nome', $params),
                new FilterData(['depto'], 'EQ', 'depto', $params),
                new FilterData(['grupo'], 'EQ', 'grupo', $params),
                new FilterData(['subgrupo'], 'EQ', 'subgrupo', $params),
                new FilterData(['fornecedor_nomeFantasia', 'fornecedor_nome'], 'LIKE', 'fornecedor', $params, null, true),
            ];
        };


        $params['limit'] = 200;

        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);

        $params['deptos'] = $repoDepto->buildDeptosGruposSubgruposSelect2(
            (int)($request->get('filter')['depto'] ?? null),
            (int)($request->get('filter')['grupo'] ?? null),
            (int)($request->get('filter')['subgrupo'] ?? null));
        $params['grupos'] = json_encode([['id' => 0, 'text' => 'Selecione...']]);
        $params['subgrupos'] = json_encode([['id' => 0, 'text' => 'Selecione...']]);


        $fnHandleDadosList = function (array &$dados, int $totalRegistros) use ($params) {
            if (count($dados) >= $params['limit'] && $totalRegistros > $params['limit']) {
                $this->addFlash('warn', 'Retornando apenas ' . $params['limit'] . ' registros de um total de ' . $totalRegistros . '. Utilize os filtros!');
            }
        };

        return $this->doListSimpl($request, $params, $fnGetFilterDatas, $fnHandleDadosList);
    }


    /**
     *
     * @Route("/est/produto/formPreco/{produto}", name="est_produto_formPreco", requirements={"produto"="\d+"})
     * @param Request $request
     * @param Produto|null $produto
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     * @return JsonResponse
     */
    public function formPreco(Request $request, Produto $produto): JsonResponse
    {
        try {
            $produtoPrecoArr = $request->get('produtoPreco');

            if (!($produtoPrecoArr['lista'] ?? false)) {
                throw new ViewException('É necessário informar "Lista"');
            }
            if (!($produtoPrecoArr['unidade'] ?? false)) {
                throw new ViewException('É necessário informar "Unidade"');
            }
            if (!($produtoPrecoArr['precoCusto'] ?? false)) {
                throw new ViewException('É necessário informar "Preço de Custo"');
            }

            if ($produtoPrecoArr['id']) {
                /** @var ProdutoPrecoRepository $repoProdutoPreco */
                $repoProdutoPreco = $this->getDoctrine()->getRepository(ProdutoPreco::class);
                $preco = $repoProdutoPreco->find($produtoPrecoArr['id']);
            } else {
                $preco = new ProdutoPreco();
            }
            $preco->produto = $produto;

            $repoListaPreco = $this->getDoctrine()->getRepository(ListaPreco::class);
            $preco->lista = $repoListaPreco->find($produtoPrecoArr['lista']);

            $repoUnidade = $this->getDoctrine()->getRepository(Unidade::class);
            $preco->unidade = $repoUnidade->find($produtoPrecoArr['unidade']);

            $preco->precoCusto = DecimalUtils::parseStr($produtoPrecoArr['precoCusto']);
            $preco->coeficiente = DecimalUtils::parseStr($produtoPrecoArr['coeficiente'] ?: 0);
            $preco->margem = DecimalUtils::parseStr($produtoPrecoArr['margem'] ?: 0);
            $preco->custoOperacional = DecimalUtils::parseStr($produtoPrecoArr['custoOperacional'] ?: 0);
            $preco->custoFinanceiro = DecimalUtils::parseStr($produtoPrecoArr['custoFinanceiro'] ?: 0);
            $preco->prazo = DecimalUtils::parseStr($produtoPrecoArr['prazo'] ?: 0);
            $preco->precoPrazo = DecimalUtils::parseStr($produtoPrecoArr['precoPrazo']);
            $preco->precoVista = DecimalUtils::parseStr($produtoPrecoArr['precoVista'] ?: 0);
            $preco->precoPromo = DecimalUtils::parseStr($produtoPrecoArr['precoPromo'] ?: 0);
            $preco->atual = $produtoPrecoArr['atual'] === 'on';
            $preco->dtCusto = DateTimeUtils::parseDateStr($produtoPrecoArr['dtCusto'] ?: (new \DateTime())->format('d/m/Y'));
            $preco->dtPrecoVenda = DateTimeUtils::parseDateStr($produtoPrecoArr['dtCusto'] ?: (new \DateTime())->format('d/m/Y'));

            $this->produtoPrecoEntityHandler->save($preco);

            // campos para facilitar o acesso
            $produto->jsonData['preco_custo'] = $preco->precoCusto;
            if ($preco->lista->descricao === 'VAREJO') {
                $produto->jsonData['preco_varejo'] = $preco->precoPrazo;
            } else if ($preco->lista->descricao === 'ATACADO') {
                $produto->jsonData['preco_atacado'] = $preco->precoPrazo;
            }


            $listasPrecos = [];
            foreach ($produto->precos as $preco) {
                $listasPrecos[$preco->lista->getId()]['lista'] = $preco->lista;
                $listasPrecos[$preco->lista->getId()]['precos'][] = $preco;
            }

            $r = [
                'result' => 'OK',
                'divTbPrecos' => $this->renderView('Estoque/produto_form_estoquesepreco_divTbPrecos.html.twig', ['listasPrecos' => $listasPrecos])
            ];
        } catch (ViewException | \Exception $e) {
            $this->logger->error('Erro - formPreco()');
            $this->logger->error($e->getMessage());
            $msg = $e instanceof ViewException ? $e->getMessage() : 'Erro ao salvar item da composição';
            $r = [
                'result' => 'ERRO',
                'msg' => $msg
            ];
        }
        return new JsonResponse($r);

    }


    /**
     *
     * @Route("/est/produto/precoDelete/{produtoPreco}/", name="est_produto_precoDelete", requirements={"produtoPreco"="\d+"})
     *
     * @param Request $request
     * @param ProdutoPreco $produtoPreco
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function produtoPrecoDelete(Request $request, ProdutoPreco $produtoPreco): RedirectResponse
    {
        try {
            if (!$this->isCsrfTokenValid('est_produto_precoDelete', $request->get('token'))) {
                throw new ViewException('Token inválido');
            }
            $this->produtoPrecoEntityHandler->delete($produtoPreco);
            $this->addFlash('success', 'Registro deletado com sucesso');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao deletar o preço');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('est_produto_form', ['id' => $produtoPreco->produto->getId(), '_fragment' => 'estoqueseprecos']);
    }


    /**
     *
     * @Route("/est/produto/clonar/{produto}/", name="est_produto_clonar", requirements={"produto"="\d+"})
     *
     * @param Request $request
     * @param Produto $produto
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function clonarProduto(Request $request, Produto $produto): RedirectResponse
    {
        try {
            if (!$this->isCsrfTokenValid('est_produto_clonar', $request->get('token'))) {
                throw new ViewException('Token inválido');
            }
            $clone = $this->entityHandler->doClone($produto);
            $this->addFlash('success', 'Registro clonado com sucesso');
            return $this->redirectToRoute('est_produto_form', ['id' => $clone->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao clonar o registro');
            if ($e instanceof ViewException) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('est_produto_form', ['id' => $produto->getId()]);
        }
    }


    /**
     *
     * @Route("/est/produto/stashAdd/", name="est_produto_stashAdd")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function stashAdd(Request $request, SessionInterface $session): JsonResponse
    {
        try {
            $produtosIds = $request->get('ids');
            $produtoStash = $session->get('produtos.stash') ?? [];
            $session->set('produto.stash', array_unique(array_merge($produtoStash, $produtosIds), SORT_REGULAR));
            return new JsonResponse(['result' => 'OK']);
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'ERR']);
        }
    }


    /**
     *
     * @Route("/est/produto/deleteValuesTagsDin/", name="est_produto_deleteValuesTagsDin")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function deleteValuesTagsDin(Request $request, SessionInterface $session): Response
    {
        $campo = $request->get('campo');
        $cache = new FilesystemAdapter($_SERVER['CROSIERAPP_ID'] . '.findValuesTagsDin', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
        $nome = 'findValuesTagsDin_' . $campo;
        $cache->delete($nome);
        return new Response('deletado: ' . $nome);
    }


    /**
     *
     * @Route("/est/produto/findValuesTagsDin/", name="est_produto_findValuesTagsDin")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findValuesTagsDin(Request $request, SessionInterface $session): JsonResponse
    {
        $campo = $request->get('campo');
        $term = $request->get('term');

        $cache = new FilesystemAdapter($_SERVER['CROSIERAPP_ID'] . '.findValuesTagsDin', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
        $vs = $cache->get('findValuesTagsDin_' . $campo, function (ItemInterface $item) use ($campo) {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->getEntityHandler()->getDoctrine()->getRepository(AppConfig::class);
            $jsonMetadata = json_decode($repoAppConfig->findByChave('est_produto_json_metadata'), true);
            $achou = false;
            foreach ($jsonMetadata['campos'] as $campoNoJson => $ig) {
                if ($campoNoJson === $campo) {
                    $achou = true;
                    break;
                }
            }
            if (!$achou) {
                return new JsonResponse(['results' => null]);
            }

            $rs = $this->getEntityHandler()->getDoctrine()->getConnection()
                ->fetchAllAssociative('select distinct(json_data->>"$.' . $campo . '") as v from est_produto');

            $vs = [];
            foreach ($rs as $r) {
                $exp = explode(',', $r['v']);
                foreach ($exp as $e) {
                    $vs[] = $e;
                }
            }
            $vs = array_unique($vs);
            sort($vs);
            return $vs;
        });

        $i = 0;
        $vs = array_filter($vs, function ($v) use ($term, $i) {
            if ($i++ < 30) {
                return $v && (strpos($v, $term) !== FALSE);
            }
        });

        $vs = Select2JsUtils::arrayToSelect2DataKeyEqualValue($vs);
        return new JsonResponse(['results' => $vs]);
    }


    /**
     *
     * @Route("/est/produto/ecommerce/integrarProduto/{produto}", name="est_produto_ecommerce_integrarProduto")
     * @param Request $request
     * @param IntegradorWebStorm $integraWebStormBusiness
     * @param Produto $produto
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     * @return RedirectResponse
     */
    public function integrarProduto(Request $request, IntegradorEcommerceFactory $integradorEcommerceFactory, Produto $produto): RedirectResponse
    {
        try {
            $start = microtime(true);
            $integrarImagens = null;
            if ($request->query->has('integrarImagens')) {
                $integrarImagens = filter_var($request->query->get('integrarImagens'), FILTER_VALIDATE_BOOLEAN);
            } else {
                $integrarImagens = true;
            }
            $integradorEcommerceFactory->getIntegrador()->integraProduto($produto, $integrarImagens);
            $tt = (int)(microtime(true) - $start);
            $this->addFlash('success', 'Produto integrado com sucesso (em ' . $tt . 's)');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao integrar produto (' . $e->getMessage() . ')');
        }
        return $this->redirectToRoute('est_produto_form', ['id' => $produto->getId(), '_fragment' => 'ecommerce']);
    }


    /**
     *
     * @Route("/api/est/produto/findProxCodigo/", name="api_est_produto_findProxCodigo")
     * @param Request $request
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProxCodigo(Connection $conn): JsonResponse
    {
        try {
            $rsProxCodigo = $conn->fetchAssociative('SELECT max(cast(codigo as unsigned))+1 as prox FROM est_produto WHERE codigo < 2147483647');
            $rsProxCodigo['prox'] = $rsProxCodigo['prox'] ?: 1; 
            return CrosierApiResponse::success($rsProxCodigo);
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }


}
