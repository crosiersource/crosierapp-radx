<?php

namespace App\Entity\Fiscal;

/**
 * Segundo o Manual_Integracao_Contribuinte_4.01-NT2009.006 (3).pdf
 *
 * @author Carlos Eduardo Pauluk
 *
 */
final class IndicadorFormaPagto
{

    const VISTA = array(
        'codigo' => 0,
        'label' => 'A Vista'
    );

    const PRAZO = array(
        'codigo' => 1,
        'label' => 'A Prazo'
    );

    const OUTROS = array(
        'codigo' => 2,
        'label' => 'Outros'
    );
}