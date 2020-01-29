<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Atributo;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoAtributo;
use App\Repository\Estoque\AtributoRepository;
use App\Repository\Estoque\ProdutoAtributoRepository;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoAtributoEntityHandler extends EntityHandler
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
        return ProdutoAtributo::class;
    }

    public function beforeSave(/** @var ProdutoAtributo $produtoAtributo */ $produtoAtributo)
    {
        if (!$produtoAtributo->getOrdem()) {
            /** @var ProdutoAtributo $ultimo */
            $ultimo = $produtoAtributo->getProduto()->getAtributos()->last();
            $produtoAtributo->setOrdem($ultimo ? $ultimo->getOrdem() + 1 : 1);
        }
    }

    /**
     * @param Produto $produto
     * @param array $produtoAtributoArr
     * @throws ViewException
     */
    public function salvarAtributo(Produto $produto, array $produtoAtributoArr): void
    {

        if ($produtoAtributoArr['id']) {
            /** @var ProdutoAtributoRepository $repoAtributo */
            $repoProdutoAtributo = $this->getDoctrine()->getRepository(ProdutoAtributo::class);
            /** @var ProdutoAtributo $produtoAtributo */
            $produtoAtributo = $repoProdutoAtributo->find($produtoAtributoArr['id']);
            $produtoAtributo->setPrecif($produtoAtributoArr['precif'] ?? 'N');
            $produtoAtributo->setQuantif($produtoAtributoArr['quantif'] ?? 'N');
            $produtoAtributo->setSomaPreench($produtoAtributoArr['somaPreench'] ?? 'N');
            $produtoAtributo->setAba($produtoAtributoArr['aba'] ?: '');
            $produtoAtributo->setGrupo($produtoAtributoArr['grupo'] ?: '');
            $this->save($produtoAtributo);
            return;
        }

        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->getDoctrine()->getRepository(Atributo::class);
        /** @var Atributo $atributo */
        $atributo = $repoAtributo->find($produtoAtributoArr['atributo']);

        $existente = null;
        if ($produto->getAtributos()) {
            foreach ($produto->getAtributos() as $atributoProduto) {
                if ($atributo->getId() === $atributoProduto->getAtributo()->getId()) {
                    $existente = $atributoProduto;
                    break;
                }
            }
        }
        if (!$existente) {
            $produtoAtributo = new ProdutoAtributo();
            $produtoAtributo->setProduto($produto);
            $produtoAtributo->setAtributo($atributo);
        } else {
            $produtoAtributo = $existente;
        }
        $produtoAtributo->setPrecif($produtoAtributoArr['precif'] ?? 'N');
        $produtoAtributo->setQuantif($produtoAtributoArr['quantif'] ?? 'N');
        $produtoAtributo->setSomaPreench($produtoAtributoArr['somaPreench'] ?? 'N');
        $produtoAtributo->setAba($produtoAtributoArr['aba'] ?: '');
        $produtoAtributo->setGrupo($produtoAtributoArr['grupo'] ?: '');

        $this->save($produtoAtributo);
    }

    /**
     * @param array $produtoAtributos
     * @param Produto $produto
     * @throws ViewException
     */
    public function saveProdutoAtributos(array $produtoAtributos, Produto $produto): void
    {
        foreach ($produtoAtributos as $atributoId => $valor) {
            $contem = false;
            if ($produto->getAtributos()) {
                foreach ($produto->getAtributos() as $produtoAtributo) {
                    if ($produtoAtributo->getAtributo()->getId() === $atributoId) {
                        $produtoAtributo->setValor($produtoAtributo->paraValor($valor));
                        $this->save($produtoAtributo);
                        $contem = true;
                        break;
                    }
                }
            }
            if (!$contem) {
                throw new ViewException('Atributo não encontrada: ' . $atributoId);
            }
        }
    }


    /**
     * @param array $ids
     * @return array
     * @throws ViewException
     */
    public function salvarOrdens(array $ids): array
    {
        /** @var ProdutoAtributoRepository $repoAtributo */
        $repoProdutoAtributo = $this->getDoctrine()->getRepository(ProdutoAtributo::class);
        $i = 1;
        $ordens = [];
        foreach ($ids as $id) {
            if (!$id) continue;
            /** @var ProdutoAtributo $produtoAtributo */
            $produtoAtributo = $repoProdutoAtributo->find($id);
            $ordens[$id] = $i;
            $produtoAtributo->setOrdem($i++);
            $this->save($produtoAtributo);
        }
        return $ordens;
    }

    /**
     * @param Produto $produto
     * @throws ViewException
     */
    public function reordenar(Produto $produto)
    {
        $i = 1;
        foreach ($produto->getAtributos() as $produtoAtributo) {
            $produtoAtributo->setOrdem($i++);
            $this->save($produtoAtributo);
        }
    }

    /**
     * @param Produto $produtoFrom
     * @param int $produtoToId
     * @throws \Doctrine\DBAL\DBALException
     */
    public function colarConfigs(Produto $produtoFrom, int $produtoToId): void
    {
        /** @var Connection $conn */
        $conn = $this->doctrine->getConnection();

        foreach ($produtoFrom->getAtributos() as $produtoAtributoFrom) {

            $produtoAtributoTo = null;
            $produtoToAtributos = $conn->fetchAll('SELECT * FROM est_produto_atributo WHERE produto_id = :produto_id', ['produto_id' => $produtoToId]);

            if ($produtoToAtributos) {
                foreach ($produtoToAtributos as $prodAtribTo_) {
                    if ((int)$produtoAtributoFrom->getAtributo()->getId() === (int)$prodAtribTo_['atributo_id']) {
                        $produtoAtributoTo = $prodAtribTo_;
                        break;
                    }
                }
            }

            if (!$produtoAtributoTo) {
                $produtoAtributoTo = [];
                $produtoAtributoTo['produto_id'] = $produtoToId;
                $produtoAtributoTo['atributo_id'] = $produtoAtributoFrom->getAtributo()->getId();
            } else if ($produtoAtributoTo['precif'] === $produtoAtributoFrom->getPrecif() &&
                $produtoAtributoTo['quantif'] === $produtoAtributoFrom->getQuantif() &&
                $produtoAtributoTo['soma_preench'] === $produtoAtributoFrom->getSomaPreench() &&
                $produtoAtributoTo['aba'] === $produtoAtributoFrom->getAba() &&
                $produtoAtributoTo['grupo'] === $produtoAtributoFrom->getGrupo() &&
                (int)$produtoAtributoTo['ordem'] === (int)$produtoAtributoFrom->getOrdem()) {
                continue;
            }

            $produtoAtributoTo['precif'] = $produtoAtributoFrom->getPrecif();
            $produtoAtributoTo['quantif'] = $produtoAtributoFrom->getQuantif();
            $produtoAtributoTo['soma_preench'] = $produtoAtributoFrom->getSomaPreench();
            $produtoAtributoTo['aba'] = $produtoAtributoFrom->getAba();
            $produtoAtributoTo['grupo'] = $produtoAtributoFrom->getGrupo();
            $produtoAtributoTo['ordem'] = $produtoAtributoFrom->getOrdem();

            if ($produtoAtributoTo['id'] ?? null) {
                $produtoAtributoTo['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
                $conn->update('est_produto_atributo', $produtoAtributoTo, ['id' => $produtoAtributoTo['id']]);
            } else {
                $produtoAtributoTo['inserted'] = (new \DateTime())->format('Y-m-d H:i:s');
                $produtoAtributoTo['updated'] = (new \DateTime())->format('Y-m-d H:i:s');
                $produtoAtributoTo['user_inserted_id'] = 1;
                $produtoAtributoTo['user_updated_id'] = 1;
                $produtoAtributoTo['estabelecimento_id'] = 1;
                $conn->insert('est_produto_atributo', $produtoAtributoTo);
            }
        }
    }

    /**
     * @param Produto $produtoFrom
     * @throws \Doctrine\DBAL\DBALException
     */
    public function copiarParaTodos(Produto $produtoFrom): void
    {
        $this->logger->info('Iniciando "copiarParaTodos()');

        /** @var Connection $conn */
        $conn = $this->doctrine->getConnection();
        $conn->getConfiguration()->setSQLLogger(null);

        $conn->beginTransaction();
        $produtosIds = $conn->fetchAll('SELECT id FROM est_produto WHERE id != :produtoFromId', ['produtoFromId' => $produtoFrom->getId()]);
        $qtdeProdutos = count($produtosIds);

        $i=0;
        foreach ($produtosIds as $produtoId) {
            $this->logger->info('Copiando para ' . $produtoId['id']);
            $this->colarConfigs($produtoFrom, $produtoId['id']);
            $this->logger->info('OK! (' . ++$i . '/' . $qtdeProdutos . ')');
        }
        $this->logger->info('Commitando...');
        $conn->commit();
        $this->logger->info('OK!!!!!');
    }
}
