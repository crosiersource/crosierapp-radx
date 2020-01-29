<?php

namespace App\Controller\Financeiro\API;

use App\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseAPIEntityIdController;
use CrosierSource\CrosierLibBaseBundle\Utils\EntityIdUtils\EntityIdUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @package App\Controller\Base\API
 * @author Carlos Eduardo Pauluk
 */
class CarteiraAPIController extends BaseAPIEntityIdController
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Carteira::class;
    }

    /**
     *
     * @Route("/api/carteira/findById/{id}", name="api_carteira_findById", requirements={"id"="\d+"})
     * @param int $id
     * @return JsonResponse
     */
    public function findById(int $id): JsonResponse
    {
        return $this->doFindById($id);
    }

    /**
     *
     * @Route("/api/carteira/findByFilters/", name="api_carteira_findByFilters")
     * @param Request $request
     * @return JsonResponse
     */
    public function findByFilters(Request $request): JsonResponse
    {
        return $this->doFindByFilters($request);
    }

    /**
     *
     * @Route("/api/carteira/getNew", name="api_carteira_getNew")
     * @return JsonResponse
     */
    public function getNew(): JsonResponse
    {
        $pessoa = new Carteira();
        return new JsonResponse(['entity' => EntityIdUtils::serialize($pessoa)]);
    }


    /**
     *
     * @Route("/api/carteira/save", name="api_carteira_save")
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        return $this->doSave($request);
    }


}
