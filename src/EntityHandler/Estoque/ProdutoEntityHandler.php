<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoImagem;
use App\Entity\Estoque\Subgrupo;
use App\Repository\Estoque\ProdutoImagemRepository;
use App\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class ProdutoEntityHandler extends EntityHandler
{

    private LoggerInterface $logger;

    private AppConfigEntityHandler $appConfigEntityHandler;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @required
     * @param AppConfigEntityHandler $appConfigEntityHandler
     */
    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }


    public function getEntityClass(): string
    {
        return Produto::class;
    }

    public function beforeSave(/** @var Produto $produto */ $produto)
    {
        if (!$produto->UUID) {
            $produto->UUID = StringUtils::guidv4();
        }

        if (!$produto->depto) {
            $produto->depto = $this->doctrine->getRepository(Depto::class)->find(1);
        }
        if (!$produto->grupo) {
            $produto->grupo = $this->doctrine->getRepository(Grupo::class)->find(1);
        }
        if (!$produto->subgrupo) {
            $produto->subgrupo = $this->doctrine->getRepository(Subgrupo::class)->find(1);
        }
        $produto->jsonData['depto_codigo'] = $produto->depto->codigo;
        $produto->jsonData['depto_nome'] = $produto->depto->nome;

        $produto->jsonData['grupo_codigo'] = $produto->grupo->codigo;
        $produto->jsonData['grupo_nome'] = $produto->grupo->nome;

        $produto->jsonData['subgrupo_codigo'] = $produto->subgrupo->codigo;
        $produto->jsonData['subgrupo_nome'] = $produto->subgrupo->nome;

        /** @var ProdutoImagemRepository $repoProdutoImagem */
        $repoProdutoImagem = $this->getDoctrine()->getRepository(ProdutoImagem::class);
        $imagens = $repoProdutoImagem->findBy(['produto' => $produto], ['ordem' => 'ASC']);

        $produto->jsonData['qtde_imagens'] = count($imagens);
        $produto->jsonData['imagem1'] = $imagens ? $imagens[0]->getImageName() : '';

        $this->calcPorcentPreench($produto);
    }

    /**
     * @param $produto
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function afterSave(/** @var Produto $produto */ $produto)
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadataAppConfig = $repoAppConfig->findConfigByChaveAndAppUUID('est_produto_json_metadata', $_SERVER['CROSIERAPP_UUID']);
        $jsonMetadata = json_decode($jsonMetadataAppConfig->getValor(), true);

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $mudou = null;
        foreach ($jsonMetadata['campos'] as $campo => $metadata) {
            if (isset($metadata['sugestoes'])) {
                $valoresNaBase = $conn->fetchAll('SELECT distinct(json_data->>"$.' . $campo . '") as val FROM est_produto WHERE json_data->>"$.' . $campo . '" NOT IN (\'\',\'null\') ORDER BY json_data->>"$.' . $campo . '"');
                $sugestoes = [];
                foreach ($valoresNaBase as $v) {
                    $valExploded = explode(',', $v['val']);
                    foreach ($valExploded as $val) {
                        if ($val && !in_array($val, $sugestoes)) {
                            $sugestoes[] = $val;
                        }
                    }
                }
                if (strcmp(json_encode($metadata['sugestoes']), json_encode($sugestoes)) !== 0) {
                    $mudou .= $campo . ',';
                    sort($sugestoes);
                    $jsonMetadata['campos'][$campo]['sugestoes'] = $sugestoes;
                }
            }
        }
        if ($mudou) {
            $jsonMetadataAppConfig->setValor(json_encode($jsonMetadata));
            $this->appConfigEntityHandler->save($jsonMetadataAppConfig);
        }
    }


    /**
     * @param Produto $produto
     */
    public function calcPorcentPreench(Produto $produto): void
    {
        $preench = 0;
        $camposFaltantes = '';

        $qtdeFotosMinima = $this->getQtdeFotosMinima();

        $pesoTotal = $qtdeFotosMinima;

        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->doctrine->getRepository(Produto::class);
        $jsonMetadata = json_decode($repoProduto->getJsonMetadata(), true);
        foreach ($jsonMetadata['campos'] as $nomeDoCampo => $metadata) {
            if (isset($metadata['soma_preench'])) {
                $pesoTotal += $metadata['soma_preench'];
                if ($produto->jsonData[$nomeDoCampo] ?? false) {
                    $preench += $metadata['soma_preench'];
                } else {
                    $camposFaltantes .= ($metadata['label'] ?? $nomeDoCampo) . ' (' . DecimalUtils::roundUp(bcdiv($metadata['soma_preench'] * 100, $pesoTotal, 2), 0) . '%)|';
                }
            }
        }

        for ($i = 1; $i <= $qtdeFotosMinima; $i++) {
            if ($produto->getImagens() && $produto->getImagens()->count() >= $i) {
                $preench += $pesos['imagem'] ?? 1;
            } else {
                $camposFaltantes .= 'Imagem ' . $i . ' (1%)|';
            }
        }

        $totalPreench = $preench / $pesoTotal;

        $produto->jsonData['porcent_preench'] = $totalPreench;
        $produto->jsonData['porcent_preench_campos_faltantes'] = $camposFaltantes;

    }

    /**
     * @return int
     */
    private function getQtdeFotosMinima(): int
    {
        $qtdeFotosMinima = 0;
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
            /** @var AppConfig $cfgQtdeFotosMinima */
            $cfgQtdeFotosMinima = $repoAppConfig->findOneBy(['appUUID' => $_SERVER['CROSIERAPP_UUID'], 'chave' => 'qtdeFotosMinima']);
            if ($cfgQtdeFotosMinima) {
                $qtdeFotosMinima = (int)$cfgQtdeFotosMinima->getValor();
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao pesquisar AppConfig para "qtdeFotosMinima"');
        }
        return $qtdeFotosMinima;
    }

}