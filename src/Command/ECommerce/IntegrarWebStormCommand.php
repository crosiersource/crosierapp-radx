<?php

namespace App\Command\ECommerce;

use App\Business\ECommerce\IntegraWebStorm;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegrarWebStormCommand extends Command
{

    private IntegraWebStorm $integraWebStorm;

    /**
     * @required
     * @param IntegraWebStorm $integraWebStorm
     */
    public function setIntegraWebStorm(IntegraWebStorm $integraWebStorm): void
    {
        $this->integraWebStorm = $integraWebStorm;
    }

    protected function configure()
    {
        $this->setName('crosierappradx:integrarWebStorm');
        $this->addArgument('tipoIntegracao', InputArgument::REQUIRED, 'Tipo de Integração: "vendas"');
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
        $dtBase = DateTimeUtils::parseDateStr($input->getArgument('dtBase'));
        if (!$dtBase) {
            $dtBase = new \DateTime();
        }
        switch ($tipoIntegracao) {
            case 'vendas':
                try {
                    $output->writeln('Obtendo vendas');
                    $qtdeVendas = $this->integraWebStorm->obterVendas($dtBase);
                    $output->writeln('OK: ' . $qtdeVendas . ' venda(s) integrada(s)');
                } catch (ViewException $e) {
                    $output->writeln('Erro ao obterVendas');
                    $output->writeln($e->getMessage());
                    $output->writeln($e->getTraceAsString());
                }
                break;
            default:
                throw new \RuntimeException('tipoIntegracao desconhecido: ' . $tipoIntegracao);
        }
        return 1;
    }


}