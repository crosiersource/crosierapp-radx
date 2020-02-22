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
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Psr\Log\LoggerInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class ProdutoEntityHandler extends EntityHandler
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
        $produto->jsonData['depto_codigo'] = $produto->depto->getCodigo();
        $produto->jsonData['depto_nome'] = $produto->depto->getNome();

        $produto->jsonData['grupo_codigo'] = $produto->grupo->getCodigo();
        $produto->jsonData['grupo_nome'] = $produto->grupo->getNome();

        $produto->jsonData['subgrupo_codigo'] = $produto->subgrupo->getCodigo();
        $produto->jsonData['subgrupo_nome'] = $produto->subgrupo->getNome();

        /** @var ProdutoImagemRepository $repoProdutoImagem */
        $repoProdutoImagem = $this->getDoctrine()->getRepository(ProdutoImagem::class);
        $imagens = $repoProdutoImagem->findBy(['produto' => $produto], ['ordem' => 'ASC']);

        $produto->jsonData['qtde_imagens'] = count($imagens);
        $produto->jsonData['imagem1'] = $imagens ? $imagens[0]->getImageName() : '';

        $this->calcPorcentPreench($produto);
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