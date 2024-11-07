<?php

namespace App\Command\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\DistDFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NFeUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\SpedNFeBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class FiscalCommand extends Command
{

    private NFeUtils $nfeUtils;

    private DistDFeBusiness $distDFeBusiness;

    private SpedNFeBusiness $spedNFeBusiness;

    private NotaFiscalEntityHandler $notaFiscalEntityHandler;

    private SyslogBusiness $syslog;


    /** @required */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }

    /** @required */
    public function setDistDFeBusiness(DistDFeBusiness $distDFeBusiness): void
    {
        $distDFeBusiness->setEchoToLogger();
        $this->distDFeBusiness = $distDFeBusiness;
    }

    /** @required */
    public function setSpedNFeBusiness(SpedNFeBusiness $spedNFeBusiness): void
    {
        $this->spedNFeBusiness = $spedNFeBusiness;
    }

    /** @required */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $notaFiscalEntityHandler): void
    {
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
    }

    /** @required */
    public function setSyslog(SyslogBusiness $syslog): void
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
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
        $this->addOption(
            'cnpj',
            null,
            InputOption::VALUE_OPTIONAL,
            'Apenas para o CNPJ...'
        );
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operacao = $input->getOption('operacao');
        $cnpj = $input->getOption('cnpj');
        if (!$operacao) {
            throw new \InvalidArgumentException('operacao n/d');
        }

        switch ($operacao) {
            case 'obterDistDFes':
                $this->obterDistDFes($output, $cnpj);
                break;
            case 'processarDistDFes':
                $this->processarDistDFes($output, $cnpj);
                break;
            case 'manifestarCienciaParaUltimas':
                $this->manifestarCienciaParaUltimas($output);
                break;
            default:
                throw new \RuntimeException('operacao desconhecida: ' . $operacao);
        }
        return Command::SUCCESS;
    }

    /**
     * Chamar com:
     * - php bin/console crosierappradx:fiscal --operacao=obterDistDFes [--cnpj=00000000000000]
     *
     * @throws ViewException
     */
    public function obterDistDFes(
        OutputInterface $output,
        ?string         $cnpj = null,
    ): void
    {
        if ($cnpj) {
            $nfeConfig = $this->nfeUtils->getNFeConfigsByCNPJ($cnpj);
            $nfeConfigs = [$nfeConfig];
        } else {
            $nfeConfigs = $this->nfeUtils->getNFeConfigs();
        }

        foreach ($nfeConfigs as $nfeConfig) {

            $output->writeln(print_r($nfeConfig, true));
            
            $this->doObterDistDFes($nfeConfig['cnpj'], $output, null, false);

            if ($nfeConfig['obterDistDFesParaCTes'] ?? false) {
                $this->doObterDistDFes($nfeConfig['cnpj'], $output, null, true);
            }

            $output->writeln('Processando obtidos...');
            $this->distDFeBusiness->processarDistDFesObtidos($nfeConfig['cnpj']);
            $output->writeln('OK');
            $output->writeln('');
            $output->writeln('----------');
        }
    }


    /**
     * Chamar com:
     * - php bin/console crosierappradx:fiscal --operacao=processarDistDFes [--cnpj=00000000000000]
     *
     * @throws ViewException
     */
    public function processarDistDFes(
        OutputInterface $output,
        ?string         $cnpj = null,
    ): void
    {
        if ($cnpj) {
            $nfeConfig = $this->nfeUtils->getNFeConfigsByCNPJ($cnpj);
            $nfeConfigs = [$nfeConfig];
        } else {
            $nfeConfigs = $this->nfeUtils->getNFeConfigs();
        }

        foreach ($nfeConfigs as $nfeConfig) {
            $output->writeln('Processando obtidos...');
            $this->distDFeBusiness->processarDistDFesObtidos($nfeConfig['cnpj']);
            $output->writeln('OK');
            $output->writeln('');
            $output->writeln('----------');
        }
    }
    

    private function doObterDistDFes(
        string          $cnpj,
        OutputInterface $output,
        ?int            $primeiroNSU,
        ?bool           $paraCTes
    ): void
    {
        $output->writeln('Obtendo DistDFes ' . ($paraCTes ? '(para CTes)' : '') . ' para o CNPJ: ' . $cnpj);
        try {
            if ($primeiroNSU) {
                $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpj, $paraCTes);
            } else {
                $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpj, $paraCTes);
            }
            $output->writeln($q ? $q . ' DistDFe(s) obtidos' : 'Nenhum DistDFe obtido');

        } catch (ViewException $e) {
            $output->writeln('Erro ao obter e processar DistDFes para o CNPJ: ' . $cnpj);
            $output->writeln($e->getMessage());
        }
        $output->writeln('----------');
    }


    /**
     * Chamar com:
     * - php bin/console crosierappradx:fiscal --operacao=manifestarCienciaParaUltimas
     *
     * @throws ViewException|\Doctrine\DBAL\Exception
     */
    public function manifestarCienciaParaUltimas(
        OutputInterface $output,
        int             $primeiroNSU = null
    ): void
    {

        $rsDias = $this->nfeUtils->conn
            ->fetchAssociative(
                'SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                [
                    'chave' => 'manifestarCienciaParaUltimas.dias',
                    'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                ]);

        $dias = $rsDias['valor'] ?? 10;
        $cnpjs = $this->nfeUtils->getNFeConfigsCNPJs();

        $repoNotaFiscal = $this->notaFiscalEntityHandler->getDoctrine()->getRepository(NotaFiscal::class);

        foreach ($cnpjs as $cnpj) {
            $msg = 'Manifestando "210210 - CIÊNCIA DA OPERAÇÃO" para o CNPJ: ' . $cnpj;
            $this->syslog->info($msg);
            $output->writeln($msg);
            try {
                $rsNFs = $this->nfeUtils->conn
                    ->fetchAllAssociative(
                        'SELECT id FROM fis_nf WHERE (manifest_dest IS NULL OR trim(manifest_dest) = \'\') AND documento_destinatario = :cnpj AND resumo = true AND dt_emissao >= DATE(NOW()) - INTERVAL :dias DAY',
                        [
                            'cnpj' => $cnpj,
                            'dias' => $dias
                        ]);

                if ($rsNFs) {

                    $msg = count($rsNFs) . ' NFs para manifestar nos últimos ' . $dias . ' dias para o CNPJ ' . $cnpj . '. Manifestando...';
                    $output->writeln($msg);
                    $this->syslog->info($msg);

                    foreach ($rsNFs as $rNF) {
                        try {
                            $nf = $repoNotaFiscal->find($rNF);
                            $this->spedNFeBusiness->manifestar($nf, 210210); // 210210 - Ciência da Operação
                            $msg = 'NF manifestada com sucesso (chave: ' . $nf->chaveAcesso . ')';
                            $output->writeln($msg);
                            $this->syslog->info($msg);
                        } catch (ViewException $e) {
                            $this->syslog->err('Ocorreu um erro ao manifestar a NF (chave: ' . $nf->chaveAcesso . ')');
                        }
                    }
                    $output->writeln('OK');
                } else {
                    $msg = 'Nenhuma NF encontrada para o CNPJ ' . $cnpj;
                    $this->syslog->info($msg);
                    $output->writeln($msg);
                }
            } catch (ViewException $e) {
                $msg = 'Erro - manifestarCienciaParaUltimas - CNPJ: ' . $cnpj;
                $this->syslog->err($msg, $e->getMessage());
                $output->writeln($msg);
                $output->writeln($e->getMessage());
            }
            $output->writeln('----------');
        }
    }


}