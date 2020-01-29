<?php

namespace App\Repository\Estoque;


use App\Entity\Estoque\Produto;
use App\Entity\Estoque\ProdutoAtributo;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ProdutoRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Produto::class;
    }

    /**
     * @param Produto $produto
     * @return array
     */
    public function getAbas(Produto $produto): array
    {
        if (!$produto->getId()) {
            return ['Produto' => []];
        }
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getEntityManager()->getRepository(AppConfig::class);
        $ordemAbasConfig = $repoAppConfig->findOneBy(
            [
                'appUUID' => '440e429c-b711-4411-87ed-d95f7281cd43',
                'chave' => 'produto_form.ordem_abas'
            ]
        )->getValor();

        $ordemAbas = explode(',', $ordemAbasConfig);
        $abas = [];
        foreach ($ordemAbas as $label) {
            $abas[$label] = [];
        }


        $qry = $this->getEntityManager()->createQuery('SELECT e FROM App\Entity\Estoque\ProdutoAtributo e WHERE e.produto = :produto ORDER BY e.ordem');
        $qry->setParameter('produto', $produto);
        $atributos = $qry->getResult();

        /** @var ProdutoAtributo $atributo */
        foreach ($atributos as $atributo) {
            $labelAba = $atributo->getAba() ?: 'Complementos';
            $labelGrupo = $atributo->getGrupo() ?: 'Nenhum';
            $abas[$labelAba][$labelGrupo][] = $atributo;
        }

        if ($produto->getComposicao() === 'S') {
            $abas['Composição'] = [];
            unset($abas['Fiscal'], $abas['Preços']);
        }

        return $abas;
    }


}
