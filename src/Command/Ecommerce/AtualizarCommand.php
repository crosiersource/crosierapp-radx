<?php

namespace App\Command\Ecommerce;

use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\MercadoLivreBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Ecommerce\TrayBusiness;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * php bin/console conecta:atualizar [TRAY,MERCADOLIVRE]
 * 
 * @author Carlos Eduardo Pauluk
 */
class AtualizarCommand extends Command implements ServiceSubscriberInterface
{

    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
        parent::__construct();
    }

    public static function getSubscribedServices(): array
    {
        return [
            "TRAY" => TrayBusiness::class,
            "MERCADOLIVRE" => MercadoLivreBusiness::class,
        ];
    }


    protected function configure()
    {
        $this->setName('conecta:atualizar');
        $this->addArgument('tipo', InputArgument::REQUIRED, 'Tipo [' . implode(',', array_keys(AtualizarCommand::getSubscribedServices())) . ']');
    }


    /**
     * Cria, baseado no comando do usuário, a instância de relatório apropriada para o trabalho a ser desenvolvido e a executa.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @resultado int|null|void
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tipo = $input->getArgument('tipo');
        if (AtualizarCommand::getSubscribedServices()[$tipo] ?? null && $this->locator->has($tipo)) {
            $business = $this->locator->get($tipo);
            $business->atualizar();
        } else {
            throw new \Exception($tipo . " não é suportado.");
        }
        return 1;
    }
}
