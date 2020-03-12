<?php

namespace App\Controller\Estoque;

use App\Business\ECommerce\IntegraWebStorm;
use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Subgrupo;
use App\EntityHandler\Estoque\DeptoEntityHandler;
use App\EntityHandler\Estoque\GrupoEntityHandler;
use App\EntityHandler\Estoque\SubgrupoEntityHandler;
use App\Repository\Estoque\DeptoRepository;
use App\Repository\Estoque\GrupoRepository;
use App\Repository\Estoque\SubgrupoRepository;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @throws \Exception
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function form(Request $request)
    {
        /** @var DeptoRepository $repoDepto */
        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
        $deptos = $repoDepto->findAll(['codigo' => 'ASC']);
        $parameters = [];
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
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['deptoId' => $depto->getId()]);
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
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $grupo->getId()]);
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
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $grupo->getId()]);
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
     *
     * @Route("/est/deptoGrupoSubgrupo/integraDepto/{depto}", name="est_deptoGrupoSubgrupo_integraDepto", requirements={"depto"="\d+"})
     * @param IntegraWebStorm $integraWebStorm
     * @param Depto $depto
     * @return RedirectResponse
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integraDepto(IntegraWebStorm $integraWebStorm, Depto $depto)
    {
        $integraWebStorm->integraDepto($depto);
        $this->addFlash('success', 'Depto integrado com sucesso');
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['deptoId' => $depto->getId()]);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/integraGrupo/{grupo}", name="est_deptoGrupoSubgrupo_integraGrupo", requirements={"grupo"="\d+"})
     * @param IntegraWebStorm $integraWebStorm
     * @param Grupo $grupo
     * @return RedirectResponse
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integraGrupo(IntegraWebStorm $integraWebStorm, Grupo $grupo)
    {
        $integraWebStorm->integraGrupo($grupo);
        $this->addFlash('success', 'Grupo integrado com sucesso');
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $grupo->getId()]);
    }

    /**
     *
     * @Route("/est/deptoGrupoSubgrupo/integraSubgrupo/{subgrupo}", name="est_deptoGrupoSubgrupo_integraSubgrupo", requirements={"subgrupo"="\d+"})
     * @param IntegraWebStorm $integraWebStorm
     * @param Subgrupo $subgrupo
     * @return RedirectResponse
     * @throws ViewException
     * @IsGranted("ROLE_ESTOQUE_ADMIN", statusCode=403)
     */
    public function integraSubgrupo(IntegraWebStorm $integraWebStorm, Subgrupo $subgrupo)
    {
        $integraWebStorm->integraSubgrupo($subgrupo);
        $this->addFlash('success', 'Subgrupo integrado com sucesso');
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['subgrupoId' => $subgrupo->getId()]);
    }


}