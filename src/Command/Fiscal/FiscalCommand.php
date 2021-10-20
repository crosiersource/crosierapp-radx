<?php

namespace App\Command\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class FiscalCommand extends Command
{

    private NFeUtils $nfeUtils;

    private DistDFeBusiness $distDFeBusiness;


    /**
     * @required
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }

    /**
     * @required
     * @param DistDFeBusiness $distDFeBusiness
     */
    public function setDistDFeBusiness(DistDFeBusiness $distDFeBusiness): void
    {
        $this->distDFeBusiness = $distDFeBusiness;
    }


    protected function configure()
    {
        $this->setName('crosierappradx:fiscal');
        $this->addOption(
            'operacao',
            null,
            InputOption::VALUE_REQUIRED,
            'Operação a ser executada'
        );
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operacao = $input->getOption('operacao');
        if (!$operacao) {
            throw new \InvalidArgumentException('operacao n/d');
        }

        switch ($operacao) {
            case 'obterDistDFes':
                $this->obterDistDFes($output);
                break;
            default:
                throw new \RuntimeException('operacao desconhecida: ' . $operacao);
        }
        return Command::SUCCESS;
    }

    /**
     * Chamar com:
     * - php bin/console crosierappradx:fiscal --operacao=obterDistDFes
     * 
     * @param OutputInterface $output
     * @param int|null $primeiroNSU
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function obterDistDFes(OutputInterface $output, int $primeiroNSU = null)
    {
        $cnpjs = $this->nfeUtils->getNFeConfigsCNPJs();
        foreach ($cnpjs as $cnpj) {
            $output->write('Obtendo DistDFes para o CNPJ: ' . $cnpj);
            if ($primeiroNSU) {
                $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpj);
            } else {
                $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpj);
            }
            $output->write($q ? $q . ' DistDFe(s) obtidos' : 'Nenhum DistDFe obtido');
            $output->write('Processando obtidos...');
            $this->distDFeBusiness->processarDistDFesObtidos();
            $output->write('OK');
            $output->write('----------');
        }
    }

}