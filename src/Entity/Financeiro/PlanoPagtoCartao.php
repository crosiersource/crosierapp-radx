<?php

namespace App\Entity\Financeiro;

/**
 * Constantes para movimentacao.planoPagtoCartao.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
final class PlanoPagtoCartao
{

    const DEBITO = "Débito";

    const CREDITO_30DD = "Crédito 30 dias";

    const CREDITO_PARCELADO = "Crédito Parcelado";

    const N_A = "N/A";

    const ALL = array(
        PlanoPagtoCartao::DEBITO,
        PlanoPagtoCartao::CREDITO_30DD,
        PlanoPagtoCartao::CREDITO_PARCELADO,
        PlanoPagtoCartao::N_A
    );
}

