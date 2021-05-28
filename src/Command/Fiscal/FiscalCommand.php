<?php

namespace App\Command\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        $this->addArgument('operacao', InputArgument::REQUIRED, '');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operacao = $input->getArgument('operacao');

        switch ($operacao) {
            case 'obterDistDFes':
                $this->obterDistDFes();
                break;
            default:
                throw new \RuntimeException('operacao desconhecida: ' . $operacao);
        }
        return 0;
    }


    public function obterDistDFes(int $primeiroNSU = null)
    {
        $cnpjEmUso = $this->nfeUtils->getNFeConfigsEmUso()['cnpj'];
        if ($primeiroNSU) {
            $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpjEmUso);
        } else {
            $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpjEmUso);
        }
        $this->distDFeBusiness->processarDistDFesObtidos();
    }

}