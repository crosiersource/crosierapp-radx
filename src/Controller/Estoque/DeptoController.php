<?php

namespace App\Controller\Estoque;

use App\Business\Ecommerce\IntegradorTray;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\DeptoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\SubgrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\GrupoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\SubgrupoRepository;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class DeptoController extends BaseController
{

    private DeptoEntityHandler $deptoEntityHandler;

    private GrupoEntityHandler $grupoEntityHandler;

    private SubgrupoEntityHandler $subgrupoEntityHandler;

    /**
     * @required
     * @param DeptoEntityHandler $deptoEntityHandler
     */
    public function setDeptoEntityHandler(DeptoEntityHandler $deptoEntityHandler): void
    {
        $this->deptoEntityHandler = $deptoEntityHandler;
    }

    /**
     * @required
     * @param GrupoEntityHandler $grupoEntityHandler
     */
    public function setGrupoEntityHandler(GrupoEntityHandler $grupoEntityHandler): void
    {
        $this->grupoEntityHandler = $grupoEntityHandler;
    }

    /**
     * @required
     * @param SubgrupoEntityHandler $subgrupoEntityHandler
     */
    public function setSubgrupoEntityHandler(SubgrupoEntityHandler $subgrupoEntityHandler): void
    {
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
    }


    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/form", name="est_deptoGrupoSubgrupo_form")
     * @param Request $request
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request)
    {
        $parameters = [
            'deptos' => [],
        ];

        try {
            /** @var DeptoRepository $repoDepto */
            $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
            $deptos = $repoDepto->findAll(['codigo' => 'ASC']);
            /** @var Connection $conn */
            $conn = $this->getDoctrine()->getConnection();
            $stmt_qtdePorSubgrupo = $conn->prepare('SELECT count(*) as qt FROM est_produto WHERE subgrupo_id = :subgrupoId');
            /** @var Depto $depto */
            foreach ($deptos as $depto) {
                foreach ($depto->grupos as $grupo) {
                    foreach ($grupo->subgrupos as $subgrupo) {
                        $stmt_qtdePorSubgrupo->bindValue('subgrupoId', $subgrupo->getId());
                        $rs_qtdePorSubgrupo = $stmt_qtdePorSubgrupo->executeQuery()->fetchAssociative();
                        $subgrupo->qtdeTotalProdutos = $rs_qtdePorSubgrupo['qt'] ?? 0;
                    }
                }
            }
            $parameters['deptos'] = $deptos;
            /** @var GrupoRepository $repoGrupo */
            $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
            if ($request->get('grupoId')) {
                /** @var Grupo $grupo */
                $grupo = $repoGrupo->find($request->get('grupoId'));
                $parameters['grupoSelected'] = $grupo;
                $parameters['deptoSelected'] = $grupo->depto;
            } else if ($request->get('deptoId')) {
                /** @var Depto $depto */
                $depto = $repoDepto->find($request->get('deptoId'));
                $parameters['deptoSelected'] = $depto;
                if ($depto->grupos) {
                    /** @var Grupo $grupo */
                    $grupo = $depto->grupos->first();
                    if ($grupo) {
                        $parameters['grupoSelected'] = $grupo;
                    }
                }
            }
        } catch (\Throwable $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->addFlash('error', $msg);
        }

        return $this->doRender('Estoque/deptoGrupoSubgrupo.html.twig', $parameters);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/deptoSave", name="est_deptoGrupoSubgrupo_deptoSave")
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function deptoSave(Request $request): RedirectResponse
    {
        $deptoArr = $request->get('depto');
        if ($deptoArr['id'] ?? false) {
            /** @var DeptoRepository $repoDepto */
            $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
            $depto = $repoDepto->find($deptoArr['id']);
        } else {
            $depto = new Depto();
        }
        $depto->codigo = $deptoArr['codigo'];
        $depto->nome = $deptoArr['nome'];
        $this->deptoEntityHandler->save($depto);
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['deptoId' => $depto->getId(), '_fragment' => 'gerenciar']);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/deptoDelete/{depto}", name="est_deptoGrupoSubgrupo_deptoDelete", requirements={"depto"="\d+"})
     * @param Depto $depto
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function deptoDelete(Depto $depto): RedirectResponse
    {

        try {
            $this->deptoEntityHandler->delete($depto);
            $this->addFlash('success', 'Depto deletado com sucesso');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao deletar o depto');
        }
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form');
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/grupoSave", name="est_deptoGrupoSubgrupo_grupoSave")
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function grupoSave(Request $request): RedirectResponse
    {
        $grupoArr = $request->get('grupo');
        if ($grupoArr['id'] ?? false) {
            /** @var GrupoRepository $repoGrupo */
            $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
            $grupo = $repoGrupo->find($grupoArr['id']);
        } else {
            $grupo = new Grupo();
        }

        /** @var DeptoRepository $repoDepto */
        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
        /** @var Depto $depto */
        $depto = $repoDepto->find($grupoArr['deptoId']);
        $grupo->depto = $depto;
        $grupo->codigo = $grupoArr['codigo'];
        $grupo->nome = $grupoArr['nome'];
        $this->grupoEntityHandler->save($grupo);
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $grupo->getId(), '_fragment' => 'gerenciar']);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/grupoDelete/{grupo}", name="est_deptoGrupoSubgrupo_grupoDelete", requirements={"grupo"="\d+"})
     * @param Grupo $grupo
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function grupoDelete(Grupo $grupo): RedirectResponse
    {
        try {
            $this->grupoEntityHandler->delete($grupo);
            $this->addFlash('success', 'Grupo deletado com sucesso');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao deletar o grupo');
        }
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['deptoId' => $grupo->depto->getId()]);
    }


    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/subgrupoSave", name="est_deptoGrupoSubgrupo_subgrupoSave")
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function subgrupoSave(Request $request): RedirectResponse
    {
        $subgrupoArr = $request->get('subgrupo');
        if ($subgrupoArr['id'] ?? false) {
            /** @var SubgrupoRepository $repoSubgrupo */
            $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);
            $subgrupo = $repoSubgrupo->find($subgrupoArr['id']);
        } else {
            $subgrupo = new Subgrupo();
        }

        /** @var GrupoRepository $repoGrupo */
        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        /** @var Grupo $grupo */
        $grupo = $repoGrupo->find($subgrupoArr['grupoId']);
        $subgrupo->grupo = $grupo;
        $subgrupo->codigo = $subgrupoArr['codigo'];
        $subgrupo->nome = $subgrupoArr['nome'];
        $this->subgrupoEntityHandler->save($subgrupo);
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $grupo->getId(), '_fragment' => 'gerenciar']);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/subgrupoDelete/{subgrupo}", name="est_deptoGrupoSubgrupo_subgrupoDelete", requirements={"subgrupo"="\d+"})
     * @param Subgrupo $subgrupo
     * @return RedirectResponse
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function subgrupoDelete(Subgrupo $subgrupo): RedirectResponse
    {
        try {
            $this->subgrupoEntityHandler->delete($subgrupo);
            $this->addFlash('success', 'Subgrupo deletado com sucesso');
        } catch (ViewException $e) {
            $this->addFlash('error', 'Erro ao deletar o subgrupo');
        }
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $subgrupo->grupo->getId()]);
    }


    /**
     * Corrige os json_data de est_subgrupo e est_grupo
     *
     * @Route("/est/deptoGrupoSubgrupo/corrigir", name="est_deptoGrupoSubgrupo_corrigir")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function corrigirDeptosGruposSubgrupos(): Response
    {
        try {// corrige os subgrupos
            $conn = $this->deptoEntityHandler->getDoctrine()->getConnection();

            $conn->executeQuery('
                UPDATE 
                    est_produto p, est_subgrupo sg, est_grupo g, est_depto d 
                SET 
                    p.grupo_id = g.id, 
                    p.depto_id = d.id 
                WHERE 
                      p.subgrupo_id = sg.id AND 
                      sg.grupo_id = g.id AND 
                      g.depto_id = d.id');


            $subgrupos = $conn->fetchAllAssociative('
                SELECT 
                       s.id as subgrupo_id, 
                       s.codigo as subgrupo_codigo, 
                       s.nome as subgrupo_nome, 
                       g.id as grupo_id, 
                       g.codigo as grupo_codigo, 
                       g.nome as grupo_nome, 
                       d.id as depto_id, 
                       d.codigo as depto_codigo, 
                       d.nome as depto_nome 
                FROM 
                     est_subgrupo s, est_grupo g, est_depto d 
                WHERE 
                      s.grupo_id = g.id AND g.depto_id = d.id'
            );
            foreach ($subgrupos as $s) {
                $conn->update('est_subgrupo',
                    [
                        'json_data' => json_encode([
                            'depto_id' => $s['depto_id'],
                            'depto_codigo' => $s['depto_codigo'],
                            'depto_nome' => $s['depto_nome'],
                            'grupo_id' => $s['grupo_id'],
                            'grupo_codigo' => $s['grupo_codigo'],
                            'grupo_nome' => $s['grupo_nome'],
                        ])
                    ], ['id' => $s['subgrupo_id']]);
                $conn->update('est_grupo',
                    [
                        'json_data' => json_encode([
                            'depto_id' => $s['depto_id'],
                            'depto_codigo' => $s['depto_codigo'],
                            'depto_nome' => $s['depto_nome'],
                        ])
                    ], ['id' => $s['grupo_id']]);
            }
            return new Response('OK');
        } catch (\Throwable $e) {
            return new Response('ERRO');
        }
    }


    /**
     * Corrige os json_data de est_subgrupo e est_grupo
     *
     * @Route("/est/deptoGrupoSubgrupo/importar", name="est_deptoGrupoSubgrupo_importar")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function importar(DeptoEntityHandler    $deptoEntityHandler,
                             GrupoEntityHandler    $grupoEntityHandler,
                             SubgrupoEntityHandler $subgrupoEntityHandler,
                             IntegradorTray        $integradorTray): Response
    {
        $txt = file_get_contents('/home/carlos/Downloads/categorias+ecommerce.csv');

        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);

        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);

        $linhas = explode("\n", $txt);

        $deptoNome = '';
        $grupoNome = '';

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();


        try {
            foreach ($linhas as $i => $linha) {
                $campos = explode(";", $linha);
                if ($campos[0] === '"<<FIM>>"') {
                    break;
                }

                $deptoNome_novo = str_replace('"', '', trim(mb_strtoupper($campos[0])));
                if ($deptoNome_novo !== '-') {
                    $deptoNome = $deptoNome_novo . ' (NOVO)';
                }

                $grupoNome_novo = str_replace('"', '', trim(mb_strtoupper($campos[1])));
                if ($grupoNome_novo !== '-') {
                    $grupoNome = $grupoNome_novo . ' (NOVO)';
                }

                $subgrupoNome = str_replace('"', '', trim(mb_strtoupper($campos[2])));

                /** @var Depto $depto */
                $depto = $repoDepto->findOneByNome($deptoNome);
                if (!$depto) {
                    $depto = new Depto();

                    $proximo = $conn->fetchAssociative('SELECT max(codigo) as proximo FROM est_depto');
                    if (!($proximo['proximo'] ?? false)) {
                        $depto->codigo = '9001';
                    } else {
                        $depto->codigo = ((int)$proximo['proximo']) + 1;
                    }

                    $depto->nome = mb_strtoupper($deptoNome);
                    $depto = $deptoEntityHandler->save($depto);
                }

                /** @var Grupo $grupo */
                $grupo = $repoGrupo->findOneByFiltersSimpl([['nome', 'EQ', $grupoNome], ['depto', 'EQ', $depto]]);
                if (!$grupo) {
                    $grupo = new grupo();
                    $grupo->depto = $depto;
                    $proximo = $conn->fetchAssociative('SELECT max(codigo) as proximo FROM est_grupo WHERE depto_id = :deptoId', ['deptoId' => $depto->getId()]);
                    if (!($proximo['proximo'] ?? false)) {
                        $grupo->codigo = $depto->codigo . '01';
                    } else {
                        $grupo->codigo = str_pad(($proximo['proximo'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
                    }
                    $grupo->nome = mb_strtoupper($grupoNome);
                    $grupo = $grupoEntityHandler->save($grupo);
                }


                /** @var Subgrupo $subgrupo */
                $subgrupo = $repoSubgrupo->findOneByFiltersSimpl([['nome', 'EQ', $subgrupoNome], ['grupo', 'EQ', $grupo]]);
                if (!$subgrupo) {
                    $subgrupo = new Subgrupo();
                    $subgrupo->grupo = $grupo;

                    $proximo = $conn->fetchAssociative('SELECT max(codigo) as proximo FROM est_subgrupo WHERE grupo_id = :grupoId', ['grupoId' => $grupo->getId()]);
                    if (!($proximo['proximo'] ?? false)) {
                        $subgrupo->codigo = $grupo->codigo . '01';
                    } else {
                        $subgrupo->codigo = str_pad(($proximo['proximo'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
                    }

                    $subgrupo->nome = mb_strtoupper($subgrupoNome);
                    $subgrupoEntityHandler->save($subgrupo);
                }
                if (!($subgrupo->jsonData['ecommerce_id'] ?? false)) {
                    $integradorTray->integraSubgrupo($subgrupo);
                }


            }
        } catch (\Throwable $e) {
            $msg = ExceptionUtils::treatException($e);
            $bla = 1;
        }

        return new Response('OK');
    }


    /**
     * @Route("/est/deptoGrupoSubgrupo/integrarTodasAsCategorias", name="est_deptoGrupoSubgrupo_integrarTodasAsCategorias")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integrarTodasAsCategorias(IntegradorTray $integradorTray): Response
    {
        $todosOsSubgrupos = $this->getDoctrine()->getRepository(Subgrupo::class)->findAll();
        foreach ($todosOsSubgrupos as $subgrupo) {
            // if ($subgrupo->grupo->depto->codigo === '00') continue;
            $integradorTray->integraSubgrupo($subgrupo);
        }
        return new Response('ok');
    }


    /**
     * Corrige os json_data de est_subgrupo e est_grupo
     *
     * @Route("/est/deptoGrupoSubgrupo/corrigirProdutos", name="est_deptoGrupoSubgrupo_corrigirProdutos")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function corrigirProdutos(): Response
    {
        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();
        $conn->executeStatement("
            UPDATE 
              est_produto produto, est_subgrupo sg, est_grupo g, est_depto d 
              SET 
                produto.depto_id = d.id, 
                produto.grupo_id = g.id, 
                produto.json_data = 
                    json_set(produto.json_data, 
                    '$.depto_id', d.id, 
                    '$.depto_nome', d.nome, 
                    '$.depto_codigo', d.codigo, 
                    '$.grupo_id', g.id, 
                    '$.grupo_nome', g.nome, 
                    '$.grupo_codigo', g.codigo, 
                    '$.subgrupo_nome', sg.nome, 
                    '$.subgrupo_codigo', sg.codigo) 
                WHERE 
                    produto.subgrupo_id = sg.id AND 
                    sg.grupo_id = g.id AND 
                    g.depto_id = d.id AND 
                    (produto.grupo_id != g.id OR produto.depto_id != d.id)");
        return new Response('OK');
    }


    /**
     * @Route("/api/est/deptoGrupoSubgrupo/migrar", name="est_deptoGrupoSubgrupo_migrar")
     * @return Response
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function migrar(Request $request, ProdutoEntityHandler $produtoEntityHandler): JsonResponse
    {
        try {
            $content = json_decode($request->getContent(), true);
            $repoProduto = $this->getDoctrine()->getRepository(Produto::class);

            $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
            $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
            $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);

            $deptoDe = $repoSubgrupo->find($content['deptoDe']['id']);
            $grupoDe = $repoSubgrupo->find($content['grupoDe']['id']);
            $subgrupoDe = $repoSubgrupo->find($content['subgrupoDe']['id']);

            $deptoPara = $repoDepto->find($content['deptoPara']['id']);
            $grupoPara = $repoGrupo->find($content['grupoPara']['id']);
            $subgrupoPara = $repoSubgrupo->find($content['subgrupoPara']['id']);

            $produtos = $repoProduto->findAllByFiltersSimpl([
                ['depto', 'EQ', $deptoDe],
                ['grupo', 'EQ', $grupoDe],
                ['subgrupo', 'EQ', $subgrupoDe],
            ]);
            /** @var Connection $conn */
            $conn = $this->getDoctrine()->getConnection();
            $conn->beginTransaction();
            /** @var Produto $produto */
            foreach ($produtos as $produto) {
                $produto->depto = $deptoPara;
                $produto->grupo = $grupoPara;
                $produto->subgrupo = $subgrupoPara;
                if ($produto->ecommerce) {
                    $produto->json_data['ecommerce_desatualizado'] = 'S';
                }
                $produtoEntityHandler->save($produto);
            }
            $conn->commit();
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::error();
        }

    }


    /**
     * @Route("/est/deptoGrupoSubgrupo/importarDaTray", name="est_deptoGrupoSubgrupo_importarDaTray")
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function importarDaTray(DeptoEntityHandler    $deptoEntityHandler,
                                   GrupoEntityHandler    $grupoEntityHandler,
                                   SubgrupoEntityHandler $subgrupoEntityHandler,
                                   IntegradorTray        $integradorTray): Response
    {
        $rs = $integradorTray->obterCategorias();
        /// $json = '[{"Category":{"has_product":"1","id":"9001","parent_id":"","name":"CONVENI\u00caNCIA E NUTRI\u00c7\u00c3O (NOVO)","description":"","order":"154","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9002","parent_id":"","name":"CUIDADOS PESSOAIS (NOVO)","description":"","order":"192","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9003","parent_id":"","name":"DERMOCOSM\u00c9TICOS (NOVO)","description":"","order":"265","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9004","parent_id":"","name":"MAM\u00c3E, BEB\u00ca E CRIAN\u00c7A (NOVO)","description":"","order":"300","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9005","parent_id":"","name":"MEDICAMENTOS (NOVO)","description":"","order":"337","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9006","parent_id":"","name":"ORTOP\u00c9DICOS (NOVO)","description":"","order":"403","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"9007","parent_id":"","name":"OUTROS CUIDADOS (NOVO)","description":"","order":"418","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900101","parent_id":"9001","name":"ALIMENTOS (NOVO)","description":"","order":"155","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900102","parent_id":"9001","name":"BEBIDAS (NOVO)","description":"","order":"160","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900103","parent_id":"9001","name":"DOCES (NOVO)","description":"","order":"168","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900104","parent_id":"9001","name":"NUTRI\u00c7\u00c3O SAUD\u00c1VEL (NOVO)","description":"","order":"173","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900105","parent_id":"9001","name":"SUPLEMENTO NUTRICIONAL (NOVO)","description":"","order":"182","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900106","parent_id":"9001","name":"UTILIDADES (NOVO)","description":"","order":"184","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900201","parent_id":"9002","name":"ACESS\u00d3RIOS PARA CABELO E BARBA (NOVO)","description":"","order":"193","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900202","parent_id":"9002","name":"CUIDADOS CABELO E BARBA (NOVO)","description":"","order":"203","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900203","parent_id":"9002","name":"CUIDADOS \u00cdNTIMOS (NOVO)","description":"","order":"220","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900204","parent_id":"9002","name":"CUIDADOS M\u00c3OS E P\u00c9S (NOVO)","description":"","order":"230","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900205","parent_id":"9002","name":"DEPILA\u00c7\u00c3O MASCULINA E FEMININA (NOVO)","description":"","order":"240","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900206","parent_id":"9002","name":"DESODORANTES (NOVO)","description":"","order":"244","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900207","parent_id":"9002","name":"FRAGR\u00c2NCIAS (NOVO)","description":"","order":"248","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900208","parent_id":"9002","name":"HIGIENE (NOVO)","description":"","order":"251","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900209","parent_id":"9002","name":"HIGIENE ORAL (NOVO)","description":"","order":"257","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900301","parent_id":"9003","name":"CUIDADOS DA PELE (NOVO)","description":"","order":"266","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900302","parent_id":"9003","name":"MAQUIAGEM (NOVO)","description":"","order":"286","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900303","parent_id":"9003","name":"NUTRICOSM\u00c9TICOS (NOVO)","description":"","order":"294","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900401","parent_id":"9004","name":"CUIDADOS DA MAM\u00c3E (NOVO)","description":"","order":"301","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900402","parent_id":"9004","name":"HIGIENE ORAL INFANTIL (NOVO)","description":"","order":"306","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900403","parent_id":"9004","name":"HORA DA TROCA DA FRALDA (NOVO)","description":"","order":"312","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900404","parent_id":"9004","name":"HORA DO BANHO (NOVO)","description":"","order":"318","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900405","parent_id":"9004","name":"ITENS INFANTIS (NOVO)","description":"","order":"325","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900406","parent_id":"9004","name":"NUTRI\u00c7\u00c3O INFANTIL (NOVO)","description":"","order":"332","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900501","parent_id":"9005","name":"ANTICONCEPCIONAL (NOVO)","description":"","order":"338","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900502","parent_id":"9005","name":"CONTROLADOS (NOVO)","description":"","order":"343","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900503","parent_id":"9005","name":"FITOTER\u00c1PICOS (NOVO)","description":"","order":"345","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"900504","parent_id":"9005","name":"OFT\u00c1LMICOS (NOVO)","description":"","order":"350","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900505","parent_id":"9005","name":"PARA TODO O CORPO (NOVO)","description":"","order":"357","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900506","parent_id":"9005","name":"USO CONTINUO (NOVO)","description":"","order":"382","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900507","parent_id":"9005","name":"VITAMINAS (NOVO)","description":"","order":"390","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900601","parent_id":"9006","name":"ACESS\u00d3RIO ORTOP\u00c9DICO (NOVO)","description":"","order":"404","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900602","parent_id":"9006","name":"OUTROS ACESS\u00d3RIOS ORTOP\u00c9DICOS  (NOVO)","description":"","order":"415","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900701","parent_id":"9007","name":"ACESS\u00d3RIOS ENFERMAGEM (NOVO)","description":"","order":"419","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"900702","parent_id":"9007","name":"PRIMEIROS SOCORROS (NOVO)","description":"","order":"431","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010101","parent_id":"900101","name":"BOLACHAS E BISCOITOS","description":"","order":"156","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010102","parent_id":"900101","name":"MACARR\u00c3O INSTANT\u00c2NEO","description":"","order":"157","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010103","parent_id":"900101","name":"SALDINHOS","description":"","order":"158","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010104","parent_id":"900101","name":"TEMPEROS","description":"","order":"159","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010201","parent_id":"900102","name":"\u00c1GUA MINERAL","description":"","order":"161","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010202","parent_id":"900102","name":"BEBIDA L\u00c1CTEA\/SOJA","description":"","order":"162","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010203","parent_id":"900102","name":"CH\u00c1S","description":"","order":"163","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010204","parent_id":"900102","name":"ENERG\u00c9TICO","description":"","order":"164","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010205","parent_id":"900102","name":"ISOT\u00d4NICOS","description":"","order":"165","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010206","parent_id":"900102","name":"REFRIGERANTE","description":"","order":"166","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010207","parent_id":"900102","name":"SUCO\/N\u00c9CTAR","description":"","order":"167","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010301","parent_id":"900103","name":"BARRA DE CEREAL","description":"","order":"169","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010302","parent_id":"900103","name":"CHOCOLATES","description":"","order":"170","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010303","parent_id":"900103","name":"DOCES","description":"","order":"171","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010304","parent_id":"900103","name":"PASTILHAS","description":"","order":"172","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010401","parent_id":"900104","name":"ADO\u00c7ANTES","description":"","order":"174","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010402","parent_id":"900104","name":"ALIMENTOS DIET\/LIGHT","description":"","order":"175","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010403","parent_id":"900104","name":"BEBIDAS DIET\/LIGHT","description":"","order":"176","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010404","parent_id":"900104","name":"EMAGRECEDORES","description":"","order":"177","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010405","parent_id":"900104","name":"ENERG\u00c9TICOS","description":"","order":"178","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010406","parent_id":"900104","name":"NUTRI\u00c7\u00c3O ESPORTIVA","description":"","order":"179","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010407","parent_id":"900104","name":"OUTROS","description":"","order":"180","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010408","parent_id":"900104","name":"SUPLEMENTA\u00c7\u00c3O","description":"","order":"181","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010501","parent_id":"900105","name":"SUPLEMENTO S\u00caNIOR","description":"","order":"183","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010601","parent_id":"900106","name":"ACESS\u00d3RIOS DIVERSOS","description":"","order":"185","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010602","parent_id":"900106","name":"BALAN\u00c7A DIGITAL","description":"","order":"186","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010603","parent_id":"900106","name":"ELETR\u00d4NICOS","description":"","order":"187","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010604","parent_id":"900106","name":"MASSAGEADOR","description":"","order":"188","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010605","parent_id":"900106","name":"OUTROS","description":"","order":"189","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90010606","parent_id":"900106","name":"PILHA\/BATERIA","description":"","order":"190","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90010607","parent_id":"900106","name":"UMIDIFICADOR DE AR","description":"","order":"191","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020101","parent_id":"900201","name":"ACESS\u00d3RIO PARA BARBEAR","description":"","order":"194","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020102","parent_id":"900201","name":"ACESS\u00d3RIOS PARA COLORA\u00c7\u00c3O","description":"","order":"195","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020103","parent_id":"900201","name":"APARELHO\/CARGA PARA BARBEAR","description":"","order":"196","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020104","parent_id":"900201","name":"CORTADOR\/APARADOR DE CABELO E BARBA","description":"","order":"197","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020105","parent_id":"900201","name":"ESCOVAS E PENTES","description":"","order":"198","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020106","parent_id":"900201","name":"MODELADORES","description":"","order":"199","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020107","parent_id":"900201","name":"PRENDEDORES DE CABELO","description":"","order":"200","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020108","parent_id":"900201","name":"SECADOR\/PRANCHA\/MODELADORA","description":"","order":"201","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020109","parent_id":"900201","name":"TOUCAS","description":"","order":"202","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020201","parent_id":"900202","name":"ALISANTES","description":"","order":"204","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020202","parent_id":"900202","name":"COLORA\u00c7\u00c3O PARA CABELO E BARBA","description":"","order":"205","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020203","parent_id":"900202","name":"COLORA\u00c7\u00c3O PERMANENTE","description":"","order":"206","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020204","parent_id":"900202","name":"COLORA\u00c7\u00c3O TEMPOR\u00c1RIA","description":"","order":"207","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020205","parent_id":"900202","name":"CONDICIONADOR","description":"","order":"208","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020206","parent_id":"900202","name":"CREME DE PENTEAR E LEAVE-IN","description":"","order":"209","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020207","parent_id":"900202","name":"CREME PENTEAR","description":"","order":"210","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020208","parent_id":"900202","name":"CUIDADOS BARBA","description":"","order":"211","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020209","parent_id":"900202","name":"DESCOLORANTES","description":"","order":"212","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020210","parent_id":"900202","name":"FINALIZADORES","description":"","order":"213","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020211","parent_id":"900202","name":"KITS DE CUIDADOS","description":"","order":"214","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020212","parent_id":"900202","name":"MASCARA","description":"","order":"215","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020213","parent_id":"900202","name":"\u00d3LEO CAPILAR","description":"","order":"216","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020214","parent_id":"900202","name":"SHAMPOO","description":"","order":"217","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020215","parent_id":"900202","name":"TONALIZANTES","description":"","order":"218","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020216","parent_id":"900202","name":"TRATAMENTOS E REPARADORES","description":"","order":"219","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020301","parent_id":"900203","name":"ABSORVENTES","description":"","order":"221","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020302","parent_id":"900203","name":"ACESS\u00d3RIOS \u00cdNTIMOS","description":"","order":"222","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020303","parent_id":"900203","name":"FRALDA GERI\u00c1TRICA","description":"","order":"223","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020304","parent_id":"900203","name":"HIGIENE \u00cdNTIMA","description":"","order":"224","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020305","parent_id":"900203","name":"LUBRIFICANTES","description":"","order":"225","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020306","parent_id":"900203","name":"OUTROS CUIDADOS","description":"","order":"226","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020307","parent_id":"900203","name":"PRESERVATIVOS","description":"","order":"227","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020308","parent_id":"900203","name":"ROUPA \u00cdNTIMA","description":"","order":"228","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020309","parent_id":"900203","name":"TRATAMENTO PARA ASSADURAS","description":"","order":"229","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020401","parent_id":"900204","name":"ACESS\u00d3RIO PARA CUIDADOS","description":"","order":"231","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020402","parent_id":"900204","name":"ANTIMIC\u00d3TICO\/ANTIF\u00daNGICO T\u00d3PICO","description":"","order":"232","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020403","parent_id":"900204","name":"ANTISS\u00c9PTICO PARA P\u00c9S","description":"","order":"233","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020404","parent_id":"900204","name":"CREME P\u00c9S E M\u00c3OS","description":"","order":"234","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020405","parent_id":"900204","name":"ESFOLIANTE P\u00c9S E M\u00c3OS","description":"","order":"235","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020406","parent_id":"900204","name":"ESMALTES","description":"","order":"236","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020407","parent_id":"900204","name":"FORTALECEDORES DE UNHA","description":"","order":"237","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020408","parent_id":"900204","name":"REMOVEDOR DE ESMALTE","description":"","order":"238","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020409","parent_id":"900204","name":"UNHA POSTI\u00c7A\/COLA","description":"","order":"239","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020501","parent_id":"900205","name":"APARELHOS PARA DEPILA\u00c7\u00c3O","description":"","order":"241","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020502","parent_id":"900205","name":"P\u00d3S DEPILA\u00c7\u00c3O","description":"","order":"242","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020503","parent_id":"900205","name":"PRODUTOS PARA DEPILA\u00c7\u00c3O","description":"","order":"243","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020601","parent_id":"900206","name":"DESODORANTE AEROSSOL","description":"","order":"245","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020602","parent_id":"900206","name":"DESODORANTE CREME\/SPRAY\/STICK","description":"","order":"246","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020603","parent_id":"900206","name":"DESODORANTE ROLL-ON","description":"","order":"247","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020701","parent_id":"900207","name":"PERFUMES\/COL\u00d4NIAS FEMININAS","description":"","order":"249","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020702","parent_id":"900207","name":"PERFUMES\/COL\u00d4NIAS MASCULINAS","description":"","order":"250","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020801","parent_id":"900208","name":"ACESS\u00d3RIOS PARA BANHO","description":"","order":"252","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020802","parent_id":"900208","name":"PAPEL HIGI\u00caNICO","description":"","order":"253","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020803","parent_id":"900208","name":"SABONETE BARRA","description":"","order":"254","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020804","parent_id":"900208","name":"SABONETE \u00cdNTIMO","description":"","order":"255","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020805","parent_id":"900208","name":"SABONETE L\u00cdQUIDO","description":"","order":"256","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020901","parent_id":"900209","name":"ACESS\u00d3RIO DENTAL","description":"","order":"258","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020902","parent_id":"900209","name":"CREME DENTAL","description":"","order":"259","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020903","parent_id":"900209","name":"CUIDADOS COM PR\u00d3TESE\/DENTADURA","description":"","order":"260","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020904","parent_id":"900209","name":"ENXAGUANTE E SOLU\u00c7\u00c3O BUCAL","description":"","order":"261","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90020905","parent_id":"900209","name":"ESCOVA DENTAL","description":"","order":"262","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020906","parent_id":"900209","name":"FIO E FITA DENTAL","description":"","order":"263","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90020907","parent_id":"900209","name":"KIT HIGIENE ORAL","description":"","order":"264","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030101","parent_id":"900301","name":"ANTIACNE","description":"","order":"267","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030102","parent_id":"900301","name":"ANTISSINAIS","description":"","order":"268","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030103","parent_id":"900301","name":"\u00c1REA DOS OLHOS","description":"","order":"269","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030104","parent_id":"900301","name":"BRONZEADOR","description":"","order":"270","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030105","parent_id":"900301","name":"CALMANTE PARA PELE","description":"","order":"271","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030106","parent_id":"900301","name":"CELULITES E ESTRIAS","description":"","order":"272","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030107","parent_id":"900301","name":"CICATRIZANTE","description":"","order":"273","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030108","parent_id":"900301","name":"CLAREADOR DE MANCHA","description":"","order":"274","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030109","parent_id":"900301","name":"CUIDADOS CORPORAL","description":"","order":"275","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030110","parent_id":"900301","name":"CUIDADOS LABIAL","description":"","order":"276","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030111","parent_id":"900301","name":"ESFOLIANTES","description":"","order":"277","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030112","parent_id":"900301","name":"LIMPEZA DA PELE FACIAL","description":"","order":"278","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030113","parent_id":"900301","name":"OUTROS CUIDADOS FACIAIS","description":"","order":"279","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030114","parent_id":"900301","name":"PROTETOR SOLAR","description":"","order":"280","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030115","parent_id":"900301","name":"PROTETOR SOLAR FACIAL","description":"","order":"281","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030116","parent_id":"900301","name":"PROTETOR SOLAR INFANTIL","description":"","order":"282","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030117","parent_id":"900301","name":"REPELENTES ADULTOS","description":"","order":"283","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030118","parent_id":"900301","name":"REPELENTES INFANTIS","description":"","order":"284","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030119","parent_id":"900301","name":"TRATAMENTO P\u00c9S E M\u00c3OS","description":"","order":"285","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030201","parent_id":"900302","name":"ACESS\u00d3RIO PARA LIMPEZA","description":"","order":"287","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030202","parent_id":"900302","name":"ACESS\u00d3RIO PARA MAQUIAGEM","description":"","order":"288","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030203","parent_id":"900302","name":"DEMAQUILANTES","description":"","order":"289","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030204","parent_id":"900302","name":"L\u00c1BIOS","description":"","order":"290","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030205","parent_id":"900302","name":"OLHOS","description":"","order":"291","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030206","parent_id":"900302","name":"OUTROS PRODUTOS","description":"","order":"292","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90030207","parent_id":"900302","name":"PELE","description":"","order":"293","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030301","parent_id":"900303","name":"ANTI-CELULITE E FIRMADORES","description":"","order":"295","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030302","parent_id":"900303","name":"ANTI-IDADE","description":"","order":"296","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030303","parent_id":"900303","name":"CABELOS E UNHAS","description":"","order":"297","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030304","parent_id":"900303","name":"REDUTOR DE MEDIDAS","description":"","order":"298","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90030305","parent_id":"900303","name":"SISTEMA CIRCULAT\u00d3RIO","description":"","order":"299","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040101","parent_id":"900401","name":"HIDRATANTES PARA MAM\u00c3E","description":"","order":"302","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040102","parent_id":"900401","name":"ORTOP\u00c9DICOS PARA MAM\u00c3E","description":"","order":"303","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040103","parent_id":"900401","name":"PROTETOR\/CONCHAS PARA SEIOS","description":"","order":"304","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040104","parent_id":"900401","name":"TIRA LEITE","description":"","order":"305","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040201","parent_id":"900402","name":"ACESS\u00d3RIO DENTAL","description":"","order":"307","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040202","parent_id":"900402","name":"CREME DENTAL INFANTIL","description":"","order":"308","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040203","parent_id":"900402","name":"ENXAGUANTE BUCAL INFANTIL","description":"","order":"309","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040204","parent_id":"900402","name":"ESCOVA DENTAL INFANTIL","description":"","order":"310","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040205","parent_id":"900402","name":"FIO DENTAL INFANTIL","description":"","order":"311","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040301","parent_id":"900403","name":"FRALDA INFANTIL","description":"","order":"313","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040302","parent_id":"900403","name":"LEN\u00c7O UMEDECIDO","description":"","order":"314","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040303","parent_id":"900403","name":"OUTROS","description":"","order":"315","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040304","parent_id":"900403","name":"TALCO INFANTIL","description":"","order":"316","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040305","parent_id":"900403","name":"TRATAMENTO PARA ASSADURAS","description":"","order":"317","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040401","parent_id":"900404","name":"ACESS\u00d3RIO PARA BANHO","description":"","order":"319","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040402","parent_id":"900404","name":"CUIDADOS COM OS CABELOS","description":"","order":"320","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040403","parent_id":"900404","name":"HIDRATANTE INFANTIL","description":"","order":"321","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040404","parent_id":"900404","name":"KITS INFANTIS","description":"","order":"322","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040405","parent_id":"900404","name":"\u00d3LEOS E COL\u00d4NIAS","description":"","order":"323","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040406","parent_id":"900404","name":"SABONETE INFANTIL","description":"","order":"324","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040501","parent_id":"900405","name":"BRINQUEDOS","description":"","order":"326","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040502","parent_id":"900405","name":"CHUPETAS","description":"","order":"327","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040503","parent_id":"900405","name":"CUIDADOS INFANTIS","description":"","order":"328","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040505","parent_id":"900405","name":"ALIMENTA\u00c7\u00c3O\/AMAMENTA\u00c7\u00c3O","description":"","order":"330","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040506","parent_id":"900405","name":"MORDEDORES","description":"","order":"331","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040601","parent_id":"900406","name":"CEREAIS INFANTIS","description":"","order":"333","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040602","parent_id":"900406","name":"F\u00d3RMULAS INFANTIS","description":"","order":"334","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90040603","parent_id":"900406","name":"PAPINHAS","description":"","order":"335","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90040604","parent_id":"900406","name":"SUPLEMENTOS ALIMENTARES","description":"","order":"336","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050101","parent_id":"900501","name":"DIU","description":"","order":"339","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050102","parent_id":"900501","name":"N\u00c3O ORAL","description":"","order":"340","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050103","parent_id":"900501","name":"P\u00cdLULA ANTICONCEPCIONAL","description":"","order":"341","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050104","parent_id":"900501","name":"P\u00cdLULA DO DIA SEGUINTE","description":"","order":"342","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050201","parent_id":"900502","name":"REM\u00c9DIOS CONTROLADOS","description":"","order":"344","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050301","parent_id":"900503","name":"CALMANTES","description":"","order":"346","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050302","parent_id":"900503","name":"FLORAIS","description":"","order":"347","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050303","parent_id":"900503","name":"\u00d3LEOS ESS\u00caNCIAS","description":"","order":"348","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050304","parent_id":"900503","name":"OUTROS FITOTER\u00c1PICOS","description":"","order":"349","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050305","parent_id":"900503","name":"XAROPES","description":"","order":"289","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050306","parent_id":"900503","name":"IMUNIDADE","description":"","order":"290","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050307","parent_id":"900503","name":"VITAMINAS","description":"","order":"291","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050401","parent_id":"900504","name":"COL\u00cdRIO","description":"","order":"351","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050402","parent_id":"900504","name":"DESCONGESTIONANTE OFT\u00c1LMICO","description":"","order":"352","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050403","parent_id":"900504","name":"HIGIENIZA\u00c7\u00c3O","description":"","order":"353","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050404","parent_id":"900504","name":"PROTETOR OFT\u00c1LMICO","description":"","order":"354","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050405","parent_id":"900504","name":"SOLU\u00c7\u00c3O PARA LENTES","description":"","order":"355","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050406","parent_id":"900504","name":"TRATAMENTO PARA OLHOS","description":"","order":"356","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050501","parent_id":"900505","name":"SISTEMA RESPIRAT\u00d3RIO","description":"","order":"358","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050502","parent_id":"900505","name":"ANTIPARASIT\u00c1RIOS","description":"","order":"359","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050503","parent_id":"900505","name":"SISTEMA MUSCULAR E ARTICULAR","description":"","order":"360","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050504","parent_id":"900505","name":"SISTEMA NERVOSO","description":"","order":"361","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050505","parent_id":"900505","name":"SISTEMA DIGESTIVO","description":"","order":"362","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050506","parent_id":"900505","name":"SISTEMA EXCRETOR","description":"","order":"363","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050507","parent_id":"900505","name":"SISTEMA END\u00d3CRINO","description":"","order":"364","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050508","parent_id":"900505","name":"SISTEMA IMUNOL\u00d3GICO","description":"","order":"365","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050509","parent_id":"900505","name":"SISTEMA REPRODUTOR","description":"","order":"366","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050510","parent_id":"900505","name":"DERMATOL\u00d3GICOS","description":"","order":"367","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050511","parent_id":"900505","name":"TRATAMENTO CAPILAR","description":"","order":"368","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050512","parent_id":"900505","name":"SISTEMA CARDIOVASCULAR","description":"","order":"369","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050513","parent_id":"900505","name":"MANUTEN\u00c7\u00c3O DO PESO","description":"","order":"370","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050514","parent_id":"900505","name":"ANTIBI\u00d3TICOS","description":"","order":"371","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050515","parent_id":"900505","name":"ANTIVIRAIS","description":"","order":"372","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050516","parent_id":"900505","name":"ANTIF\u00daNGICOS","description":"","order":"373","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050517","parent_id":"900505","name":"ANTI-INFLAMAT\u00d3RIO","description":"","order":"374","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050519","parent_id":"900505","name":"ANALG\u00c9SICOS","description":"","order":"376","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050520","parent_id":"900505","name":"OTOL\u00d3GICOS","description":"","order":"377","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050521","parent_id":"900505","name":"ANTIAL\u00c9RGICOS","description":"","order":"378","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050522","parent_id":"900505","name":"CONTROLE DE V\u00cdCIOS","description":"","order":"379","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050523","parent_id":"900505","name":"TERAPIA DO C\u00c2NCER","description":"","order":"380","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050524","parent_id":"900505","name":"OUTROS","description":"","order":"381","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050601","parent_id":"900506","name":"ASMA","description":"","order":"383","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050602","parent_id":"900506","name":"COAGULA\u00c7\u00c3O","description":"","order":"384","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050603","parent_id":"900506","name":"COLESTEROL","description":"","order":"385","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050604","parent_id":"900506","name":"DIABETES","description":"","order":"386","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050605","parent_id":"900506","name":"DIUR\u00c9TICO","description":"","order":"387","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050606","parent_id":"900506","name":"HIPERTENS\u00c3O","description":"","order":"388","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050607","parent_id":"900506","name":"G\u00c1STRICOS","description":"","order":"389","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050608","parent_id":"900506","name":"OUTROS DE USO CONT\u00cdNUO","description":"","order":"292","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050610","parent_id":"900506","name":"SISTEMA NERVOSO","description":"","order":"294","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050701","parent_id":"900507","name":"BELEZA","description":"","order":"391","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050702","parent_id":"900507","name":"REGULADORES DE PESO","description":"","order":"392","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050703","parent_id":"900507","name":"ESTIMULANTES","description":"","order":"393","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050704","parent_id":"900507","name":"FADIGA","description":"","order":"394","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050705","parent_id":"900507","name":"INFANTIL","description":"","order":"395","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050706","parent_id":"900507","name":"FEMININOS","description":"","order":"396","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050707","parent_id":"900507","name":"GESTANTES","description":"","order":"397","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050708","parent_id":"900507","name":"MASCULINO","description":"","order":"398","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050709","parent_id":"900507","name":"MULTIVITAMINAS","description":"","order":"399","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050710","parent_id":"900507","name":"\u00d4MEGA 3","description":"","order":"400","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050711","parent_id":"900507","name":"OUTROS","description":"","order":"401","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90050712","parent_id":"900507","name":"S\u00caNIOR","description":"","order":"402","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90050713","parent_id":"900507","name":"IMUNIDADE","description":"","order":"288","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060101","parent_id":"900601","name":"ACESS\u00d3RIOS CORRETIVOS PARA OS P\u00c9S","description":"","order":"405","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060102","parent_id":"900601","name":"COLETES ORTOP\u00c9DICOS","description":"","order":"406","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060103","parent_id":"900601","name":"COTOVELEIRA","description":"","order":"407","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060104","parent_id":"900601","name":"FITA KINESIO","description":"","order":"408","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060105","parent_id":"900601","name":"IMOBILIZADORES","description":"","order":"409","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90060106","parent_id":"900601","name":"JOELHEIRA","description":"","order":"410","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060107","parent_id":"900601","name":"MEIA DE COMPRESS\u00c3O","description":"","order":"411","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060108","parent_id":"900601","name":"MUNHEQUEIRA","description":"","order":"412","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060109","parent_id":"900601","name":"SAND\u00c1LIAS ORTOP\u00c9DICAS","description":"","order":"413","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90060110","parent_id":"900601","name":"TORNOZELEIRA","description":"","order":"414","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90060201","parent_id":"900602","name":"ACESS\u00d3RIOS PARA FISIOTERAPIA","description":"","order":"416","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90060202","parent_id":"900602","name":"OUTROS","description":"","order":"417","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070101","parent_id":"900701","name":"ACESS\u00d3RIOS HOSPITALARES","description":"","order":"420","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070102","parent_id":"900701","name":"ACESS\u00d3RIOS TERMOL\u00c1BEIS","description":"","order":"421","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070103","parent_id":"900701","name":"BOLSA T\u00c9RMICA","description":"","order":"422","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070104","parent_id":"900701","name":"COLETOR PARA EXAME","description":"","order":"423","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070105","parent_id":"900701","name":"COMPRESSAS FRIAS\/ QUENTES","description":"","order":"424","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070106","parent_id":"900701","name":"INALA\u00c7\u00c3O","description":"","order":"425","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070107","parent_id":"900701","name":"LUVAS","description":"","order":"426","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070108","parent_id":"900701","name":"M\u00c1SCARA","description":"","order":"427","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070109","parent_id":"900701","name":"MEDIDOR DE GLICOSE","description":"","order":"428","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070110","parent_id":"900701","name":"MEDIDOR PRESS\u00c3O ARTERIAL","description":"","order":"429","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070111","parent_id":"900701","name":"SERINGA\/AGULHA","description":"","order":"430","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070201","parent_id":"900702","name":"ALGOD\u00c3O","description":"","order":"432","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070202","parent_id":"900702","name":"ANTISS\u00c9PTICO","description":"","order":"433","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070203","parent_id":"900702","name":"CICATRIZANTE","description":"","order":"434","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070204","parent_id":"900702","name":"CONTUS\u00d5ES","description":"","order":"435","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070205","parent_id":"900702","name":"CURATIVOS","description":"","order":"436","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070206","parent_id":"900702","name":"DILATADOR NASAL","description":"","order":"437","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070207","parent_id":"900702","name":"HASTES FLEX\u00cdVEIS","description":"","order":"438","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070208","parent_id":"900702","name":"PORTA COMPRIMIDO","description":"","order":"439","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070209","parent_id":"900702","name":"PROTETOR AUDITIVO","description":"","order":"440","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"1","id":"90070210","parent_id":"900702","name":"SORO\/SOLU\u00c7\u00c3O","description":"","order":"441","active":"1","small_description":"","Images":[]}},{"Category":{"has_product":"","id":"90070211","parent_id":"900702","name":"TERM\u00d4METRO","description":"","order":"442","active":"1","small_description":"","Images":[]}}]';
        // $rs = json_decode($json, true);

        /** @var $repoDepto $repoDepto */
        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
        /** @var $repoGrupo $repoGrupo */
        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        /** @var $repoSubgrupo $repoSubgrupo */
        $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);

        foreach ($rs as $r) {

            $codigo = substr($r['Category']['id'], 0, 4);
            $depto = $repoDepto->findOneByCodigo($codigo);

            $grupoCodigo = null;
            $grupo = null;
            $subgrupoCodigo = null;
            $subgrupo = null;

            if (strlen($r['Category']['id']) > 4) {
                $grupoCodigo = substr($r['Category']['id'], 0, 6);
                $grupo = $repoGrupo->findOneByCodigo($grupoCodigo);

                if (strlen($r['Category']['id']) > 6) {
                    $subgrupoCodigo = $r['Category']['id'];
                    $subgrupo = $repoSubgrupo->findOneByCodigo($subgrupoCodigo);
                }
            }


            if (!$depto) {
                $depto = new Depto();
                $depto->codigo = $r['Category']['id'];
                $depto->nome = $r['Category']['name'];
                $depto->jsonData['ecommerce_id'] = $r['Category']['id'];
                $deptoEntityHandler->save($depto);
            }

            if ($grupoCodigo) {
                $grupo = $grupo ?? new Grupo();
                $grupo->depto = $depto;
                $grupo->codigo = $r['Category']['id'];
                $grupo->nome = $r['Category']['name'];
                $grupo->jsonData['ecommerce_id'] = $r['Category']['id'];
                $grupoEntityHandler->save($grupo);
            }

            if ($subgrupoCodigo) {
                $subgrupo = $subgrupo ?? new Subgrupo();
                $subgrupo->grupo = $grupo;
                $subgrupo->codigo = $r['Category']['id'];
                $subgrupo->nome = $r['Category']['name'];
                $subgrupo->jsonData['ecommerce_id'] = $r['Category']['id'];
                $subgrupoEntityHandler->save($subgrupo);
            }

        }
        return new Response("OK");
    }

}
