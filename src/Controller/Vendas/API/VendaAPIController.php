<?php

namespace App\Controller\Vendas\API;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseAPIEntityIdController;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Vendas\VendaBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class VendaAPIController.
 *
 * @package App\Controller\Base\API
 * @author Carlos Eduardo Pauluk
 */
class VendaAPIController extends BaseAPIEntityIdController
{

    /** @var VendaEntityHandler */
    protected $entityHandler;

    private VendaBusiness $vendaBusiness;

    /**
     * @required
     * @param VendaEntityHandler $entityHandler
     */
    public function setEntityHandler(VendaEntityHandler $entityHandler): void
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * @required
     * @param VendaBusiness $vendaBusiness
     */
    public function setVendaBusiness(VendaBusiness $vendaBusiness): void
    {
        $this->vendaBusiness = $vendaBusiness;
    }


    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Venda::class;
    }

    /**
     *
     * @Route("/api/venda/findById/{id}", name="api_venda_findById", requirements={"id"="\d+"})
     * @param int $id
     * @return JsonResponse
     */
    public function findById(int $id): JsonResponse
    {
        return $this->doFindById($id);
    }

    /**
     * A ser sobreescrito.
     * Chamado após o retorno do resultado pelo findById.
     * @param $venda
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleFindById($venda): void
    {
        $this->vendaBusiness->recalcularTotais($venda);
    }


    /**
     *
     * @Route("/api/venda/findByFilters/", name="api_venda_findByFilters")
     * @param Request $request
     * @return JsonResponse
     */
    public function findByFilters(Request $request): JsonResponse
    {
        return $this->doFindByFilters($request);
    }

    /**
     * @param array $results
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleFindByFilters(array $results): void
    {
        foreach ($results as $venda) {
            $this->vendaBusiness->recalcularTotais($venda);
        }
    }


    /**
     *
     * @Route("/api/venda/getNew", name="api_venda_getNew")
     * @return JsonResponse
     */
    public function getNew(): JsonResponse
    {
        $venda = new Venda();
        $venda->addItem(new VendaItem());
        return new JsonResponse(['entity' => EntityIdUtils::serialize($venda)]);
    }


    /**
     *
     * @Route("/api/venda/save", name="api_venda_save")
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        return $this->doSave($request);
    }


}
