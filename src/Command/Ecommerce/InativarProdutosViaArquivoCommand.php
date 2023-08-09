<?php

namespace App\Command\Ecommerce;

use App\Business\Ecommerce\InativarProdutosViaArquivo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * php bin/console radx:inativarProdutosViaArquivo PATH_PARA_O_ARQUIVO
 *
 * O arquivo deve conter um est_produto.id por linha.
 *
 * @author Carlos Eduardo Pauluk
 */
class InativarProdutosViaArquivoCommand extends Command
{

    /** @required */
    public InativarProdutosViaArquivo $inativarProdutosViaArquivo;


    protected function configure()
    {
        $this->setName('radx:inativarProdutosViaArquivo');
        $this->addArgument(
            'pathDoArquivo',
            InputArgument::REQUIRED,
            'Path para o arquivo'
        );
    }


    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arquivo = $input->getArgument('pathDoArquivo');
        $this->inativarProdutosViaArquivo->inativarProdutos($arquivo);
        return 1;
    }
}
