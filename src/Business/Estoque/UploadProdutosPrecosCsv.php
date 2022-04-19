<?php

namespace App\Business\Estoque;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;

/**
 * Rodar com:
 * php bin/console crosierappradx:processarUploads UploadProdutosPrecosCsv
 *
 * @author Carlos Eduardo Pauluk
 */
class UploadProdutosPrecosCsv
{

    private SyslogBusiness $syslog;

    private ProdutoEntityHandler $produtoEntityHandler;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private string $pasta;

    private ?array $estProdutos = null;

    public function __construct(
        ProdutoEntityHandler   $produtoEntityHandler,
        SyslogBusiness         $syslog,
        AppConfigEntityHandler $appConfigEntityHandler)
    {
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->syslog = $syslog->setApp('radx')
            ->setComponent('UploadProdutosPrecosCsv')
            ->setEcho(true);
        $this->pasta = $_SERVER['PASTA_UPLOADS'] . 'est_produtos_precos_csv/';
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }


    /**
     *
     * @throws ViewException
     */
    public function processar(): void
    {
        $pastaFila = $this->pasta . 'fila/';
        @mkdir($pastaFila, 0777, true);
        @mkdir($this->pasta . 'falha/', 0777, true);
        @mkdir($this->pasta . 'ok/', 0777, true);
        $this->syslog->info('Processando arquivos na fila.');
        $files = scandir($pastaFila, 0);
        if (count($files) < 3) { // conta sempre mais o "." e o ".."
            $this->syslog->info('Nenhum arquivo para processar. Finalizando.');
            return;
        }
        $this->syslog->info('São ' . (count($files) - 2) . ' arquivo(s) para processar');
        foreach ($files as $file) {
            if (!in_array($file, array('.', '..')) && !is_dir($pastaFila . $file)) {
                try {
                    $this->processarArquivo($file);
                    $this->marcarDtHrAtualizacao();
                    $this->syslog->info('Arquivo processado com sucesso.');
                    @unlink($this->pasta . 'ok/ultimo.zip');
                    rename($pastaFila . $file, $this->pasta . 'ok/ultimo.zip');
                    $this->syslog->info('Arquivo movido para pasta "ok".');
                } catch (\Exception $e) {
                    @rename($pastaFila . $file, $this->pasta . 'falha/' . $file);
                    $this->syslog->err('Erro processarArquivosNaFila()');
                    $this->syslog->err('processarArquivosNaFila', $e->getTraceAsString());
                    $this->syslog->info('Arquivo movido para pasta "falha".');
                    $this->marcarDtHrAtualizacao();
                    throw new ViewException('Erro ao processarArquivosNaFila', 0, $e);
                }
            }
        }
        $this->syslog->info('Finalizando com sucesso.');
    }

    /**
     * @param string $arquivo
     * @return int
     */
    public function processarArquivo(string $arquivo): int
    {
        $this->syslog->info('Iniciando processamento do arquivo ' . $arquivo);

        try {
            $pastaFila = $this->pasta . 'fila/';

            $zip = zip_open($pastaFila . $arquivo);
            $zip_entry = zip_read($zip);
            zip_entry_open($zip, $zip_entry, "r");
            $conteudo = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
            zip_close($zip);

            $linhas = explode(PHP_EOL, $conteudo);
            $totalRegistros = count($linhas) - 2;

            if ($totalRegistros < 1) {
                $this->syslog->info('Nenhum registro para processar na est_produto_preco');
                return 0;
            }

            $alterados = 0;
            $naoAlterados = 0;

            $this->carregarEstProdutos();

            $nomesCampos = str_getcsv($linhas[0]);

            $batchSize = 500;
            $iBatch = 0;
            $this->produtoEntityHandler->getDoctrine()->getConnection()->getConfiguration()->setSQLLogger(null);

            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);

            for ($i = 1; $i <= $totalRegistros; $i++) {
                try {
                    $linha = $linhas[$i];
                    if (!trim($linha)) {
                        continue;
                    }

                    $campos = str_getcsv($linha);
                    // erp_codigo,preco_ecommerce,preco_tabela,preco_custo,preco_venda_com_desconto,preco_promocao

                    foreach ($campos as $k => $valor) {
                        $campos[$nomesCampos[$k]] = trim($valor);
                        unset($campos[$k]);
                    }

                    $campos['preco_ecommerce'] = (float)$campos['preco_ecommerce'];
                    $campos['preco_tabela'] = (float)$campos['preco_tabela'];
                    $campos['preco_custo'] = (float)$campos['preco_custo'];
                    $campos['preco_venda_com_desconto'] = (float)$campos['preco_venda_com_desconto'];
                    $campos['preco_promocao'] = (float)$campos['preco_promocao'];

                    $estProduto = $this->estProdutos[$campos['erp_codigo']] ?? false;
                    $produto = null;

                    if (!$estProduto) {
                        $this->syslog->err($i . '/' . $totalRegistros . ' - Impossível atualizar est_produto_preco', 'Não existe registro para erp_codigo: ' . $campos['erp_codigo']);
                        continue;
                    }

                    if (
                        ((float)$estProduto['preco_ecommerce'] !== (float)$campos['preco_ecommerce']) ||
                        ((float)$estProduto['preco_tabela'] !== (float)$campos['preco_tabela']) ||
                        ((float)$estProduto['preco_custo'] !== (float)$campos['preco_custo']) ||
                        ((float)$estProduto['preco_venda_com_desconto'] !== (float)$campos['preco_venda_com_desconto']) ||
                        ((float)$estProduto['preco_promocao'] !== (float)$campos['preco_promocao'])
                    ) {
                        /** @var Produto $produto */
                        $produto = $repoProduto->find($this->estProdutos[$campos['erp_codigo']]['produto_id']);
                        $produto->jsonData['preco_ecommerce'] = (float)$campos['preco_ecommerce'];
                        $produto->jsonData['preco_tabela'] = (float)$campos['preco_tabela'];
                        $produto->jsonData['preco_custo'] = (float)$campos['preco_custo'];
                        $produto->jsonData['preco_venda_com_desconto'] = (float)$campos['preco_venda_com_desconto'];
                        $produto->jsonData['preco_promocao'] = (float)$campos['preco_promocao'];
                        if ($produto->ecommerce) {
                            $produto->jsonData['ecommerce_desatualizado'] = 'S';
                        } else {
                            unset($produto->jsonData['ecommerce_desatualizado']);
                        }
                        $produto = $this->produtoEntityHandler->save($produto, false);
                        $alterados++;
                    } else {
                        $naoAlterados++;
                    }

                    $this->syslog->info($i . '/' . $totalRegistros . ') produto alterado (' . $campos['erp_codigo'] . ')');

                    if ((++$iBatch % $batchSize) === 0) {
                        $this->produtoEntityHandler->getDoctrine()->flush();
                        $this->produtoEntityHandler->getDoctrine()->clear(); // Detaches all objects from Doctrine!
                        $this->carregarEstProdutos();
                    }

                } catch (\Exception $e) {
                    $this->syslog->err('Erro ao agrupar campos para linha [' . $linhas[$i] . ']. Continuando...', $e->getTraceAsString());
                }
            }

            $this->produtoEntityHandler->getDoctrine()->flush();
            $this->produtoEntityHandler->getDoctrine()->clear(); // Detaches all objects from Doctrine!

            $this->syslog->info('Total alterados: ' . $alterados);
            $this->syslog->info('Total NÃO alterados: ' . $naoAlterados);
            return $alterados;
        } catch (\Throwable $e) {
            $this->syslog->err('processarArquivo() - Erro ao inserir a linha "' . $linha . '"', $e->getTraceAsString());
            return 0;
        }
    }


    private function carregarEstProdutos()
    {
        // preco_ecommerce,preco_tabela,preco_custo,preco_venda_com_desconto,preco_promocao

        $rs = $this->produtoEntityHandler->getDoctrine()->getConnection()->fetchAllAssociative(
            'SELECT 
                        p.id as produto_id, 
                        p.codigo as erp_codigo,
                        p.json_data->>"$.preco_site" as preco_ecommerce,
                        p.json_data->>"$.preco_tabela" as preco_tabela,
                        p.json_data->>"$.preco_custo" as preco_custo,
                        p.json_data->>"$.preco_venda_com_desconto" as preco_venda_com_desconto,
                        p.json_data->>"$.preco_promocao" as preco_promocao
                   FROM 
                        est_produto p');

        $this->estProdutos = [];

        foreach ($rs as $r) {
            $this->estProdutos[$r['erp_codigo']] = $r;
        }
    }


    /**
     * @throws ViewException
     */
    protected function marcarDtHrAtualizacao(): void
    {
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->produtoEntityHandler->getDoctrine()->getRepository(AppConfig::class);
            /** @var AppConfig $appConfig */
            $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'UploadProdutosPrecosCsv.dthrAtualizacao'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfig) {
                $appConfig = new AppConfig();
                $appConfig->chave = 'UploadProdutosPrecosCsv.dthrAtualizacao';
                $appConfig->appUUID = $_SERVER['CROSIERAPP_UUID'];
            }
            $appConfig->valor = (new \DateTime())->format('Y-m-d H:i:s.u');
            $this->appConfigEntityHandler->save($appConfig);
        } catch (\Exception $e) {
            $errMsg = 'Erro ao marcar app_config (UploadProdutosPrecosCsv.dthrAtualizacao)';
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }


    protected function getPastaUpload(): string
    {
        return $_SERVER['PASTA_UPLOAD_RELESTOQUE01'];
    }


}
