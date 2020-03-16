<?php

namespace App\EntityHandler\Fiscal;

use App\Business\Fiscal\NotaFiscalBusiness;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalItem;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use Symfony\Component\Security\Core\Security;

/**
 * Class NotaFiscalEntityHandler
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalEntityHandler extends EntityHandler
{

    /** @var Security */
    protected $security;

    private NotaFiscalBusiness $notaFiscalBusiness;

    private NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler;

    /**
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

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
     * @param NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler
     */
    public function setNotaFiscalItemEntityHandler(NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler): void
    {
        $this->notaFiscalItemEntityHandler = $notaFiscalItemEntityHandler;
    }

    public function getEntityClass()
    {
        return NotaFiscal::class;
    }

    /**
     * @param $notaFiscal
     * @return mixed|void
     */
    public function beforeSave(/** @var NotaFiscal $notaFiscal */ $notaFiscal)
    {
        if ($notaFiscal->getItens() && $notaFiscal->getItens()->count() > 0) {
            $this->notaFiscalBusiness->calcularTotais($notaFiscal);
        }

        $i = 1;
        foreach ($notaFiscal->getItens() as $item) {
            $item->setOrdem($i++);
        }

        $notaFiscal->setDocumentoEmitente(preg_replace("/[\D]/", '', $notaFiscal->getDocumentoEmitente()));
        $notaFiscal->setDocumentoDestinatario(preg_replace("/[\D]/", '', $notaFiscal->getDocumentoDestinatario()));
        $notaFiscal->setTranspDocumento(preg_replace("/[\D]/", '', $notaFiscal->getTranspDocumento()));

        if ($notaFiscal->getChaveAcesso() === '') {
            $notaFiscal->setChaveAcesso(null);
        }
    }

    /**
     * @param $notaFiscal
     * @throws \Exception
     */
    public function beforeClone($notaFiscal)
    {
        /** @var NotaFiscal $notaFiscal */
        $notaFiscal->setUuid(null);
        $notaFiscal->setNumero(null);
        $notaFiscal->setSerie(null);
        $notaFiscal->setRandFaturam(null);
        $notaFiscal->setChaveAcesso(null);
        $notaFiscal->setDtEmissao(new \DateTime());
        $notaFiscal->setDtSaiEnt(null);
        $notaFiscal->setCStat(null);
        $notaFiscal->setCStatLote(null);
        $notaFiscal->setXMotivo(null);
        $notaFiscal->setXMotivoLote(null);
        $notaFiscal->setCnf(null);
        $notaFiscal->setMotivoCancelamento(null);
        $notaFiscal->setProtocoloAutorizacao(null);
        $notaFiscal->setXmlNota(null);

        $notaFiscal->setDocumentoEmitente(null);
        $notaFiscal->setXNomeEmitente(null);
        $notaFiscal->setInscricaoEstadualEmitente(null);
        $notaFiscal->setLogradouroEmitente(null);
        $notaFiscal->setNumeroEmitente(null);
        $notaFiscal->setBairroEmitente(null);
        $notaFiscal->setCepEmitente(null);
        $notaFiscal->setCidadeEmitente(null);
        $notaFiscal->setEstadoEmitente(null);
        $notaFiscal->setFoneEmitente(null);


        if ($notaFiscal->getItens() && $notaFiscal->getItens()->count() > 0) {
            $oldItens = clone $notaFiscal->getItens();
            $notaFiscal->getItens()->clear();
            foreach ($oldItens as $oldItem) {
                /** @var NotaFiscalItem $newItem */
                $newItem = clone $oldItem;
                $newItem->setId(null);
                $newItem->setInserted(new \DateTime());
                $newItem->setUserInsertedId($this->security->getUser()->getId());
                $newItem->setNotaFiscal($notaFiscal);
                $notaFiscal->getItens()->add($newItem);
            }
        }

        if ($notaFiscal->getHistoricos()) {
            $notaFiscal->getHistoricos()->clear();
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function deleteAllItens(NotaFiscal $notaFiscal)
    {
        foreach ($notaFiscal->getItens() as $item) {
            $item->setNotaFiscal(null);
            $this->notaFiscalItemEntityHandler->delete($item);
        }
        $notaFiscal->getItens()->clear();
        /** @var NotaFiscal $notaFiscal */
        $notaFiscal = $this->save($notaFiscal);
        return $notaFiscal;
    }

}