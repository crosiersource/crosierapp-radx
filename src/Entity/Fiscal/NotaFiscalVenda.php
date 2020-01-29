<?php

namespace App\Entity\Fiscal;

use App\Entity\Vendas\Venda;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\NotaFiscalVendaRepository")
 * @ORM\Table(name="fis_nf_venda")
 */
class NotaFiscalVenda implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fiscal\NotaFiscal")
     * @ORM\JoinColumn(name="nota_fiscal_id", nullable=true)
     *
     * @var $notaFiscal null|NotaFiscal
     */
    private $notaFiscal;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\vendas\Venda")
     * @ORM\JoinColumn(name="venda_id", nullable=false)
     *
     * @var null|Venda
     */
    private $venda;

    /**
     * @return NotaFiscal|null
     */
    public function getNotaFiscal(): ?NotaFiscal
    {
        return $this->notaFiscal;
    }

    /**
     * @param NotaFiscal|null $notaFiscal
     * @return NotaFiscalVenda
     */
    public function setNotaFiscal(?NotaFiscal $notaFiscal): NotaFiscalVenda
    {
        $this->notaFiscal = $notaFiscal;
        return $this;
    }

    /**
     * @return Venda|null
     */
    public function getVenda(): ?Venda
    {
        return $this->venda;
    }

    /**
     * @param Venda|null $venda
     * @return NotaFiscalVenda
     */
    public function setVenda(?Venda $venda): NotaFiscalVenda
    {
        $this->venda = $venda;
        return $this;
    }


}