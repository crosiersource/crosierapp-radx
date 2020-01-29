<?php

namespace App\EntityHandler\Fiscal;

use App\Business\Fiscal\NotaFiscalBusiness;
use App\Entity\Fiscal\DistDFe;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use Symfony\Component\Security\Core\Security;

/**
 * Class DistDFeEntityHandler
 * @package App\EntityHandler
 * @author Carlos Eduardo Pauluk
 */
class DistDFeEntityHandler extends EntityHandler
{

    /** @var Security */
    protected $security;

    /** @var NotaFiscalBusiness */
    private $notaFiscalBusiness;

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

    public function getEntityClass()
    {
        return DistDFe::class;
    }

}