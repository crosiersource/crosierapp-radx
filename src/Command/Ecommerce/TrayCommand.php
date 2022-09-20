<?php

namespace App\Command\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use App\Business\Ecommerce\IntegradorTray;
use App\Business\Ecommerce\IntegradorWebStorm;
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
        $this->addArgument('tipoIntegracao', InputArgument::REQUIRED, 'Tipo de Integração: "renewAccessToken"');
        $this->addArgument('dtBase', InputArgument::OPTIONAL, 'Data Base');
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tipoIntegracao = $input->getArgument('tipoIntegracao');

        switch ($tipoIntegracao) {
            case 'renewAccessToken':
                $output->writeln('renewAccessToken');
                $this->integradorTray->renewAccessToken();
                break;
            default:
                throw new \RuntimeException('tipoIntegracao desconhecido: ' . $tipoIntegracao);
        }
        return 0;
    }

}