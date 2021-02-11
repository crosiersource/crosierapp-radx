<?php

namespace App\Business\Financeiro;


use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Financeiro\GrupoItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\GrupoItemRepository;

/**
 * Class GrupoBusiness
 * @package App\Business\Financeiro
 */
class GrupoBusiness
{
    private GrupoItemEntityHandler $grupoItemEntityHandler;

    public function __construct(GrupoItemEntityHandler $grupoItemEntityHandler)
    {
        $this->grupoItemEntityHandler = $grupoItemEntityHandler;
    }


    /**
     * Gera um novo próximo item de grupo de movimentação.
     *
     * @param Grupo $pai
     * @param bool $prox
     * @return GrupoItem
     * @throws \Exception
     */
    public function gerarNovo(Grupo $pai, bool $prox = true): ?GrupoItem
    {
        try {
            $this->grupoItemEntityHandler->getDoctrine()->beginTransaction();


            $novo = new GrupoItem();
            $novo->pai = $pai;
            $novo->fechado = false;
            $novo->valorInformado = 0.0;

            /** @var GrupoItemRepository $repoGrupoItem */
            $repoGrupoItem = $this->grupoItemEntityHandler->getDoctrine()->getRepository(GrupoItem::class);

            if ($prox) {

                /** @var GrupoItem $ultimo */
                $ultimo = $repoGrupoItem->findOneBy(['pai' => $pai], ['dtVencto' => 'ASC']);

                if (!$ultimo) {
                    $proxDtVencto = new \DateTime();
                    $proxDtVencto->setDate($proxDtVencto->format('Y'), $proxDtVencto->format('m'), $pai->diaVencto);
                    $novo->carteiraPagante = $pai->carteiraPagantePadrao;
                } else {
                    $novo->anterior = $ultimo;
                    $proxDtVencto = clone $ultimo->dtVencto;
                    $proxDtVencto = $proxDtVencto->setDate($proxDtVencto->format('Y'), (int)$proxDtVencto->format('m') + 1, $proxDtVencto->format('d'));
                    $novo->carteiraPagante = $ultimo->carteiraPagante;
                }
                $novo->dtVencto = $proxDtVencto;
                $novo->dtVencto->setTime(0, 0);

                $novo->descricao = $pai->descricao . ' - ' . $proxDtVencto->format('d/m/Y');

                $this->grupoItemEntityHandler->save($novo);

                if ($ultimo) {
                    $ultimo->proximo = $novo;
                    $this->grupoItemEntityHandler->save($ultimo);
                }
            } else {
                /** @var GrupoItem $primeiro */
                $primeiro = $repoGrupoItem->findOneBy(['pai' => $pai], ['dtVencto' => 'ASC']);

                if (!$primeiro) {
                    $proxDtVencto = new \DateTime();
                    $proxDtVencto->setDate($proxDtVencto->format('Y'), $proxDtVencto->format('m'), $pai->diaVencto);
                    $novo->carteiraPagante = $pai->carteiraPagantePadrao;
                } else {
                    $novo->proximo = $primeiro;
                    $proxDtVencto = clone $primeiro->dtVencto;
                    $proxDtVencto = $proxDtVencto->setDate($proxDtVencto->format('Y'), (int)$proxDtVencto->format('m') - 1, $proxDtVencto->format('d'));
                    $novo->carteiraPagante = $primeiro->carteiraPagante;
                }
                $novo->dtVencto = $proxDtVencto;
                $novo->dtVencto->setTime(0, 0);

                $novo->descricao = $pai->descricao . ' - ' . $proxDtVencto->format('d/m/Y');

                $this->grupoItemEntityHandler->save($novo);

                if ($primeiro) {
                    $primeiro->anterior = $novo;
                    $this->grupoItemEntityHandler->save($primeiro);
                }
            }

            $this->grupoItemEntityHandler->getDoctrine()->commit();
            return $novo;
        } catch (\Exception $e) {
            $this->grupoItemEntityHandler->getDoctrine()->rollback();
            $erro = "Erro ao gerar novo item";
            throw new \Exception($erro, null, $e);
        }


    }

}