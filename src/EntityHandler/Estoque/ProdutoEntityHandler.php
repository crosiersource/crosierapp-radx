<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoImagem;
use App\Entity\Estoque\Subgrupo;
use App\Repository\Estoque\ProdutoImagemRepository;
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
        $produto->jsonData['imagem1'] = $imagens[0]->getImageName();

        $this->calcPorcentPreench($produto);
    }

    /**
     * @param Produto $produto
     */
    public function calcPorcentPreench(Produto $produto): void
    {
        $preench = 0;
        $camposFaltantes = '';

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);

        $pesosCampos = $repoAppConfig->findOneBy(
            [
                'appUUID' => '440e429c-b711-4411-87ed-d95f7281cd43',
                'chave' => 'porcentPreenchPesosCampos'
            ]
        )->getValor();
        //titulo=10;caracteristicas=10;ean=1;ncm=1

        $pesosKeyVal = explode(';', $pesosCampos);
        $pesos = [];
        foreach ($pesosKeyVal as $pesoKeyVal) {
            $keyVal = explode('=', $pesoKeyVal);
            $pesos[$keyVal[0]] = $keyVal[1];
        }

        $pesoTotal = $this->calcPesoTotal($produto);

        if (isset($pesos['titulo'])) {
            if ($produto->jsonData['titulo'] ?? false) {
                $preench += $pesos['titulo'];
            } else {
                $camposFaltantes .= 'Título (' . DecimalUtils::roundUp(bcdiv($pesos['titulo'] * 100, $pesoTotal, 2), 0) . '%)|';
            }
        }

        if (isset($pesos['caracteristicas'])) {
            if ($produto->jsonData['caracteristicas'] ?? false) {
                $preench += $pesos['caracteristicas'];
            } else {
                $camposFaltantes .= 'Características (' . DecimalUtils::roundUp(bcdiv($pesos['caracteristicas'] * 100, $pesoTotal, 2), 0) . '%)|';
            }
        }

        if (isset($pesos['ean'])) {
            if ($produto->jsonData['ean'] ?? false) {
                $preench += $pesos['ean'];
            } else {
                $camposFaltantes .= 'EAN (' . DecimalUtils::roundUp(bcdiv($pesos['ean'] * 100, $pesoTotal, 2), 0) . '%)|';
            }
        }

        if (isset($pesos['ncm'])) {
            if ($produto->jsonData['ncm'] ?? false) {
                $preench += $pesos['ncm'];
            } else {
                $camposFaltantes .= 'NCM (' . DecimalUtils::roundUp(bcdiv($pesos['ncm'] * 100, $pesoTotal, 2), 0) . '%)|';
            }
        }

        $qtdeFotosMinima = $this->getQtdeFotosMinima();
        $qtdeImagensProduto = $produto->getImagens()->count();
        if ($produto->getImagens()) {
            for ($i = 1; $i <= $qtdeFotosMinima; $i++) {
                if ($qtdeImagensProduto < $i) {
                    $camposFaltantes .= 'Imagem ' . $i . ' (' . DecimalUtils::roundUp(bcdiv($pesos['imagem'] * 100, $pesoTotal, 2), 0) . '%)|';
                } else {
                    $preench += $pesos['imagem'] ?? 1;
                }
            }

        }

        $totalPreench = $preench / $pesoTotal;

        $produto->jsonData['porcent_preench'] = $totalPreench;

    }

    /**
     * @param Produto $produto
     * @return int
     */
    private function calcPesoTotal(Produto $produto): int
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $pesosCampos = $repoAppConfig->findOneBy(
            [
                'appUUID' => '440e429c-b711-4411-87ed-d95f7281cd43',
                'chave' => 'porcentPreenchPesosCampos'
            ]
        )->getValor();

        $pesoTotal = 0;
        $pesosKeyVal = explode(';', $pesosCampos);
        $pesos = [];
        foreach ($pesosKeyVal as $pesoKeyVal) {
            $keyVal = explode('=', $pesoKeyVal);
            if ($keyVal[0] !== 'imagem') {
                $pesoTotal += (int)$keyVal[1];
            }
            $pesos[$keyVal[0]] = $keyVal[1];
        }


        $qtdeFotosMinima = $this->getQtdeFotosMinima();

        $pesoTotal += $qtdeFotosMinima * ($pesos['imagem'] ?? 1);

        return $pesoTotal;
    }

    /**
     * @return int
     */
    private function getQtdeFotosMinima(): int
    {
        $qtdeFotosMinima = 3;
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