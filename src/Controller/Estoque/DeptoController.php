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
            $subgrupos = $conn->fetchAllAssociative('SELECT s.id as subgrupo_id, s.codigo as subgrupo_codigo, s.nome as subgrupo_nome, g.id as grupo_id, g.codigo as grupo_codigo, g.nome as grupo_nome, d.id as depto_id, d.codigo as depto_codigo, d.nome as depto_nome FROM est_subgrupo s, est_grupo g, est_depto d WHERE s.grupo_id = g.id AND g.depto_id = d.id');
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
    public function importar(DeptoEntityHandler $deptoEntityHandler, GrupoEntityHandler $grupoEntityHandler, SubgrupoEntityHandler $subgrupoEntityHandler): Response
    {
        $txt = file_get_contents('/home/carlos/Downloads/categorias+ecommerce.csv');

        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);

        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);

        $linhas = explode("\n", $txt);

        $deptoCodigo = 100;
        $grupoCodigo = 100;
        $subgrupoCodigo = 100;
        $deptoNome = '';
        $grupoNome = '';

        try {
            foreach ($linhas as $i => $linha) {
                $campos = explode(";", $linha);
                if ($campos[0] === '<<FIM>>') {
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
                if ($depto) {
                    $deptoCodigo = $depto->codigo;
                } else {
                    $depto = new Depto();
                    $deptoCodigo++;
                    $depto->codigo = $deptoCodigo;
                    $depto->nome = mb_strtoupper($deptoNome);
                    $depto = $deptoEntityHandler->save($depto);
                }

                /** @var Grupo $grupo */
                $grupo = $repoGrupo->findOneByFiltersSimpl([['nome', 'EQ', $grupoNome], ['depto', 'EQ', $depto]]);
                if ($grupo) {
                    $grupoCodigo = $grupo->codigo;
                } else {
                    $grupo = new grupo();
                    $grupo->depto = $depto;
                    $grupoCodigo++;
                    $grupo->codigo = str_pad($grupoCodigo, 2, '0', STR_PAD_LEFT);
                    $grupo->nome = mb_strtoupper($grupoNome);
                    $grupo = $grupoEntityHandler->save($grupo);
                }


                /** @var Subgrupo $subgrupo */
                $subgrupo = $repoSubgrupo->findOneByFiltersSimpl([['nome', 'EQ', $subgrupoNome], ['grupo', 'EQ', $grupo]]);
                if ($subgrupo) {
                    $subgrupoCodigo = $subgrupo->codigo;
                } else {
                    $subgrupo = new Subgrupo();
                    $subgrupo->grupo = $grupo;
                    $subgrupoCodigo++;
                    $subgrupo->codigo = str_pad($subgrupoCodigo, 2, '0', STR_PAD_LEFT);
                    $subgrupo->nome = mb_strtoupper($subgrupoNome);
                    $subgrupoEntityHandler->save($subgrupo);
                }


            }
        } catch (\Throwable $e) {
            $bla = 1;
        }


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
                $produtoEntityHandler->save($produto);
            }
            $conn->commit();
            return CrosierApiResponse::success();
        } catch (\Throwable $e) {
            return CrosierApiResponse::error();
        }

    }

}
