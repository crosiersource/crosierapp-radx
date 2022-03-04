<?php

namespace App\Controller\Estoque;

use App\Form\Estoque\FornecedorType;
use CrosierSource\CrosierLibBaseBundle\Controller\FormListController;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\APIUtils\CrosierApiResponse;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\FilterData;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\FornecedorEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
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
class FornecedorController extends FormListController
{

    /**
     *
     * @Route("/api/est/fornecedor/findProxCodigo/", name="api_est_fornecedor_findProxCodigo")
     * @param Request $request
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findProxCodigo(Connection $conn): JsonResponse
    {
        try {
            $rsProxCodigo = $conn->fetchAssociative('SELECT max(codigo)+1 as prox FROM est_fornecedor WHERE codigo < 2147483647');
            return CrosierApiResponse::success($rsProxCodigo);
        } catch (\Exception $e) {
            return CrosierApiResponse::error();
        }
    }
    
    /**
     *
     * @Route("/api/est/fornecedor/findByStr", name="api_est_fornecedor_findByStr")
     * @param Request $request
     * @return JsonResponse
     *
     * @IsGranted("ROLE_ESTOQUE", statusCode=403)
     */
    public function findByStr(Request $request): JsonResponse
    {
        try {
            $str = $request->get('term');
            /** @var FornecedorRepository $repoFornecedor */
            $repoFornecedor = $this->getDoctrine()->getRepository(Fornecedor::class);

            $fornecedores = $repoFornecedor->findByFiltersSimpl([[['nome', 'nomeFantasia'], 'LIKE', $str]], ['nome' => 'ASC'], 0, 50);
            $select2js = Select2JsUtils::toSelect2DataFn($fornecedores, function ($e) {
                /** @var Fornecedor $e */
                return $e->nome . ' [Id.: ' . str_pad($e->getId(), 7, '0', STR_PAD_LEFT) . ']';
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

}
