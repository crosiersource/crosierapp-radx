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
     * @param $produto
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The directory name
     */
    public function directoryName(/** @var ProdutoImagem $produtoImagem */ $produtoImagem, PropertyMapping $mapping): string
    {
        return $produtoImagem->getProduto()->getDepto()->getId() . DIRECTORY_SEPARATOR .
            $produtoImagem->getProduto()->getGrupo()->getId() . DIRECTORY_SEPARATOR .
            $produtoImagem->getProduto()->getSubgrupo()->getId();
    }
}