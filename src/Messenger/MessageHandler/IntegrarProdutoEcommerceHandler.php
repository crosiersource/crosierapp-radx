<?php


namespace App\Messenger\MessageHandler;


use App\Business\ECommerce\IntegradorECommerceFactory;
use App\Messenger\Message\IntegrarProdutoEcommerceMessage;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class IntegrarProdutoEcommerceHandler
 * @package App\Messenger\MessageHandler
 * @author Carlos Eduardo Pauluk
 */
class IntegrarProdutoEcommerceHandler implements MessageHandlerInterface
{

    private SyslogBusiness $syslog;

    private EntityManagerInterface $doctrine;

    private IntegradorECommerceFactory $integradorBusinessFactory;


    public function __construct(SyslogBusiness $syslog, EntityManagerInterface $doctrine, IntegradorECommerceFactory $integradorBusinessFactory)
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->doctrine = $doctrine;
        $this->integradorBusinessFactory = $integradorBusinessFactory;
    }

    /**
     * Consumidor das mensagems IntegrarProdutoEcommerceMessage
     *
     * @param IntegrarProdutoEcommerceMessage $integrarProdutoEcommerce
     */
    public function __invoke(IntegrarProdutoEcommerceMessage $integrarProdutoEcommerce)
    {
        $this->syslog->info('Iniciando integração de produto ao ecommerce', 'id = ' . $integrarProdutoEcommerce->produtoId);
        $produto = $this->doctrine->getRepository(Produto::class)->find($integrarProdutoEcommerce->produtoId);
        $integrador = $this->integradorBusinessFactory->getIntegrador();
        $integrador->integraProduto($produto);
    }
}