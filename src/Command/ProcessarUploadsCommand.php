<?php

namespace App\Command;

use App\Business\Estoque\UploadProdutoCsv;
use App\Business\Estoque\UploadProdutosPrecosCsv;
use App\Business\Estoque\UploadProdutosSaldosCsv;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class ProcessarUploadsCommand extends Command implements ServiceSubscriberInterface
{

    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
        parent::__construct();
    }

    public static function getSubscribedServices(): array
    {
        return [
            "UploadProdutoCsv" => UploadProdutoCsv::class,
            "UploadProdutosSaldosCsv" => UploadProdutosSaldosCsv::class,
            "UploadProdutosPrecosCsv" => UploadProdutosPrecosCsv::class,
        ];
    }


    protected function configure()
    {
        $this->setName('crosierappradx:processarUploads');
        $this->addArgument('handler', InputArgument::REQUIRED, 'Handler [' . implode(',', array_keys(ProcessarUploadsCommand::getSubscribedServices())) . ']');
        $this->addArgument('atualizarExistentes', InputArgument::OPTIONAL);
    }


    /**
     * Cria, baseado no comando do usuário, a instância de relatório apropriada para o trabalho a ser desenvolvido e a executa.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @resultado int|null|void
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $handlerName = $input->getArgument('handler');
        $atualizarExistentes = $input->getArgument('atualizarExistentes');
        if (
            ProcessarUploadsCommand::getSubscribedServices()[$handlerName] ?? null &&
            $this->locator->has($handlerName)
        ) {
            $handler = $this->locator->get($handlerName);
            try {
                $handler->processar($atualizarExistentes);
            } catch (\Exception $ex) {
            }
        } else {
            throw new \Exception("O handler " . $handlerName . " não é suportado.");
        }
        return 1;
    }
}
