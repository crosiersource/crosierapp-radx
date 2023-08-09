<?php

namespace App\Business\Ecommerce;

use App\Messenger\Ecommerce\Message\IntegrarProdutoEcommerceMessage;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class InativarProdutosViaArquivo
{

    private SyslogBusiness $syslog;

    private ProdutoEntityHandler $produtoEntityHandler;

    private MessageBusInterface $messageBus;


    public function __construct(
        ProdutoEntityHandler $produtoEntityHandler,
        MessageBusInterface  $messageBus,
        SyslogBusiness       $syslog)
    {
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->messageBus = $messageBus;
        $this->syslog = $syslog->setApp('radx')
            ->setComponent('InativarProdutosViaArquivo')
            ->setEcho(true);
    }


    /**
     * @throws ViewException
     */
    public function inativarProdutos(string $arquivo): void
    {
        $this->syslog->info('Iniciando processamento do arquivo ' . $arquivo);

        $linhas = file($arquivo, FILE_IGNORE_NEW_LINES);

        $ids = [];
        foreach ($linhas as $linha) {
            if (!trim($linha) || !is_numeric($linha)) {
                continue;
            }
            $ids[] = trim($linha);
        }

        $totalRegistros = count($ids);

        $batchSize = 500;
        $iBatch = 0;
        (new Configuration())->setMiddlewares([]);
        $this->produtoEntityHandler->getDoctrine()->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->produtoEntityHandler->getDoctrine()->getConnection()->getConfiguration()->setMiddlewares([]); // DBAL 3

        try {
            $this->produtoEntityHandler->getDoctrine()->getConnection()->beginTransaction();
            for ($i = 0; $i < $totalRegistros; $i++) {
                $id = $ids[$i];
                $this->syslog->info('Inativando produto ' . $id);
                $produto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class)->find($id);
                if (!$produto) {
                    $this->syslog->err('Produto não encontrado: ' . $id . '. Continuando...');
                    continue;
                }
                if (!$produto->ecommerce) {
                    $this->syslog->err('Produto não está integrado ao e-commerce: ' . $id . '. Continuando...');
                    continue;
                }
                $produto->jsonData['ecommerce_desatualizado'] = 'S';
                $produto->status = 'INATIVO';

                $produto = $this->produtoEntityHandler->save($produto, false);

                $this->messageBus->dispatch(new IntegrarProdutoEcommerceMessage($produto->getId(), false));
                $this->syslog->info($i . '/' . $totalRegistros . ') produto inativado e enviado para integração (' . $produto->getId() . ')');

                if ((++$iBatch % $batchSize) === 0) {
                    $this->produtoEntityHandler->getDoctrine()->flush();
                    $this->produtoEntityHandler->getDoctrine()->clear();
                }
            }
            $this->produtoEntityHandler->getDoctrine()->flush();
            $this->produtoEntityHandler->getDoctrine()->clear();
            $this->produtoEntityHandler->getDoctrine()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->syslog->err('Ocorreu um erro ao processar o arquivo ' . $arquivo . '. Msg: ' . $e->getMessage(), $e->getTraceAsString());
            try {
                $this->produtoEntityHandler->getDoctrine()->getConnection()->rollBack();
            } catch (Exception $e) {
            }
        }
    }


}
