<?php

namespace App\EntityHandler\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use App\Entity\Ecommerce\MercadoLivrePergunta;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivrePerguntaEntityHandler extends EntityHandler
{


    /**
     * @param MercadoLivrePergunta $mercadoLivrePergunta
     */
    public function beforeSave($mercadoLivrePergunta)
    {
        if (!$mercadoLivrePergunta->UUID) {
            $mercadoLivrePergunta->UUID = StringUtils::guidv4();
        }
    }

    public function getEntityClass(): string
    {
        return MercadoLivrePergunta::class;
    }

}
