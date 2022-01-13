<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\RegistroConferenciaType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\RegistroConferenciaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegistroConferencia;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\RegistroConferenciaEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Carlos Eduardo Pauluk
 */
class RegistroConferenciaController extends FormListController
{

    private RegistroConferenciaBusiness $business;

    /**
     *
     * @Route("/fin/registroConferencia/gerarProximo/{id}/", name="registroConferencia_gerarProximo", requirements={"id"="\d+"})
     * @param RegistroConferencia $registroConferencia
     * @return RedirectResponse
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