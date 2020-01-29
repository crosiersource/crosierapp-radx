<?php

namespace App\EntityHandler\Fiscal;

use App\Business\Fiscal\NotaFiscalBusiness;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalItem;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;

/**
 * Class NotaFiscalItemEntityHandler
 *
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalItemEntityHandler extends EntityHandler
{

    /** @var NotaFiscalBusiness */
    private $notaFiscalBusiness;

    /** @var NotaFiscalEntityHandler */
    private $notaFiscalEntityHandler;

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }

    /**
     * @required
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $notaFiscalEntityHandler): void
    {
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
    }


    public function beforeSave($nfItem)
    {
        /** @var NotaFiscalItem $nfItem */
        if (!$nfItem->getOrdem()) {
            $ultimaOrdem = 0;
            foreach ($nfItem->getNotaFiscal()->getItens() as $item) {
                if ($item->getOrdem() > $ultimaOrdem) {
                    $ultimaOrdem = $item->getOrdem();
                }
            }
            $nfItem->setOrdem($ultimaOrdem + 1);
        }
        if (!$nfItem->getCsosn()) {
            $nfItem->setCsosn(103);
        }
        $nfItem->calculaTotais();
    }

    public function afterSave(/** @var NotaFiscalItem $nfItem */ $nfItem)
    {
        $notaFiscal = $this->getDoctrine()->getRepository(NotaFiscal::class)->findOneBy(['id' => $nfItem->getNotaFiscal()->getId()]);
        $this->notaFiscalBusiness->calcularTotais($notaFiscal);
        $this->notaFiscalEntityHandler->save($notaFiscal);
    }

    public function afterDelete(/** @var NotaFiscalItem $nfItem */ $nfItem)
    {
        if ($nfItem->getNotaFiscal()) {
            $notaFiscal = $this->getDoctrine()->getRepository(NotaFiscal::class)->findOneBy(['id' => $nfItem->getNotaFiscal()->getId()]);
            $this->notaFiscalBusiness->calcularTotais($notaFiscal);
            $this->notaFiscalEntityHandler->save($notaFiscal);
        }
    }


    public function getEntityClass()
    {
        return NotaFiscalItem::class;
    }
}