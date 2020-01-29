<?php

namespace App\EntityHandler\Fiscal;

use App\Entity\Fiscal\NotaFiscalCartaCorrecao;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class NotaFiscalCartaCorrecaoEntityHandler
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalCartaCorrecaoEntityHandler extends EntityHandler
{

    public function getEntityClass()
    {
        return NotaFiscalCartaCorrecao::class;
    }


    /**
     * @param $cartaCorrecao
     * @return mixed|void
     * @throws ViewException
     */
    public function beforeSave($cartaCorrecao)
    {
        /** @var NotaFiscalCartaCorrecao $cartaCorrecao */

        if (!$cartaCorrecao->getCartaCorrecao()) {
            throw new ViewException('É necessário informar a mensagem');
        }
        if (!$cartaCorrecao->getDtCartaCorrecao()) {
            throw new ViewException('É necessário informar a data/hora');
        }

        if (!$cartaCorrecao->getSeq()) {
            $cartaCorrecao->setSeq(1);
            /** @var ArrayCollection $cartasCorrecao */
            $cartasCorrecao = $cartaCorrecao->getNotaFiscal()->getCartasCorrecao();
            if ($cartasCorrecao && $cartasCorrecao->count() > 0) {
                $a = $cartasCorrecao->toArray();
                uasort($a, function (NotaFiscalCartaCorrecao $cartaCorrecao1, NotaFiscalCartaCorrecao $cartaCorrecao2) {
                    return strcasecmp($cartaCorrecao2->getSeq(), $cartaCorrecao1->getSeq());
                });
                $ultSeq = $a[0]->getSeq();
                $cartaCorrecao->setSeq($ultSeq + 1);
            }

        }

        // incrementar o seq
        // verificar se foi preenchido a carta_correcao

    }


}