<?php

namespace App\Business\ECommerce;


use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use Doctrine\DBAL\ConnectionException;

/**
 * @author Carlos Eduardo Pauluk
 */
interface IntegradorECommerce
{

    /**
     * Percorre a árvore de Deptos/Grupos/Subgrupos e realiza a integração para o WebStorm.
     * @throws ViewException
     */
    public function integrarDeptosGruposSubgrupos();

    /**
     * @param Depto $depto
     * @return int
     * @throws ViewException
     */
    public function integraDepto(Depto $depto): int;

    /**
     * @param Grupo $grupo
     * @return int
     * @throws ViewException
     */
    public function integraGrupo(Grupo $grupo): int;

    /**
     * @param Subgrupo $subgrupo
     * @return int
     * @throws ViewException
     */
    public function integraSubgrupo(Subgrupo $subgrupo): int;

    /**
     * Integra um Depto, Grupo ou Subgrupo.
     *
     * @param string $descricao
     * @param int $nivel
     * @param int|null $idNivelPai1
     * @param int|null $idNivelPai2
     * @param int|null $ecommerce_id
     * @return int
     */
    public function integraDeptoGrupoSubgrupo(string $descricao, int $nivel, ?int $idNivelPai1 = null, ?int $idNivelPai2 = null, ?int $ecommerce_id = null);

    /**
     * @param Produto $produto
     * @param bool $integrarImagens
     * @return void
     * @throws ViewException
     */
    public function integraProduto(Produto $produto, ?bool $integrarImagens = true): void;

    /**
     * @return int
     * @throws ViewException
     */
    public function atualizaEstoqueEPrecos(): int;

    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     * @throws ViewException
     * @throws ConnectionException
     */
    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int;

    /**
     * @param \DateTime $dtVenda
     * @return \SimpleXMLElement|void|null
     */
    public function obterVendasPorData(\DateTime $dtVenda);

    /**
     * @param int $idClienteECommerce
     * @return \SimpleXMLElement|null
     */
    public function obterCliente(int $idClienteECommerce);

    /**
     * @param Venda $venda
     */
    public function reintegrarVendaParaCrosier(Venda $venda);

    /**
     * @param Venda $venda
     * @return \SimpleXMLElement|null
     */
    public function integrarVendaParaECommerce(Venda $venda);

    /**
     * Envia produtos para a fila (queue) que executará as integrações com o webstorm.
     *
     * @param int|null $limit
     * @return int
     */
    public function reenviarProdutosParaIntegracao(?int $limit = null): int;
}