<?php


namespace App\Utils\Estoque;


use App\Entity\Estoque\ProdutoImagem;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

/**
 * Class VichUploaderProdutoDirectoryNamer
 * @package App\Utils\Estoque
 * @author Carlos Eduardo Pauluk
 */
class VichUploaderProdutoDirectoryNamer implements DirectoryNamerInterface
{

    /**
     *
     *
     * @param $produtoImagem
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The directory name
     */
    public function directoryName(/** @var ProdutoImagem $produtoImagem */ $produtoImagem, PropertyMapping $mapping): string
    {
        return $produtoImagem->getProduto()->depto->getId() . DIRECTORY_SEPARATOR .
            $produtoImagem->getProduto()->grupo->getId() . DIRECTORY_SEPARATOR .
            $produtoImagem->getProduto()->subgrupo->getId();
    }
}