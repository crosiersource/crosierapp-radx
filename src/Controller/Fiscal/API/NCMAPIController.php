<?php

namespace App\Controller\Fiscal\API;

use App\Entity\Fiscal\NCM;
use CrosierSource\CrosierLibBaseBundle\Controller\BaseAPIEntityIdController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PropAPIController.
 *
 * @package App\Controller\Base\API
 * @author Carlos Eduardo Pauluk
 */
class NCMAPIController extends BaseAPIEntityIdController
{

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return NCM::class;
    }

    /**
     *
     * @Route("/api/ncm/findById/{id}", name="api_bse_municipio_findById", requirements={"id"="\d+"})
     * @param int $id
     * @return JsonResponse
     */
    public function findById(int $id): JsonResponse
    {
        return $this->doFindById($id);
    }

    /**
     *
     * @Route("/api/ncm/findByFilters/", name="api_bse_municipio_findByFilters")
     * @param Request $request
     * @return JsonResponse
     */
    public function findByFilters(Request $request): JsonResponse
    {
        return $this->doFindByFilters($request);
    }


}
