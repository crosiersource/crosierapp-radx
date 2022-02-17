<?php

namespace App\Business\Estoque;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\ProdutoSaldo;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoSaldoEntityHandler;

/**
 * @author Carlos Eduardo Pauluk
 */
class UploadProdutosSaldosCsv
{

    private SyslogBusiness $syslog;

    private ProdutoSaldoEntityHandler $produtoSaldoEntityHandler;

    private string $pasta;

    private ?array $estProdutos = null;
    private ?array $estProdutosSaldos = null;


    public function __construct(
        ProdutoSaldoEntityHandler $produtoSaldoEntityHandler,
        SyslogBusiness            $syslog)
    {
        $this->produtoSaldoEntityHandler = $produtoSaldoEntityHandler;
        $this->syslog = $syslog->setApp('radx')
            ->setComponent('UploadProdutosSaldosCsv')
            ->setEcho(true);
        $this->pasta = $_SERVER['PASTA_UPLOADS'] . 'est_produtos_saldos_csv/';
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
                    // $this->marcarDtHrAtualizacao(true);
                    $this->syslog->info('Arquivo processado com sucesso.');
                    @unlink($this->pasta . 'ok/ultimo.zip');
                    rename($pastaFila . $file, $this->pasta . 'ok/ultimo.zip');
                    $this->syslog->info('Arquivo movido para pasta "ok".');
                } catch (\Exception $e) {
                    @rename($pastaFila . $file, $this->pasta . 'falha/' . $file);
                    $this->syslog->err('Erro processarArquivosNaFila()');
                    $this->syslog->err('processarArquivosNaFila', $e->getTraceAsString());
                    $this->syslog->info('Arquivo movido para pasta "falha".');
                    // $this->marcarDtHrAtualizacao(false);
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

            $alterados = 0;
            $naoAlterados = 0;

            $this->carregarEstProdutos();
            $this->carregarEstProdutosSaldos();

            $nomesCampos = str_getcsv($linhas[0]);

            $conn = $this->produtoSaldoEntityHandler->getDoctrine()->getConnection();

            $batchSize = 50;
            $iBatch = 0;
            $this->produtoSaldoEntityHandler->getDoctrine()->getConnection()->getConfiguration()->setSQLLogger(null);
            
            $repoProdutoSaldo = $this->produtoSaldoEntityHandler->getDoctrine()->getRepository(ProdutoSaldo::class); 
            $repoProduto = $this->produtoSaldoEntityHandler->getDoctrine()->getRepository(Produto::class); 

            for ($i = 1; $i < $totalRegistros; $i++) {
                try {
                    $linha = $linhas[$i];
                    if (!trim($linha)) {
                        continue;
                    }

                    $campos = str_getcsv($linha);

                    foreach ($campos as $k => $valor) {
                        $campos[$nomesCampos[$k]] = trim($valor);
                        unset($campos[$k]);
                    }

                    $campos['saldo'] = (float)$campos['saldo'];

                    if (!($this->estProdutos[$campos['erp_codigo']] ?? false)) {
                        $this->syslog->info('Impossível atualizar est_produto_saldo', 'Não existe registro para erp_codigo: ' . $campos['erp_codigo']);
                        continue;
                    }

                    $agora = (new \DateTime())->format('Y-m-d H:i:s');

                    $rsProdutoSaldo = $this->estProdutosSaldos[$campos['erp_codigo']] ?? null;

                    if (!$rsProdutoSaldo) {
                        $produtoSaldo = new ProdutoSaldo();
                        $produtoSaldo->produto = $repoProduto->find($this->estProdutos[$campos['erp_codigo']]['id']);
                    } else {
                        $produtoSaldo = $repoProdutoSaldo->find($rsProdutoSaldo['saldo_id']);
                    }

                    if (!$rsProdutoSaldo || ((float)$rsProdutoSaldo['qtde'] !== $campos['saldo'])) {
                        $produtoSaldo->qtde = $campos['saldo'];
                        $this->produtoSaldoEntityHandler->save($produtoSaldo);
                        $alterados++;
                        $this->syslog->info($alterados . ') est_produto_saldo alterado para ' . $campos['erp_codigo']);
                    } else {
                        $naoAlterados++;
                    }

                    if ((++$iBatch % $batchSize) === 0) {
                        $this->produtoSaldoEntityHandler->getDoctrine()->flush();
                        $this->produtoSaldoEntityHandler->getDoctrine()->clear(); // Detaches all objects from Doctrine!
                        $this->carregarEstProdutos();
                        $this->carregarEstProdutosSaldos();
                    }

                } catch (\Exception $e) {
                    $this->syslog->err('Erro ao agrupar campos para linha [' . $linhas[$i] . ']. Continuando...', $e->getTraceAsString());
                }
            }

            $this->produtoSaldoEntityHandler->getDoctrine()->flush();
            $this->produtoSaldoEntityHandler->getDoctrine()->clear(); // Detaches all objects from Doctrine!

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
        $rs = $this->produtoSaldoEntityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT id, json_data->>"$.erp_codigo" as erp_codigo, nome FROM est_produto');
        $this->estProdutos = [];
        foreach ($rs as $r) {
            $this->estProdutos[$r['erp_codigo']] = $r;
        }
    }

    private function carregarEstProdutosSaldos()
    {
        $rs = $this->produtoSaldoEntityHandler->getDoctrine()->getConnection()->fetchAllAssociative(
            'SELECT 
                        p.id as produto_id, 
                        p.json_data->>"$.erp_codigo" as erp_codigo,
                        saldo.id as saldo_id,
                        saldo.qtde 
                   FROM 
                        est_produto p, est_produto_saldo saldo 
                   WHERE 
                        saldo.produto_id = p.id');

        $this->estProdutosSaldos = [];

        foreach ($rs as $r) {
            $this->estProdutosSaldos[$r['erp_codigo']] = $r;
        }
    }


    /**
     * @throws ViewException
     */
    protected function marcarDtHrAtualizacao(bool $foiOk): void
    {
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
            /** @var AppConfig $appConfig */
            $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'relEstoque01.dthrAtualizacao'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfig) {
                $appConfig = new AppConfig();
                $appConfig->chave = 'relEstoque01.dthrAtualizacao';
                $appConfig->appUUID = $_SERVER['CROSIERAPP_UUID'];
            }
            $appConfig->valor = (new \DateTime())->format('Y-m-d H:i:s.u');
            $this->appConfigEntityHandler->save($appConfig);

            $this->salvarImportadorStatus($foiOk);
        } catch (\Exception $e) {
            $errMsg = 'Erro ao marcar app_config (relEstoque01.dthrAtualizacao)';
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }


    /**
     * @throws ViewException
     */
    protected function salvarImportadorStatus(bool $foiOk)
    {
        $feedback = array();
        $feedback["classe"] = $this->getRelatorioTipo();
        $now = (new \DateTime())->format('Y-m-d H:i:s.u');
        $feedback["dthr"] = $now;
        $feedback["ok"] = $foiOk;
        $feedback["linhasLidas"] = $this->getTotalRegistrosNoArquivo();
        $feedback["linhasModificadas"] = $this->getItensModificadosPeloMySQL();
        $inconformidades = $this->getDescritorDeInconformidades()->getInconformidades();
        $feedback["inconformidades"] = $this->statusUpdateFormateInconformidades($inconformidades);
        $json = json_encode($feedback);

        $the_key = "RELESTOQUE01.ImportadorStatus";
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
            /** @var AppConfig $appConfig */
            $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', $the_key], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfig) {
                $appConfig = new AppConfig();
                $appConfig->chave = $the_key;
                $appConfig->appUUID = $_SERVER['CROSIERAPP_UUID'];
            }
            $appConfig->isJson = true;
            $appConfig->valor = $json;
            $this->appConfigEntityHandler->save($appConfig);
        } catch (\Exception $e) {
            $errMsg = 'Erro ao marcar app_config (' . $the_key . ')';
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }

    /**
     * @param string $obs
     * @param int $id
     * @param array $new
     * @param array|null $old
     * @throws \Doctrine\DBAL\Exception
     */
    private function salvarProdutoEntityChange(string $obs, int $id, array $new, ?array $old = null)
    {
        $strChanges = '';
        $conn = $this->doctrine->getConnection();

        $arrDiff = $old ? array_diff_assoc($old, $new) : $new;

        foreach ($arrDiff as $k => $diff) {
            $strChanges .= $k . ': ';
            if (isset($old)) {
                $strChanges .= 'de "' . $old[$k] . '" para ';
            }

            $strChanges .= '"' . $new[$k] . '"' . PHP_EOL;
        }

        $arr = [
            'entity_class' => Produto::class,
            'entity_id' => $id,
            'changing_user_id' => 1,
            'changed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'changes' => $strChanges,
            'obs' => 'RELESTOQUE01BUSINESS - ' . $obs,
        ];
        $conn->insert('cfg_entity_change', $arr);
    }


    protected function getPastaUpload(): string
    {
        return $_SERVER['PASTA_UPLOAD_RELESTOQUE01'];
    }


}
