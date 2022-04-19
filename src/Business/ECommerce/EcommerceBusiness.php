<?php

namespace App\Business\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\IntegradorEcommerce;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Doctrine\ORM\EntityManagerInterface;

class EcommerceBusiness
{


    private EntityManagerInterface $em;

    private SyslogBusiness $syslog;

    /**
     * @param EntityManagerInterface $em
     * @param SyslogBusiness $syslog
     */
    public function __construct(EntityManagerInterface $em, SyslogBusiness $syslog)
    {
        $this->em = $em;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class)->setEcho(true);
    }


    public function reintegrarDesatualizados(IntegradorEcommerce $integradorEcommerce)
    {
        $rsProdutosDesatualizados = $this->em->getConnection()->fetchAllAssociative(
            'SELECT id FROM est_produto WHERE ecommerce AND json_data->>"$.ecommerce_desatualizado" = \'S\'');
        $this->syslog->info('EcommerceBusiness.reintegrarDesatualizados - ' . count($rsProdutosDesatualizados) . ' produto(s) desatualizado(s)');

        $repoProduto = $this->em->getRepository(Produto::class);
        foreach ($rsProdutosDesatualizados as $rProd) {
            $produto = $repoProduto->find($rProd['id']);
            $integradorEcommerce->integraProduto($produto, true, true);
        }
        $this->syslog->info('EcommerceBusiness.reintegrarDesatualizados - fim');
    }

}