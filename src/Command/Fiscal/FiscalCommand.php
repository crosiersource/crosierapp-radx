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
 *
 * @author Carlos Eduardo Pauluk
 */
class FiscalCommand extends Command
{

    private NFeUtils $nfeUtils;

    private DistDFeBusiness $distDFeBusiness;

    private SpedNFeBusiness $spedNFeBusiness;

    private NotaFiscalEntityHandler $notaFiscalEntityHandler;
    
    private SyslogBusiness $syslog;


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

    /**
     * @required
     * @param SpedNFeBusiness $spedNFeBusiness
     */
    public function setSpedNFeBusiness(SpedNFeBusiness $spedNFeBusiness): void
    {
        $this->spedNFeBusiness = $spedNFeBusiness;
    }

    /**
     * @required
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $notaFiscalEntityHandler): void
    {
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
    }


    /**
     * @required
     * @param SyslogBusiness $syslog
     */
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
     * - php bin/console crosierappradx:fiscal --operacao=obterDistDFes
     *
     * @param OutputInterface $output
     * @param int|null $primeiroNSU
     * @throws ViewException
     */
    public function obterDistDFes(OutputInterface $output, int $primeiroNSU = null)
    {
        $cnpjs = $this->nfeUtils->getNFeConfigsCNPJs();
        foreach ($cnpjs as $cnpj) {
            $output->writeln('Obtendo DistDFes para o CNPJ: ' . $cnpj);
            try {
                if ($primeiroNSU) {
                    $q = $this->distDFeBusiness->obterDistDFes($primeiroNSU, $cnpj);
                } else {
                    $q = $this->distDFeBusiness->obterDistDFesAPartirDoUltimoNSU($cnpj);
                }
                $output->writeln($q ? $q . ' DistDFe(s) obtidos' : 'Nenhum DistDFe obtido');
                $output->writeln('Processando obtidos...');
                $this->distDFeBusiness->processarDistDFesObtidos();
                $output->writeln('OK');
            } catch (ViewException $e) {
                $output->writeln('Erro ao obter e processar DistDFes para o CNPJ: ' . $cnpj);
                $output->writeln($e->getMessage());
            }
            $output->writeln('----------');
        }
    }

    /**
     * Chamar com:
     * - php bin/console crosierappradx:fiscal --operacao=manifestarCienciaParaUltimas
     *
     * @throws ViewException|\Doctrine\DBAL\Exception
     */
    public function manifestarCienciaParaUltimas(OutputInterface $output, int $primeiroNSU = null)
    {

        $rsDias = $this->nfeUtils->conn
            ->fetchAssociative(
                'SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                [
                    'chave' => 'manifestarCienciaParaUltimas.dias',
                    'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                ]);

        $dias = $rsDias['valor'] ?? 3;
        $cnpjs = $this->nfeUtils->getNFeConfigsCNPJs();

        $repoNotaFiscal = $this->notaFiscalEntityHandler->getDoctrine()->getRepository(NotaFiscal::class);

        foreach ($cnpjs as $cnpj) {
            $msg = 'Manifestando "210210 - CIÊNCIA DA OPERAÇÃO" para o CNPJ: ' . $cnpj;
            $this->syslog->info($msg);
            $output->writeln($msg);
            try {
                $rsNFs = $this->nfeUtils->conn
                    ->fetchAllAssociative(
                        'SELECT id FROM fis_nf WHERE documento_destinatario = :cnpj AND resumo = true AND dt_emissao >= DATE(NOW()) - INTERVAL :dias DAY',
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
                            $this->spedNFeBusiness->manifestar($nf, 'ciencia');
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