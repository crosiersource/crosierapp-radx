<?php

namespace App\Business\Financeiro;

use App\Entity\Financeiro\Grupo;
use App\Entity\Financeiro\GrupoItem;
use App\EntityHandler\Financeiro\GrupoItemEntityHandler;

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
            $novo->setPai($pai);
            $novo->setFechado(false);
            $novo->setValorInformado(0.0);

            if ($prox) {
                /** @var GrupoItem $ultimo */
                $ultimo = $this->grupoItemEntityHandler->getDoctrine()->getRepository(GrupoItem::class)->findOneBy(['pai' => $pai], ['dtVencto' => 'DESC']);

                if (!$ultimo) {
                    $proxDtVencto = new \DateTime();
                    $proxDtVencto->setDate($proxDtVencto->format('Y'), $proxDtVencto->format('m'), $pai->getDiaVencto());
                    $novo->setCarteiraPagante($pai->getCarteiraPagantePadrao());
                } else {
                    $novo->setAnterior($ultimo);
                    $proxDtVencto = clone $ultimo->getDtVencto();
                    $proxDtVencto = $proxDtVencto->setDate($proxDtVencto->format('Y'), (int)$proxDtVencto->format('m') + 1, $proxDtVencto->format('d'));
                    $novo->setCarteiraPagante($ultimo->getCarteiraPagante());
                }
                $novo->setDtVencto($proxDtVencto);
                $novo->getDtVencto()->setTime(0, 0, 0, 0);

                $novo->setDescricao($pai->getDescricao() . ' - ' . $proxDtVencto->format('d/m/Y'));

                $this->grupoItemEntityHandler->save($novo);

                if ($ultimo) {
                    $ultimo->setProximo($novo);
                    $this->grupoItemEntityHandler->save($ultimo);
                }
            } else {
                /** @var GrupoItem $primeiro */
                $primeiro = $this->grupoItemEntityHandler->getDoctrine()->getRepository(GrupoItem::class)->findOneBy(['pai' => $pai], ['dtVencto' => 'ASC']);

                if (!$primeiro) {
                    $proxDtVencto = new \DateTime();
                    $proxDtVencto->setDate($proxDtVencto->format('Y'), $proxDtVencto->format('m'), $pai->getDiaVencto());
                    $novo->setCarteiraPagante($pai->getCarteiraPagantePadrao());
                } else {
                    $novo->setProximo($primeiro);
                    $proxDtVencto = clone $primeiro->getDtVencto();
                    $proxDtVencto = $proxDtVencto->setDate($proxDtVencto->format('Y'), (int)$proxDtVencto->format('m') - 1, $proxDtVencto->format('d'));
                    $novo->setCarteiraPagante($primeiro->getCarteiraPagante());
                }
                $novo->setDtVencto($proxDtVencto);
                $novo->getDtVencto()->setTime(0, 0, 0, 0);

                $novo->setDescricao($pai->getDescricao() . ' - ' . $proxDtVencto->format('d/m/Y'));

                $this->grupoItemEntityHandler->save($novo);

                if ($primeiro) {
                    $primeiro->setAnterior($novo);
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