'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Moment from 'moment';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Routing.setRoutingData(routes);

/**
 * Este script é utilizado tanto para lançamentos de movimentações em carteiras comuns quanto em caixas.
 */
$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');
    let $carteira = $('#movimentacao_carteira');
    let $carteiraDestino = $('#movimentacao_carteiraDestino');
    let $categoria = $('#movimentacao_categoria');
    let $centroCusto = $('#movimentacao_centroCusto');
    let $tipoLancto = $("#movimentacao_tipoLancto");
    let $modo = $("#movimentacao_modo");

    let $divCamposValores = $("#divCamposValores");
    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $dtVenctoEfetiva = $("#movimentacao_dtVenctoEfetiva");
    let $dtPagto = $("#movimentacao_dtPagto");

    let $divCamposCheque = $('#divCamposCheque');
    let $chequeBanco = $('#movimentacao_chequeBanco');
    let $chequeAgencia = $('#movimentacao_chequeAgencia');
    let $chequeConta = $('#movimentacao_chequeConta');

    let $divCamposDocumento = $('#divCamposDocumento');
    let $documentoBanco = $('#movimentacao_documentoBanco');
    let $documentoNum = $('#movimentacao_documentoNum');

    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');


    /**
     * Constrói o campo sacado de acordo com as regras.
     */
    function buildSacado() {
        if ($sacado.data('route-url')) {
            $sacado.select2({
                minimumInputLength: 2,
                ajax: {
                    delay: 750,
                    url: function (params) {
                        let uri = $sacado.data('route-url') + params.term;
                        return uri;
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        let dataNew = $.map(data.results, function (obj) {
                            obj.text = obj['nome'];
                            return obj;
                        });
                        return {results: dataNew};
                    },
                    cache: true
                }
            });
        } else {
            $sacado.select2();
        }
    }

    /**
     * Constrói o campo cedente de acordo com as regras.
     */
    function buildCedente() {
        if ($cedente.data('route-url')) {
            $cedente.select2({
                minimumInputLength: 2,
                ajax: {
                    delay: 750,
                    url: function (params) {
                        let uri = $cedente.data('route-url') + params.term;
                        return uri;
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        let dataNew = $.map(data.results, function (obj) {
                            obj.text = obj['nome'];
                            return obj;
                        });
                        return {results: dataNew};
                    },
                    cache: true
                }
            });
        } else {
            $cedente.select2();
        }
    }

    function resValorTotal() {
        let valor =  Number($valor.val().replace('.','').replace(',','.'));
        let descontos = Number($descontos.val().replace('.','').replace(',','.'));
        let acrescimos = Number($acrescimos.val().replace('.','').replace(',','.'));
        let valorTotal = (valor - descontos + acrescimos).toFixed(2).replace('.',',');
        $valorTotal.val(valorTotal).maskMoney('mask');
    }



    $valor.on('blur', function () {
        resValorTotal()
    });
    $descontos.on('blur', function () {
        resValorTotal()
    });
    $acrescimos.on('blur', function () {
        resValorTotal()
    });

    $dtMoviment.on('focus', function () {
        if ($dtMoviment.val() === '') {
            $dtMoviment.val(Moment().format('DD/MM/YYYY'));
        }
    });

    let dtVenctoS = null;
    $dtVenctoEfetiva.on('focus', function () {
        if ($dtVencto.val() !== '' && (!dtVenctoS || dtVenctoS !== $dtVencto.val())) {
            dtVenctoS = $dtVencto.val();
            $.getJSON(Routing.generate('findProximoDiaUtilFinanceiro') + '/?dia=' + encodeURIComponent($dtVencto.val()))
                .done(
                    function (data) {
                        $("#movimentacao_dtVenctoEfetiva").val(data['dia']);
                    });
        }
    });


    // -----------------

    function initializeForm() {
        buildSacado();
        buildCedente();
    }


    initializeForm();

    $tipoLancto.select2('focus');
})
;
