<?php

namespace App\Controller\Estoque;

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

    /** @var DeptoEntityHandler */
    private $deptoEntityHandler;

    /** @var GrupoEntityHandler */
    private $grupoEntityHandler;

    /** @var SubgrupoEntityHandler */
    private $subgrupoEntityHandler;

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
            $parameters['deptoSelected'] = $grupo->getDepto();
        } else if ($request->get('deptoId')) {
            /** @var Depto $depto */
            $depto = $repoDepto->find($request->get('deptoId'));
            $parameters['deptoSelected'] = $depto;
            if ($depto->getGrupos()) {
                /** @var Grupo $grupo */
                $grupo = $depto->getGrupos()->first();
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
        $depto->setCodigo($deptoArr['codigo']);
        $depto->setNome($deptoArr['nome']);
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
        $grupo->setDepto($depto);
        $grupo->setCodigo($grupoArr['codigo']);
        $grupo->setNome($grupoArr['nome']);
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
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['deptoId' => $grupo->getDepto()->getId()]);
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
        $subgrupo->setGrupo($grupo);
        $subgrupo->setCodigo($subgrupoArr['codigo']);
        $subgrupo->setNome($subgrupoArr['nome']);
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
        return $this->redirectToRoute('est_deptoGrupoSubgrupo_form', ['grupoId' => $subgrupo->getGrupo()->getId()]);
    }


    /**
     *
     * @Route("/est/depto/importar/", name="est_depto_importar")
     * @return Response
     *
     * @throws ViewException
     */
    public function importar()
    {
        $str = '76|900|DEVERSOS                           |        |0|0|0|0|0|0|
75|308|ELETR/ARRANQUE/ALTERN.VW/FORD      |        |0|0|0|0|0|0|
74|208|ELETR/ARRANQUE/ALTERN.MBB/IVECO    |        |0|0|0|0|0|0|
73|108|ELETR/ARRANQUE/ALTERN.VOLVO        |        |0|0|0|0|0|0|
72|8|ELETR/ARRANQUE/ALTERN.SCANIA       |        |0|0|0|0|0|0|
71|310|LUBRIFICANTES FORD/VOLKSWAGEM/DAF  |84213100|0|0|0|0|0|0|
70|225|EMBREAGEM MBB                      |        |0|0|0|0|0|0|
69|357|PRODUCAO PROPRIA                   |        |0|0|0|0|0|0|
68|356|INSUMOS/PRODUCAO                   |        |0|0|0|0|0|0|
67|56|BATERIAS                           |        |0|0|0|0|0|0|
66|355|ACESSORIOS FORD/VOLKSWAGEM/DAF     |        |0|0|0|0|0|0|
65|100|BENS ATIVO IMOBILIZADO             |20000000|0|0|0|0|0|0|
64|235|DIFERENCIAL MB/IVECO               |        |0|0|0|0|0|0|
63|26|CAMBIO SC                          |        |0|0|0|0|0|0|
62|239|CARRETAS MB                        |        |0|0|0|0|0|0|
61|241|CARDAN MB/IVECO                    |        |0|0|0|0|0|0|
60|233|EIXO DT/AMORTECEDOR MB/IVECO       |        |0|0|0|0|0|0|
59|207|BBA INJ//CANO BICO MB              |        |0|0|0|0|0|0|
58|214|COLETOR ESC/ADM/INTER/MB/IVECO     |        |0|0|0|0|0|0|
57|213|COMPRESSOR MB/IVECO                |        |0|0|0|0|0|0|
56|277|MANGUEIRAS/BORRACHAS MB/IVECO      |        |0|0|0|0|0|0|
55|255|ACESSORIOS MB/IVECO                |        |0|0|0|0|0|0|
54|299|RETENTORES MB                      |        |0|0|0|0|0|0|
53|224|COXIM/SUPORTES MB                  |        |0|0|0|0|0|0|
52|10|FILTROS SC                         |        |0|0|0|0|0|0|
51|120|BBA DAGUA/CORREIAS VV              |        |0|0|0|0|0|0|
50|106|JUNTAS                             |        |0|0|0|0|0|0|
49|1|MOTOR SC                           |        |0|0|0|0|0|0|
48|298|ROLAMENTOS MB/IVECO                |        |0|0|0|0|0|0|
47|325|EMBREAGEM VOLKSWAGEM/FORD/DAF      |        |0|0|0|0|0|0|
46|42|EIXO TRAZ/DT/REPAROS               |        |0|0|0|0|0|0|
45|135|DIFERENCIAL VV                     |        |0|0|0|0|0|0|
44|139|CARRETAS VV                        |        |0|0|0|0|0|0|
43|177|MANGUEIRAS/BORRACHAS VV            |        |0|0|0|0|0|0|
42|118|BBA DE OLEO/FLEX.HIDRAULICO VV     |        |0|0|0|0|0|0|
41|199|RETENTORES VV                      |        |0|0|0|0|0|0|
40|107|BBA INJ//CANO BICO VV              |        |0|0|0|0|0|0|
39|155|ACESSORIOS VV                      |        |0|0|0|0|0|0|
38|198|ROLAMENTOS VV                      |        |0|0|0|0|0|0|
37|201|MOTOR MB/IVECO                     |        |0|0|0|0|0|0|
36|55|ACESSORIOS SC                      |        |0|0|0|0|0|0|
35|124|COXIM/SUPORTES VV                  |        |0|0|0|0|0|0|
34|113|COMPRESSOR VV                      |        |0|0|0|0|0|0|
33|206|JUNTAS MB                          |        |0|0|0|0|0|0|
32|195|LUBRIFICANTES VV                   |        |0|0|0|0|0|0|
31|141|CARDAN VV                          |        |0|0|0|0|0|0|
30|133|EIXO DT/AMORTECEDOR VV             |        |0|0|0|0|0|0|
29|154|CABOS VV                           |        |0|0|0|0|0|0|
28|126|CAMBIO VV                          |        |0|0|0|0|0|0|
27|125|EMBREAGEM VV                       |        |0|0|0|0|0|0|
26|242|EIXO TRAZ/DT/REPAROS MBB/IVECO     |        |0|0|0|0|0|0|
25|210|FILTROS MB/IVECO                   |        |0|0|0|0|0|0|
24|220|BBA DAGUA/CORREIAS MB/IVECO        |        |0|0|0|0|0|0|
23|114|COLETOR ESC/ADM/INTERC/VV          |        |0|0|0|0|0|0|
22|41|CARDAN SC                          |        |0|0|0|0|0|0|
21|99|RETENTORES SC                      |        |0|0|0|0|0|0|
20|98|ROLAMENTOS SC                      |        |0|0|0|0|0|0|
19|95|LUBRIFICANTES SC                   |        |0|0|0|0|0|0|
18|77|MANGUEIRAS/BORRACHAS SC            |        |0|0|0|0|0|0|
17|54|CABOS SC                           |        |0|0|0|0|0|0|
16|142|EIXO TRAZ/DT/REPAROS VV            |        |0|0|0|0|0|0|
15|39|CARRETAS SC                        |        |0|0|0|0|0|0|
14|35|DIFERENCIAL SC                     |        |0|0|0|0|0|0|
13|33|EIXO DT/AMORTECEDOR SC             |        |0|0|0|0|0|0|
12|226|CAMBIO MB                          |        |0|0|0|0|0|0|
11|25|EMBREAGEM SC                       |        |0|0|0|0|0|0|
10|24|COXIM/SUPORTES SC                  |        |0|0|0|0|0|0|
9|20|BBA DAGUA/CORREIAS SC              |        |0|0|0|0|0|0|
8|18|BBA DE OLEO/FLEX.HIDRAULICO SC     |        |0|0|0|0|0|0|
7|14|COLETOR ESC/ADM/INTERC/SC          |        |0|0|0|0|0|0|
6|13|COMPRESSOR SC                      |        |0|0|0|0|0|0|
5|110|FILTROS VV                         |        |0|0|0|0|0|0|
4|7|BBA INJ//CANO BICO SC              |        |0|0|0|0|0|0|
3|6|JUNTAS SC                          |        |0|0|0|0|0|0|
2|4|PARAFUSOS/BRACADEIRAS SC           |        |0|0|0|0|0|0|
1|101|MOTOR VV                           |        |0|0|0|0|0|0|';

        $linhas = explode("\n", $str);
        foreach ($linhas as $linha) {
            $campos = explode("|", $linha);
            $depto = new Depto();
            $depto->setCodigo(trim($campos[1]));
            $depto->setNome(trim($campos[2]));
            $this->entityHandler->save($depto);

            $grupo = new Grupo();
            $grupo->setDepto($depto);
            $grupo->setCodigoDepto($depto->getCodigo());
            $grupo->setNomeDepto($depto->getNome());
            $grupo->setCodigo(1);
            $grupo->setNome('GERAL');
            $this->grupoEntityHandler->save($grupo);

            $subgrupo = new Subgrupo();
            $subgrupo->setDepto($depto);
            $subgrupo->setCodigoDepto($depto->getCodigo());
            $subgrupo->setNomeDepto($depto->getNome());
            $subgrupo->setGrupo($grupo);
            $subgrupo->setCodigoGrupo($grupo->getCodigo());
            $subgrupo->setNomeGrupo($grupo->getNome());
            $subgrupo->setCodigo(1);
            $subgrupo->setNome('GERAL');
            $this->subgrupoEntityHandler->save($subgrupo);

        }

        return new Response('OK');

    }


}