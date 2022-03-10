<?php

namespace App\Business\Estoque;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\DeptoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\FornecedorEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\SubgrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;

/**
 * Rodar com:
 * php bin/console crosierappradx:processarUploads UploadProdutoCsv
 * ou para alterações:
 * php bin/console crosierappradx:processarUploads UploadProdutoCsv true
 *
 * @author Carlos Eduardo Pauluk
 */
class UploadProdutoCsv
{

    private SyslogBusiness $syslog;

    private ProdutoEntityHandler $produtoEntityHandler;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private string $pasta;

    private ?array $deptos = null;
    private ?array $grupos = null;
    private ?array $subgrupos = null;
    private ?array $fornecedores = null;
    private bool $camposPreparados = false;

    private ?array $estProdutos = null;

    private Unidade $unidade_UN;

    private DeptoEntityHandler $deptoEntityHandler;
    private GrupoEntityHandler $grupoEntityHandler;
    private SubgrupoEntityHandler $subgrupoEntityHandler;

    private FornecedorEntityHandler $fornecedorEntityHandler;

    private int $totalRegistros = 0;

    private int $inseridos = 0;
    private int $jaInseridos = 0;
    private int $alterados = 0;
    private int $naoAlterados = 0;

    private array $nomesCampos = [];

    private ?bool $atualizarExistentes = false;

    private ProdutoRepository $repoProduto;


    public function __construct(
        ProdutoEntityHandler    $produtoEntityHandler,
        DeptoEntityHandler      $deptoEntityHandler,
        GrupoEntityHandler      $grupoEntityHandler,
        SubgrupoEntityHandler   $subgrupoEntityHandler,
        FornecedorEntityHandler $fornecedorEntityHandler,
        AppConfigEntityHandler  $appConfigEntityHandler,
        SyslogBusiness          $syslog)
    {
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->syslog = $syslog->setApp('radx')
            ->setComponent('UploadProdutoCsv')
            ->setEcho(true);
        $this->pasta = $_SERVER['PASTA_UPLOADS'] . 'est_produtos_csv/';
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
        $this->fornecedorEntityHandler = $fornecedorEntityHandler;
        $this->repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }


    /**
     *
     */
    public function prepararCampos()
    {
        try {
            if ($this->camposPreparados) return;
            $this->deptos = null;
            $this->grupos = null;
            $this->subgrupos = null;
            $this->fornecedores = null;

            $this->unidade_UN = $this->produtoEntityHandler->getDoctrine()->getRepository(Unidade::class)->find(1);

            $rsDeptos = $this->produtoEntityHandler->getDoctrine()->getRepository(Depto::class)->findAll();
            /** @var Depto $depto */
            foreach ($rsDeptos as $depto) {
                $this->deptos[$depto->codigo] = $depto;
            }

            $rsGrupos = $this->produtoEntityHandler->getDoctrine()->getRepository(Grupo::class)->findAll();
            /** @var Grupo $grupo */
            foreach ($rsGrupos as $grupo) {
                $this->grupos[$grupo->codigo] = $grupo;
            }

            $rsSubgrupos = $this->produtoEntityHandler->getDoctrine()->getRepository(Subgrupo::class)->findAll();
            /** @var Subgrupo $subgrupo */
            foreach ($rsSubgrupos as $subgrupo) {
                $this->subgrupos[$subgrupo->codigo] = $subgrupo;
            }

            $rsFornecedores = $this->produtoEntityHandler->getDoctrine()->getRepository(Fornecedor::class)->findAll();
            /** @var Fornecedor $fornecedor */
            foreach ($rsFornecedores as $fornecedor) {
                $this->fornecedores[$fornecedor->codigo] = $fornecedor;
            }

            $this->camposPreparados = true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao prepararCampos()');
        }
    }

    /**
     *
     * @throws ViewException
     */
    public function processar(?bool $atualizarExistentes = false): void
    {
        $this->atualizarExistentes = $atualizarExistentes;
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

        $this->carregarEstProdutos();
        $this->prepararCampos();

        try {
            $pastaFila = $this->pasta . 'fila/';

            $zip = zip_open($pastaFila . $arquivo);
            $zip_entry = zip_read($zip);
            zip_entry_open($zip, $zip_entry, "r");
            $conteudo = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
            zip_close($zip);

            $linhas = explode(PHP_EOL, $conteudo);
            $this->totalRegistros = count($linhas) - 2;

            $this->nomesCampos = str_getcsv($linhas[0]);

            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();

            $batchSize = 250;
            $iBatch = 0;
            $this->produtoEntityHandler->getDoctrine()->getConnection()->getConfiguration()->setSQLLogger(null);

            $linhasBatch = [];

            for ($i = 1; $i <= $this->totalRegistros; $i++) {

                try {
                    $linha = $linhas[$i];
                    if (!trim($linha)) {
                        continue;
                    }
                    $linhasBatch[] = $linha;

                    $this->doProcessarLinha($linha, $i);

                } catch (\Throwable $e) {
                    $errMsg = 'processarArquivo() - Erro ao inserir a linha "' . $linha . '". Continuando...';
                    $errMsg = ExceptionUtils::treatException($e, $errMsg);
                    $this->syslog->err($errMsg, $e->getTraceAsString());

                    continue;
                }

                if ((++$iBatch % $batchSize) === 0) {
                    try {
                        $this->produtoEntityHandler->getDoctrine()->flush();
                        $this->produtoEntityHandler->getDoctrine()->clear();// Detaches all objects from Doctrine!
                        $this->camposPreparados = false;
                        $this->prepararCampos();
                        $linhasBatch = [];
                    } catch (\Throwable $e) {
                        $errMsg = 'processarArquivo() - Erro no flush do batch';
                        $this->syslog->err($errMsg, $e->getTraceAsString());
                        continue;
                    }
                }
            }

            try {
                $this->produtoEntityHandler->getDoctrine()->flush();
                $this->produtoEntityHandler->getDoctrine()->clear();
            } catch (\Throwable $e) {
                $errMsg = 'processarArquivo() - Erro no último flush do batch';
                $this->syslog->err($errMsg, $e->getTraceAsString());
            }

            $this->syslog->info('Total já inserido: ' . $this->jaInseridos);
            $this->syslog->info('Total de inserções: ' . $this->inseridos);
            $this->syslog->info('Total não alterados: ' . $this->naoAlterados);
            $this->syslog->info('Total de alterações: ' . $this->alterados);

            return $this->inseridos ?: $this->alterados ?: 0;
        } catch (\Throwable $e) {
            $errMsg = 'processarArquivo() - Erro';
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }

    public function processarLinha(string $linha): void
    {
        $this->carregarEstProdutos();
        $this->prepararCampos();
        $this->nomesCampos = [
            'erp_codigo',
            'erp_referencia',
            'nome',
            'grupo_codigo',
            'grupo_nome',
            'subgrupo_codigo',
            'subgrupo_nome',
            'unidade',
            'ncm',
            'preco_custo',
            'preco_tabela',
            'fornecedor_codigo',
            'fornecedor_nome',
            'cadastro',
            'alteracao',
            'alteracao_preco',
            'EAN',
        ];
        $this->doProcessarLinha($linha, 0);
    }

    private function doProcessarLinha(string $linha, int &$i): void
    {
        $atualizandoProduto = false;

        $campos = str_getcsv($linha);
        foreach ($campos as $k => $valor) {
            $campos[$this->nomesCampos[$k]] = trim($valor);
            unset($campos[$k]);
        }
        $campos['cadastro'] = $campos['cadastro'] ? DateTimeUtils::parseDateStr($campos['cadastro']) : null;
        $campos['alteracao'] = $campos['alteracao'] ? DateTimeUtils::parseDateStr($campos['alteracao']) : null;
        $campos['alteracao_preco'] = $campos['alteracao_preco'] ? DateTimeUtils::parseDateStr($campos['alteracao_preco']) : null;
        if ($this->estProdutos[$campos['erp_codigo']] ?? false) {
            if (!$this->atualizarExistentes) {
                $this->syslog->info($i . '/' . $this->totalRegistros . ') já existe registro para erp_codigo: ' . $campos['erp_codigo']);
                $this->jaInseridos++;
                return;
            } else {
                $produto = $this->repoProduto->findOneByCodigo($campos['erp_codigo']);
                if (!$produto) {
                    $produto = new Produto();
                } else {
                    $atualizandoProduto = true;
                }
            }
        } else {
            $produto = new Produto();
        }
        if ($atualizandoProduto) {
            // os campos que são permitidos alteração
            $alterado = false;
            if ($produto->referencia !== $campos['erp_codigo']) {
                $produto->referencia = $campos['erp_codigo'];
                $alterado = true;
            }
            if ($produto->ean !== ($campos['EAN'] ?? null)) {
                $produto->ean = $campos['EAN'] ?? null;
                $alterado = true;
            }
            if ($produto->marca !== $produto->fornecedor->nome) {
                $produto->marca = $produto->fornecedor->nome;
                $alterado = true;
            }
        } else {
            $alterado = true;
            // campos somente na inserção
            $agora = (new \DateTime())->format('Y-m-d H:i:s');

            $this->handleDeptoGrupoSubgrupo($produto, $campos);
            $this->handleFornecedor($produto, $campos);

            $produto->codigo = $campos['erp_codigo'];
            $produto->referencia = $campos['erp_codigo'];
            $produto->ean = $campos['EAN'] ?? null;
            $produto->marca = $produto->fornecedor->nome;
            $produto->nome = $campos['nome'];
            $produto->status = 'INATIVO';
            $produto->unidadePadrao = $this->unidade_UN;

            $produto->jsonData['preco_custo'] = (float)$campos['preco_custo'] ?? 0.0;
            $produto->jsonData['preco_tabela'] = (float)$campos['preco_tabela'] ?? null;
            $produto->jsonData['erp_codigo'] = $campos['erp_codigo'];
            $produto->jsonData['referencias_extras'] = $campos['erp_referencia'];
            $produto->jsonData['fornecedor_nome'] = $produto->fornecedor->nome;

            ksort($produto->jsonData);

            $this->estProdutos[$produto->codigo] = $produto->nome;
        }

        if ($alterado) {
            $produto = $this->produtoEntityHandler->save($produto, false);

            if ($atualizandoProduto) {
                $this->alterados++;
            } else {
                $this->inseridos++;
            }
            $this->syslog->info($i . '/' . $this->totalRegistros . ') produto ' . ($atualizandoProduto ? 'alterado' : 'inserido') . ' (' . $produto->codigo . ')');
        } else {
            $this->syslog->info($i . '/' . $this->totalRegistros . ') produto não alterado (' . $produto->codigo . ')');
            $this->naoAlterados++;
        }
    }

    private function handleDeptoGrupoSubgrupo(Produto $produto, array $campos)
    {
        if (!($campos['depto_codigo'] ?? false)) {
            $depto = $this->deptos['00'];
        } else if (!isset($this->deptos[$campos['depto_codigo']])) {
            $depto = new Depto();
            $depto->codigo = $campos['depto'];
            $depto->nome = $campos['depto_nome'];
            $depto = $this->deptoEntityHandler->save($depto);
            $this->deptos[$depto->codigo] = $depto;
        } else {
            $depto = $this->deptos[$campos['depto_codigo']];
        }
        $produto->depto = $depto;


        if (!($campos['grupo_codigo'] ?? false)) {
            $grupo = $this->grupos['00'];
        } else if (!isset($this->grupos[$campos['grupo_codigo']])) {
            $grupo = new Grupo();
            $grupo->depto = $produto->depto;
            $grupo->codigo = $campos['grupo_codigo'];
            $grupo->nome = $campos['grupo_nome'];
            $grupo = $this->grupoEntityHandler->save($grupo);
            $this->grupos[$grupo->codigo] = $grupo;
        } else {
            $grupo = $this->grupos[$campos['grupo_codigo']];
        }
        $produto->grupo = $grupo;

        if (!($campos['subgrupo_codigo'] ?? false)) {
            $subgrupo = $this->subgrupos['00'];
        } elseif (!isset($this->subgrupos[$campos['subgrupo_codigo']])) {
            $subgrupo = new Subgrupo();
            $subgrupo->grupo = $produto->grupo;
            $subgrupo->codigo = $campos['subgrupo_codigo'];
            $subgrupo->nome = $campos['subgrupo_nome'];
            $subgrupo = $this->subgrupoEntityHandler->save($subgrupo);
            $this->subgrupos[$subgrupo->codigo] = $subgrupo;
        } else {
            $subgrupo = $this->subgrupos[$campos['subgrupo_codigo']];
        }
        $produto->subgrupo = $subgrupo;
    }


    private function handleFornecedor(Produto $produto, array $campos)
    {
        if (!isset($this->fornecedores[$campos['fornecedor_codigo']])) {
            $fornecedor = new Fornecedor();
            $fornecedor->codigo = $campos['fornecedor_codigo'];
            $fornecedor->nome = $campos['fornecedor_nome'];
            $fornecedor = $this->fornecedorEntityHandler->save($fornecedor);
            $this->fornecedores[$fornecedor->codigo] = $fornecedor;
        } else {
            $fornecedor = $this->fornecedores[$campos['fornecedor_codigo']];
        }
        $produto->fornecedor = $fornecedor;
        $produto->jsonData['marca'] = $fornecedor->nome;
    }

    private function carregarEstProdutos()
    {
        $rs = $this->produtoEntityHandler->getDoctrine()->getConnection()->fetchAllAssociative('SELECT id, json_data->>"$.erp_codigo" as erp_codigo, nome FROM est_produto');
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
            $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'UploadProdutoCsv.dthrAtualizacao'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfig) {
                $appConfig = new AppConfig();
                $appConfig->chave = 'UploadProdutoCsv.dthrAtualizacao';
                $appConfig->appUUID = $_SERVER['CROSIERAPP_UUID'];
            }
            $appConfig->valor = (new \DateTime())->format('Y-m-d H:i:s.u');
            $this->appConfigEntityHandler->save($appConfig);
        } catch (\Exception $e) {
            $errMsg = 'Erro ao marcar app_config (uploadProdutoCsv.dthrAtualizacao)';
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }


    protected function getPastaUpload(): string
    {
        return $_SERVER['PASTA_UPLOAD_RELESTOQUE01'];
    }


}
