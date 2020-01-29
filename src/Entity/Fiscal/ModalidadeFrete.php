<?php

namespace App\Entity\Fiscal;

/**
 * Segundo o Manual_Integracao_Contribuinte_4.01-NT2009.006 (3).pdf
 *
 * @author Carlos Eduardo Pauluk
 *
 */
final class ModalidadeFrete
{
    public const SEM_FRETE = [
        'codigo' => 9,
        'key' => 'SEM_FRETE',
        'label' => 'Sem frete'
    ];

    public const EMITENTE = [
        'codigo' => 0,
        'key' => 'EMITENTE',
        'label' => 'Por conta do emitente'
    ];

    public const DESTINATARIO = [
        'codigo' => 1,
        'key' => 'DESTINATARIO',
        'label' => 'Por conta do destinatário/remetente'
    ];

    public const TERCEIROS = [
        'codigo' => 2,
        'key' => 'TERCEIROS',
        'label' => 'Por conta de terceiros'
    ];

    public const ALL = [
        'SEM_FRETE' => ModalidadeFrete::SEM_FRETE,
        'EMITENTE' => ModalidadeFrete::EMITENTE,
        'DESTINATARIO' => ModalidadeFrete::DESTINATARIO,
        'TERCEIROS' => ModalidadeFrete::TERCEIROS
    ];

    /**
     * @return array
     */
    public static function getChoices(): array
    {
        $arr = array();
        foreach (self::ALL as $e) {
            $arr[$e['label']] = $e['codigo'];
        }
        return $arr;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        $all = self::ALL;
        return $all[$key];
    }

    /**
     * @param $codigo
     * @return mixed|null
     */
    public static function getByCodigo($codigo)
    {
        foreach (self::ALL as $e) {
            if ($e['codigo'] === (int)$codigo) {
                return $e;
            }
        }
        return null;
    }
}