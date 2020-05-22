<?php


namespace App\Business\ECommerce;


/**
 * Interface IntegradorBusiness
 * @package App\Business\ECommerce
 */
interface IntegradorBusiness
{

    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     */
    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int;

}