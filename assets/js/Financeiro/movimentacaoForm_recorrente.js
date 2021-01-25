/* eslint-disable */

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'jquery-maskmoney/dist/jquery.maskMoney.js';

import Moment from 'moment';


Routing.setRoutingData(routes);

/**
 * Este script é utilizado tanto para lançamentos de movimentações em carteiras comuns quanto em caixas.
 */
$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');
    let $carteira = $('#movimentacao_carteira');
    let $tipoLancto = $('#movimentacao_tipoLancto');
    let $categoria = $('#movimentacao_categoria');
    let $centroCusto = $('#movimentacao_centroCusto');
    let $modo = $("#movimentacao_modo");

    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');

    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $dtVenctoEfetiva = $("#movimentacao_dtVenctoEfetiva");
    let $dtPagto = $("#movimentacao_dtPagto");


    let $divCamposRecorrencia = $('#divCamposRecorrencia');
    let $recorrente = $('#movimentacao_recorrente');
    let $divCamposCartao = $('#divCamposCartao');
    let $bandeiraCartao = $('#movimentacao_bandeiraCartao');
    let $operadoraCartao = $('#movimentacao_operadoraCartao');

    // Para poder verificar se mudou a dtVencto antes de chamar
    let dtVenctoSaved = null;
    $dtVenctoEfetiva.on('focus', function () {
        if (dtVenctoSaved != $dtVencto.val()) {
            let route = $dtVenctoEfetiva.data('route') + '/?financeiro=true&dt=' + encodeURIComponent($dtVencto.val());
            dtVenctoSaved = $dtVencto.val();
            $.ajax(
                route,
                {
                    crossDomain: true,
                    dataType: "json",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    xhrFields: {
                        withCredentials: true
                    }
                }
            ).done(
                function (data) {
                    let dtF = Moment(data['diaUtil']).format('DD/MM/YYYY')
                    $dtVenctoEfetiva.val(dtF);
                }
            ).fail(function (jqXHR, textStatus, errorThrown) {
                if (jqXHR) {
                    console.dir(jqXHR);
                }
                if (textStatus) {
                    console.dir(textStatus);
                }
            });
        }
    });

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


    // -----------------

    function initializeForm() {
        checkExibirCamposRecorrente();
    }


    initializeForm();

    $modo.focus();


})
;
