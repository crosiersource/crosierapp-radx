<?php


namespace App\Messenger\Ecommerce\MessageHandler;


use App\Business\Ecommerce\IntegradorEcommerceFactory;
use App\Messenger\Ecommerce\Message\IntegrarProdutoEcommerceMessage;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegrarProdutoEcommerceHandler implements MessageHandlerInterface
{

    private SyslogBusiness $syslog;

    private EntityManagerInterface $doctrine;

    private IntegradorEcommerceFactory $integradorBusinessFactory;


    public function __construct(SyslogBusiness             $syslog,
                                EntityManagerInterface     $doctrine,
                                IntegradorEcommerceFactory $integradorEcommerceFactory)
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->doctrine = $doctrine;
        $this->integradorBusinessFactory = $integradorEcommerceFactory;
    }

    /**
     *
     * @param IntegrarProdutoEcommerceMessage $message
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function __invoke(IntegrarProdutoEcommerceMessage $message)
    {
        $this->syslog->info('queue: consumindo IntegrarProdutoEcommerceMessage (produto.id = ' . $message->produtoId . ')');
        $produto = $this->doctrine->getRepository(Produto::class)->find($message->produtoId);
        $integrador = $this->integradorBusinessFactory->getIntegrador();
        $integrador->integraProduto($produto, $message->integrarImagens, true);
    }
}