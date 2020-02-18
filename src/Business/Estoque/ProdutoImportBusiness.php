<?php

namespace App\Business\Estoque;


use App\Entity\Estoque\Fornecedor;
use App\Entity\Estoque\Grupo;
use App\EntityHandler\Estoque\FornecedorEntityHandler;
use App\EntityHandler\Estoque\GrupoEntityHandler;
use App\Repository\Estoque\FornecedorRepository;
use App\Repository\Estoque\GrupoRepository;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoImportBusiness
{

    public const PASTA_UPLOAD = 'PASTA_UPLOAD_ESTOQUE';

    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var LoggerInterface */
    private $logger;

    /** @var AppConfigEntityHandler */
    private $appConfigEntityHandler;

    /** @var FornecedorEntityHandler */
    private $fornecedorEntityHandler;

    /** @var GrupoEntityHandler */
    private $grupoEntityHandler;

    /**
     * @param EntityManagerInterface $doctrine
     * @param LoggerInterface $logger
     * @param AppConfigEntityHandler $appConfigEntityHandler
     * @param FornecedorEntityHandler $fornecedorEntityHandler
     * @param GrupoEntityHandler $grupoEntityHandler
     */
    public function __construct(EntityManagerInterface $doctrine,
                                LoggerInterface $logger,
                                AppConfigEntityHandler $appConfigEntityHandler,
                                FornecedorEntityHandler $fornecedorEntityHandler,
                                GrupoEntityHandler $grupoEntityHandler)
    {
        $this->doctrine = $doctrine;
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->fornecedorEntityHandler = $fornecedorEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function processarArquivosNaFila(): void
    {
        $pastaFila = $_SERVER[self::PASTA_UPLOAD] . 'fila/';
        $files = scandir($pastaFila, 0);
        foreach ($files as $file) {
            if (!in_array($file, array('.', '..'))) {

                try {
                    $this->processarArquivo($file);
                    $this->marcarDtHrAtualizacao();
                    $this->logger->info('Arquivo processado com sucesso.');
                    rename($pastaFila . $file, $_SERVER[self::PASTA_UPLOAD] . 'ok/' . $file);
                    $this->logger->info('Arquivo movido para pasta "ok".');
                } catch (\Exception $e) {
                    rename($pastaFila . $file, $_SERVER[self::PASTA_UPLOAD] . 'falha/' . $file);
                    $this->logger->info('Arquivo movido para pasta "falha".');
                }
            }
        }
    }

    /**
     * @param string $arquivo
     * @return int
     * @throws ViewException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function processarArquivo(string $arquivo): int
    {

        try {
            $pastaFila = $_SERVER[self::PASTA_UPLOAD] . 'fila/';
            $conteudo = file_get_contents($pastaFila . $arquivo);
            $linhas = explode(PHP_EOL, $conteudo);
            $linhasNaoSalvas = [];

            $mapaDePara = [];
            $l = 0;
            foreach ($linhas as $linha) {
                $linhasNaoSalvas[] = $linha;
                $l++;
                if ($linha === '--------- MAPADEPARA ----------') {
                    continue;
                }
                if ($linha === '---------- FIM ----------') {
                    break;
                }
                $dePara = explode(':', $linha);
                $mapaDePara[] = $dePara;
            }

            $l++;

            $s = 0;
            $e = 0;

            $cache = new FilesystemAdapter('crosierappvendest.produtoImportBusiness.cache');


            /** @var Connection $conn */
            $conn = $this->doctrine->getConnection();


            /** @var AtributoRepository $repoAtributo */
            $repoAtributo = $this->doctrine->getRepository(Atributo::class);
            $atributosAll = $repoAtributo->findAll();
            $atributos = []; // cache
            foreach ($atributosAll as $atributo) {
                /** @var Atributo $atributo */
                $atributos[$atributo->getUUID()] = $atributo->getId();
            }


            $linha = null;

            $conn->beginTransaction();

            $totalLinhas = count($linhas);

            for (; $l < $totalLinhas; $l++) {

                try {

                    $linha = $linhas[$l];
                    if (!$linha) continue;
                    $campos = explode("|", $linha);

                    $produto = $conn->fetchAssoc('SELECT * FROM est_produto WHERE codigo_from = :codigo_from', ['codigo_from' => trim($campos[2])]);
                    if (!$produto) {
                        // Campo obrigatórios
                        $produto = [
                            'uuid' => StringUtils::guidv4(),
                            'inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'updated' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'user_inserted_id' => 1,
                            'user_updated_id' => 1,
                            'estabelecimento_id' => 1,
                        ];
                    }
                    $produtoAtributos = [];

                    foreach ($campos as $key => $valor) {
                        if (isset($mapaDePara[$key]) && ($mapaDePara[$key] ?: false) && ($mapaDePara[$key][1] ?: false)) {
                            $deParas = explode("+", $mapaDePara[$key][1]);
                            if (!$deParas) continue;
                            foreach ($deParas as $dePara) {
                                $conf = explode("->", $dePara);


                                if ($conf[0] === 'PRODUTO') {
                                    if (isset($conf[2])) {
                                        $this->tratarEspeciais($produto, $conf[1], $valor);
                                    } else {
                                        $cmps = explode('+', $conf[1]);
                                        foreach ($cmps as $cmp) {
                                            $produto[$cmp] = utf8_encode(trim($valor));
                                        }
                                    }
                                } elseif ($conf[0] === 'ATRIBUTO') {
                                    $uuid = strtolower($conf[1]);

                                    $atributo = $cache->get('atributo_' . str_replace('-', '', $uuid), function (ItemInterface $item) use ($repoAtributo, $uuid) {
                                        return $repoAtributo->findOneBy(['UUID' => $uuid]);
                                    });

                                    if (!$atributo) {
                                        throw new \RuntimeException('Atributo não encontrada para UUID: "' . $uuid);
                                    }
                                    $subatributo = null;
                                    if ($atributo->getTipo() === 'LISTA') {

                                        $subatributoId = $cache->get('subatributoId_' . $uuid . '_' . $valor, function (ItemInterface $item) use ($repoAtributo, $uuid, $valor) {
                                            /** @var Atributo $subatributo */
                                            $subatributo = $repoAtributo->findOneBy(['paiUUID' => $uuid, 'config' => $valor]);
                                            return $subatributo->getId();
                                        });

                                    } else {
                                        $subatributoId = null; // para limpar da iteração passada
                                    }
                                    $produtoAtributo = null;
                                    if ($produto['id'] ?? false) {
                                        $produtoAtributo = $conn->fetchAssoc('SELECT * FROM est_produto_atributo WHERE produto_id = :produto_id AND atributo_id = :atributo_id',
                                            [
                                                'produto_id' => $produto['id'],
                                                'atributo_id' => $atributo->getId(),
                                            ]
                                        );
                                    }
                                    if (!$produtoAtributo) {
                                        $produtoAtributo = [
                                            'atributo_id' => $atributo->getId(),
                                            'inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
                                            'updated' => (new \DateTime())->format('Y-m-d H:i:s'),
                                            'user_inserted_id' => 1,
                                            'user_updated_id' => 1,
                                            'estabelecimento_id' => 1,
                                            'quantif' => 'N',
                                            'precif' => 'N',
                                        ];
                                    }
                                    $produtoAtributo['valor'] = $subatributoId ?? utf8_encode(trim($valor));
                                    $produtoAtributos[] = $produtoAtributo;
                                }

                            }

                        }
                    }

                    $produto['depto_id'] = 1;
                    $produto['depto_codigo'] = 'N/A';
                    $produto['depto_nome'] = 'NÃO INFORMADO';
                    $produto['grupo_id'] = 1;
                    $produto['grupo_codigo'] = 'N/A';
                    $produto['grupo_nome'] = 'NÃO INFORMADO';
                    $produto['subgrupo_id'] = 1;
                    $produto['subgrupo_codigo'] = 'N/A';
                    $produto['subgrupo_nome'] = 'NÃO INFORMADO';
                    $produto['status'] = 'INATIVO';
                    $produto['composicao'] = 'N';

                    if ($produto['id'] ?? null) {
                        $produto['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
                        $conn->update('est_produto', $produto, ['id' => $produto['id']]);
                    } else {
                        $conn->insert('est_produto', $produto);
                        $produto['id'] = $conn->lastInsertId();
                    }

                    foreach ($produtoAtributos as $atributo) {
                        $atributo['produto_id'] = $produto['id'];
                        $atributo['soma_preench'] = 'S';
                        if ($atributo['id'] ?? null) {
                            $atributo['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
                            $conn->update('est_produto_atributo', $atributo, ['id' => $atributo['id']]);
                        } else {
                            $conn->insert('est_produto_atributo', $atributo);
                        }
                    }

                    $s++;
                    $this->logger->info($s . ' registros inseridos');
                } catch (\Exception $e) {
                    $linhasNaoSalvas[] = $linha;
                    $this->logger->info($e . ' registros com erro');
                    $this->logger->error('processarArquivo() - erro ');
                    $this->logger->error($e->getMessage());
                }
            }

            $conn->commit();
            $this->logger->info('commit');
        } catch (\Exception $e) {
            try {
                $conn->rollBack();
            } catch (ConnectionException $e) {
                throw new ViewException($e->getMessage());
            }
            throw new \RuntimeException($e->getMessage());
        }
        $this->logger->info('------------------------------------------------------');
        $this->logger->info($s . ' registros inseridos');
        $this->logger->info($e . ' registros com erro');

        $arquivoNaoSalvos = $pastaFila = $_SERVER[self::PASTA_UPLOAD] . 'falha/NAO_SALVOS_' . $arquivo;
        file_put_contents($arquivoNaoSalvos, implode(PHP_EOL, $linhasNaoSalvas));

        return $s;
    }


    /**
     * Trata os relacionamentos com a entidade produto.
     *
     * @param array $produto
     * @param string $entidade
     * @param string $valor
     * @return void
     * @throws ViewException
     */
    private function tratarEspeciais(array &$produto, string $entidade, string $valor): void
    {
        $cache = new FilesystemAdapter('crosierappvendest.produtoImportBusiness.cache');

        if ($entidade === 'grupo') {
            $grupoId = $cache->get('grupo_' . $valor, function (ItemInterface $item) use ($valor) {
                /** @var AppConfigRepository $repoAppConfig */
                /** @var GrupoRepository $repoGrupo */
                $repoGrupo = $this->doctrine->getRepository(Grupo::class);
                /** @var Grupo $grupo */
                $grupo = $repoGrupo->findOneBy(['codigo' => $valor]);
                if (!$grupo) {
                    $grupo = new Grupo();
                    $grupo->setCodigo($valor);
                    $grupo->setNome('NÃO ENCONTRADO NA IMPORTAÇÃO');
                    $this->grupoEntityHandler->save($grupo, false);
                }
                return $grupo->getId();
            });

            $produto['grupo_id'] = $grupoId;
            return;
        }

        if ($entidade === 'fornecedor') {
            /** @var Fornecedor $fornecedor */
            $fornecedor = $cache->get('fornecedor_' . $valor, function (ItemInterface $item) use ($valor) {
                /** @var FornecedorRepository $repoFornecedor */
                $repoFornecedor = $this->doctrine->getRepository(Fornecedor::class);
                /** @var Fornecedor $fornecedor */
                $fornecedor = $repoFornecedor->findOneBy(['codigo' => $valor]);
                if (!$fornecedor) {
                    $fornecedor = new Fornecedor();
                    $fornecedor->setCodigo($valor);
                    $fornecedor->setNome('NÃO ENCONTRADO NA IMPORTAÇÃO');
                    $fornecedor = $this->fornecedorEntityHandler->save($fornecedor);
                    // throw new \RuntimeException('Fornecedor não encontrado para "' . $valor . '"');
                }
                return $fornecedor;
            });

            $produto['fornecedor_id'] = $fornecedor->getId();
            $produto['fornecedor_documento'] = $fornecedor->getDocumento();
            $produto['fornecedor_nome'] = $fornecedor->getNome();
            return;
        }

    }

    /**
     * @throws ViewException
     */
    private function marcarDtHrAtualizacao(): void
    {
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
            /** @var AppConfig $appConfig */
            $appConfig = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'estoque.dthrAtualizacao'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfig) {
                $appConfig = new AppConfig();
                $appConfig->setChave('estoque.dthrAtualizacao');
                $appConfig->setAppUUID($_SERVER['CROSIERAPP_UUID']);
            }
            $appConfig->setValor((new \DateTime())->format('Y-m-d H:i:s.u'));
            $this->appConfigEntityHandler->save($appConfig);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao marcar app_config (estoque.dthrAtualizacao)');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao marcar dt/hr atualização');
        }
    }

}
