<?php

namespace App\Command\Estoque;

use App\Business\Estoque\ProdutoImportBusiness;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProcessarUploadsCommand extends Command
{

    /** @var ProdutoImportBusiness */
    private $produtoImportBusiness;

    /**
     * @required
     * @param ProdutoImportBusiness $produtoImportBusiness
     */
    public function setProdutoBusiness(ProdutoImportBusiness $produtoImportBusiness): void
    {
        $this->produtoImportBusiness = $produtoImportBusiness;
    }


    protected function configure()
    {
        $this->setName('crosierappvendest:processarUploads');
        $this->addArgument('tipoUpload', InputArgument::REQUIRED,
            'Tipo de upload [\'ESTOQUE\']');
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
        $tipoUpload = $input->getArgument('tipoUpload');
        switch ($tipoUpload) {
            case 'ESTOQUE':
                $this->produtoImportBusiness->processarArquivosNaFila();
                break;
            default:
                throw new \RuntimeException('tipoUpload desconhecido');
        }
    }

}