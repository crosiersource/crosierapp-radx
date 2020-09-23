<?php

namespace App\Command\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\ECommerce\IntegradorWebStorm;
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

    private IntegradorWebStorm $integraWebStorm;

    /**
     * @required
     * @param IntegradorWebStorm $integraWebStorm
     */
    public function setIntegraWebStorm(IntegradorWebStorm $integraWebStorm): void
    {
        $this->integraWebStorm = $integraWebStorm;
    }

    protected function configure()
    {
        $this->setName('crosierappradx:integrarWebStorm');
        $this->addArgument('tipoIntegracao', InputArgument::REQUIRED, 'Tipo de Integração: "vendas", "reintegrarProdutosDesatualizados"');
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
            case 'vendas':
                try {
                    $output->writeln('Obtendo vendas');
                    $dtBase = DateTimeUtils::parseDateStr($input->getArgument('dtBase'));
                    if (!$dtBase) {
                        // Por padrão não é passado uma data no comando. Pega, portanto, as de ontem e hoje, para
                        // não correr o risco de perder alguma venda de perto de meia-noite.

                        $hoje = (new \DateTime())->setTime(12, 0);
                        $ontem = (clone $hoje)->sub((new \DateInterval('P1D')));

                        $qtdeVendas_ontem = $this->integraWebStorm->obterVendas($ontem);
                        $output->writeln('Ontem: ' . $ontem->format('d/m/Y') . ': ' . $qtdeVendas_ontem);

                        $qtdeVendas_hoje = $this->integraWebStorm->obterVendas($hoje);
                        $output->writeln('Hoje: ' . $hoje->format('d/m/Y') . ': ' . $qtdeVendas_hoje);
                    } else {
                        $qtdeVendas = $this->integraWebStorm->obterVendas($dtBase);
                        $output->writeln('OK: ' . $qtdeVendas . ' venda(s) integrada(s)');
                    }
                } catch (ViewException $e) {
                    $output->writeln('Erro ao obterVendas');
                    $output->writeln($e->getMessage());
                    $output->writeln($e->getTraceAsString());
                }
                break;
            case 'reintegrarProdutosDesatualizados':
                try {
                    $output->writeln('Reenviando para integração produtos alterados');
                    $qtde = $this->integraWebStorm->reenviarParaIntegracaoProdutosAlterados();
                    $output->writeln('OK: ' . $qtde . ' produtos enviados');
                } catch (ViewException $e) {
                    $output->writeln('Erro ao reenviarParaIntegracaoProdutosAlterados');
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