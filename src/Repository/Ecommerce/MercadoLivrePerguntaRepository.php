<?php

namespace App\Repository\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use App\Entity\Ecommerce\MercadoLivrePergunta;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivrePerguntaRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return MercadoLivrePergunta::class;
    }
}
