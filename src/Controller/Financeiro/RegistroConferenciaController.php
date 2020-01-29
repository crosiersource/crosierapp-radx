<?php

namespace App\Controller\Financeiro;


use App\Business\Financeiro\RegistroConferenciaBusiness;
use App\Entity\Financeiro\RegistroConferencia;
use App\EntityHandler\Financeiro\RegistroConferenciaEntityHandler;
use App\Form\Financeiro\RegistroConferenciaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegistroConferenciaController
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegistroConferenciaController extends FormListController
{

    /** @var RegistroConferenciaBusiness */
    private $business;

    /**
     * @required
     * @param RegistroConferenciaBusiness $business
     */
    public function setBusiness(RegistroConferenciaBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     * @required
     * @param RegistroConferenciaEntityHandler $entityHandler
     */
    public function setEntityHandler(RegistroConferenciaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    public function getFilterDatas(array $params): array
    {
        return [
            new FilterData(['descricao'], 'LIKE', 'descricao', $params)
        ];
    }

    /**
     *
     * @Route("/registroConferencia/form/{id}", name="registroConferencia_form", defaults={"id"=null}, requirements={"id"="\d+"})
     * @param Request $request
     * @param RegistroConferencia|null $registroConferencia
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function form(Request $request, RegistroConferencia $registroConferencia = null)
    {
        $params = [
            'typeClass' => RegistroConferenciaType::class,
            'formView' => '@CrosierLibBase/form.html.twig',
            'formRoute' => 'registroConferencia_form',
            'formPageTitle' => 'Registro para Conferência'
        ];
        return $this->doForm($request, $registroConferencia, $params);
    }

    /**
     *
     * @Route("/registroConferencia/list/", name="registroConferencia_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function list(Request $request): Response
    {
        $params = [
            'formRoute' => 'registroConferencia_form',
            'listView' => 'registroConferenciaList.html.twig',
            'listRoute' => 'registroConferencia_list',
            'listRouteAjax' => 'registroConferencia_datatablesJsList',
            'listPageTitle' => 'Registros para Conferências',
            'listId' => 'registroConferenciaList'
        ];
        return $this->doList($request, $params);
    }

    /**
     *
     * @Route("/registroConferencia/datatablesJsList/", name="registroConferencia_datatablesJsList")
     * @param Request $request
     * @return Response
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function datatablesJsList(Request $request): Response
    {
        return $this->doDatatablesJsList($request);
    }

    /**
     *
     * @Route("/registroConferencia/delete/{id}/", name="registroConferencia_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param RegistroConferencia $registroConferencia
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function delete(Request $request, RegistroConferencia $registroConferencia): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->doDelete($request, $registroConferencia);
    }

    /**
     *
     * @Route("/registroConferencia/gerarProximo/{id}/", name="registroConferencia_gerarProximo", requirements={"id"="\d+"})
     * @param RegistroConferencia $registroConferencia
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_FINAN_ADMIN", statusCode=403)
     */
    public function gerarProximo(RegistroConferencia $registroConferencia): RedirectResponse
    {
        try {
            $this->business->gerarProximo($registroConferencia);
            $this->addFlash('info', 'Registro gerado com sucesso');
        } catch (ViewException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao processar requisição.');
        }
        return $this->redirectToRoute('registroConferencia_list');
    }


}