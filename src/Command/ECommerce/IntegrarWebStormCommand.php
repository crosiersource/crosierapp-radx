<?php

namespace App\Command\ECommerce;

use App\Business\ECommerce\IntegradorWebStorm;
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
        $this->addArgument('tipoIntegracao', InputArgument::REQUIRED, 'Tipo de Integração: "vendas", "produtos", "estoqueEprecos"');
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
                        $ontem = $dtBase->sub((new \DateInterval('P1D')));
                        $hoje = new \DateTime();

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
            case 'produtos':
                try {
                    $output->writeln('Integrando produtos');
                    $qtde = $this->integraWebStorm->reenviarProdutosParaIntegracao();
                    $output->writeln('OK: ' . $qtde . ' produtos marcados para integração');
                } catch (ViewException $e) {
                    $output->writeln('Erro ao reenviarProdutosParaIntegracao');
                    $output->writeln($e->getMessage());
                    $output->writeln($e->getTraceAsString());
                }
                break;
            case 'estoqueEprecos':
                try {
                    $output->writeln('Atualizando estoques e preços');
                    $qtde = $this->integraWebStorm->atualizaEstoqueEPrecos();
                    $output->writeln('OK: ' . $qtde . ' produtos atualizados');
                } catch (ViewException $e) {
                    $output->writeln('Erro ao atualizaEstoqueEPrecos');
                    $output->writeln($e->getMessage());
                    $output->writeln($e->getTraceAsString());
                }
                break;
            default:
                throw new \RuntimeException('tipoIntegracao desconhecido: ' . $tipoIntegracao);
        }
        return 1;
    }

    private function integrarVendas()
    {

    }


}