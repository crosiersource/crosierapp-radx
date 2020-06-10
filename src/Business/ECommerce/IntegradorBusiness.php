<?php


namespace App\Business\ECommerce;


use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;

/**
 * @package App\Business\ECommerce
 * @author Carlos Eduardo Pauluk
 */
interface IntegradorBusiness
{

    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     */
    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int;

    /**
     * @param Produto $produto
     * @param bool|null $integrarImagens
     */
    public function integraProduto(Produto $produto, ?bool $integrarImagens = true): void;

}