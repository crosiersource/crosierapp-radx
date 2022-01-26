<?php

namespace App\Controller\Financeiro;


use App\Form\Financeiro\GrupoItemType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\GrupoBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\GrupoItemRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\MovimentacaoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * CRUD Controller para GrupoItem.
 *
 * @package App\Controller\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoItemController extends FormListController
{
    
    /**
     *
     * @Route("/fin/grupoItem/gerarNovo/{pai}", name="grupoItem_gerarNovo", requirements={"pai"="\d+"})
     * @param Request $request
     * @param Grupo $pai
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @IsGranted("ROLE_FINAN", statusCode=403)
     */
    public function gerarNovo(Request $request, Grupo $pai, GrupoEntityHandler $grupoEntityHandler): JsonResponse
    {
        try {
            $prox = $request->get('prox');
            $grupoEntityHandler->gerarNovo($pai, $prox);
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->addFlash('error', $msg);
        }
    }

    
}