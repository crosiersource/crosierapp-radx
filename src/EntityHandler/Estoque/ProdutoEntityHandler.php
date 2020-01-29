<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Atributo;
use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoAtributo;
use App\Repository\Estoque\AtributoRepository;
use App\Repository\Estoque\ProdutoAtributoRepository;
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
        if (!$produto->getUUID()) {
            $produto->setUUID(StringUtils::guidv4());
        }

        if (!$produto->getDepto()) {
            $produto->setDepto($this->doctrine->getRepository(Depto::class)->find(1));
        }
        if (!$produto->getGrupo()) {
            $produto->setGrupo($this->doctrine->getRepository(Grupo::class)->find(1));
        }
        if (!$produto->getSubgrupo()) {
            $produto->setSubgrupo($this->doctrine->getRepository(Subgrupo::class)->find(1));
        }
        $produto->setCodigoDepto($produto->getDepto()->getCodigo());
        $produto->setNomeDepto($produto->getDepto()->getNome());
        $produto->setCodigoGrupo($produto->getGrupo()->getCodigo());
        $produto->setNomeGrupo($produto->getGrupo()->getNome());
        $produto->setCodigoSubgrupo($produto->getSubgrupo()->getCodigo());
        $produto->setNomeSubgrupo($produto->getSubgrupo()->getNome());

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
            if ($produto->getTitulo()) {
                $preench += $pesos['titulo'];
            } else {
                $camposFaltantes .= 'Título (' . DecimalUtils::roundUp(bcdiv($pesos['titulo'] * 100, $pesoTotal, 2),0) . '%)|';
            }
        }

        if (isset($pesos['caracteristicas'])) {
            if ($produto->getCaracteristicas()) {
                $preench += $pesos['caracteristicas'];
            } else {
                $camposFaltantes .= 'Características (' . DecimalUtils::roundUp(bcdiv($pesos['caracteristicas'] * 100, $pesoTotal, 2),0) . '%)|';
            }
        }

        if (isset($pesos['ean'])) {
            if ($produto->getEan()) {
                $preench += $pesos['ean'];
            } else {
                $camposFaltantes .= 'EAN (' . DecimalUtils::roundUp(bcdiv($pesos['ean'] * 100, $pesoTotal, 2),0) . '%)|';
            }
        }

        if (isset($pesos['ncm'])) {
            if ($produto->getNcm()) {
                $preench += $pesos['ncm'];
            } else {
                $camposFaltantes .= 'NCM (' . DecimalUtils::roundUp(bcdiv($pesos['ncm'] * 100, $pesoTotal, 2),0) . '%)|';
            }
        }

        foreach ($produto->getAtributos() as $atributo) {
            if ($atributo->getSomaPreench()) {
                if ($atributo->getValor()) {
                    $preench += $atributo->getSomaPreench() ?? 0;
                } else {
                    $camposFaltantes .= $atributo->getAtributo()->getLabel() . ' (' . DecimalUtils::roundUp(bcdiv($atributo->getSomaPreench() * 100, $pesoTotal, 2),0) . '%)|';
                }
            }
        }

        $qtdeFotosMinima = $this->getQtdeFotosMinima();
        $qtdeImagensProduto = $produto->getImagens()->count();
        if ($produto->getImagens()) {
            for ($i = 1; $i <= $qtdeFotosMinima; $i++) {
                if ($qtdeImagensProduto < $i) {
                    $camposFaltantes .= 'Imagem ' . $i . ' (' . DecimalUtils::roundUp(bcdiv($pesos['imagem'] * 100, $pesoTotal, 2),0) . '%)|';
                } else {
                    $preench += $pesos['imagem'] ?? 1;
                }
            }

        }

        $totalPreench = $preench / $pesoTotal;

        $produto->setPorcentPreench($totalPreench);

        $this->salvarAtributoCamposFaltantes($produto, $camposFaltantes);
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

        foreach ($produto->getAtributos() as $atributo) {
            $pesoTotal += $atributo->getSomaPreench() ?? 0;
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

    /**
     * @param Produto $produto
     * @param string $camposFaltantes
     */
    private function salvarAtributoCamposFaltantes(Produto $produto, string $camposFaltantes): void
    {
        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->doctrine->getRepository(Atributo::class);
        /** @var Atributo $atrCamposFaltantes */
        $atrCamposFaltantes = $repoAtributo->findOneBy(['descricao' => 'SOMA PREENCH (CAMPOS FALTANTES)']);

        /** @var ProdutoAtributoRepository $repoProdutoAtributo */
        $repoProdutoAtributo = $this->doctrine->getRepository(ProdutoAtributo::class);
        /** @var ProdutoAtributo $produtoAtrCamposFaltantes */
        $produtoAtrCamposFaltantes = $repoProdutoAtributo->findOneBy(['atributo' => $atrCamposFaltantes, 'produto' => $produto]);

        if (!$produtoAtrCamposFaltantes) {
            $produtoAtrCamposFaltantes = new ProdutoAtributo();
            $produtoAtrCamposFaltantes->setProduto($produto);
            $produtoAtrCamposFaltantes->setAtributo($atrCamposFaltantes);
            $produtoAtrCamposFaltantes->setSomaPreench(0);
            $produtoAtrCamposFaltantes->setQuantif('N');
            $produtoAtrCamposFaltantes->setPrecif('N');
            $this->handleSavingEntityId($produtoAtrCamposFaltantes);
            $produto->getAtributos()->add($produtoAtrCamposFaltantes);

        }
        $produtoAtrCamposFaltantes->setValor($camposFaltantes);
    }
}