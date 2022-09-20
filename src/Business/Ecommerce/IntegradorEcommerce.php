<?php

namespace App\Business\Ecommerce;


use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;

/**
 * @author Carlos Eduardo Pauluk
 */
interface IntegradorEcommerce
{

    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int;

    public function obterVendasPorData(\DateTime $dtVenda);

    public function obterCliente($idClienteEcommerce);

    public function reintegrarVendaParaCrosier(Venda $venda);

    public function integrarVendaParaEcommerce(Venda $venda);

    public function integraProduto(Produto $produto, ?bool $integrarImagens = true, ?bool $respeitarDelay = false);

}
