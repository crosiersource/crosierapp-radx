<?php

namespace App\Command\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorTray;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorWebStorm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class TrayCommand extends Command
{

    private IntegradorTray $integradorTray;

    /**
     * @required
     * @param IntegradorTray $integradorTray
     */
    public function setIntegradorTray(IntegradorTray $integradorTray): void
    {
        $this->integradorTray = $integradorTray;
    }


    protected function configure()
    {
        $this->setName('crosierappradx:tray');
        $this->addArgument('tipoIntegracao', InputArgument::REQUIRED, 'Tipo de Integração: "renewAllAccessTokens"');
        $this->addArgument('dtBase', InputArgument::OPTIONAL, 'Data Base');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tipoIntegracao = $input->getArgument('tipoIntegracao');

        switch ($tipoIntegracao) {
            case 'renewAllAccessTokens':
                $output->writeln('renewAllAccessTokens');
                $this->integradorTray->renewAllAccessTokens();
                break;
            default:
                throw new \RuntimeException('tipoIntegracao desconhecido: ' . $tipoIntegracao);
        }
        return 0;
    }

}