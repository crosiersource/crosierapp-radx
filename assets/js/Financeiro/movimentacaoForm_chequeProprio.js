'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

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

    let $divCamposValores = $("#divCamposValores");
    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $divDtVVEP = $("#divDtVVEP");
    let $dtVenctoEfetiva = $("#movimentacao_dtVenctoEfetiva");
    let $dtPagto = $("#movimentacao_dtPagto");

    let $bancoAgenciaConta = $('#bancoAgenciaConta');

    let $divCamposDocumento = $('#divCamposDocumento');
    let $documentoBanco = $('#movimentacao_documentoBanco');
    let $documentoNum = $('#movimentacao_documentoNum');

    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');



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
            let route = $dtVenctoEfetiva.data('route') + '/?financeiro=true&dt=' + encodeURIComponent($dtVencto.val());
            $.getJSON(route)
                .done(
                    function (data) {
                        let dtF = Moment(data['diaUtil']).format('DD/MM/YYYY')
                        $("#movimentacao_dtVenctoEfetiva").val(dtF);
                    });
        }
    });

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


    $carteira.on('select2:select', function () {
        handleBancoAgenciaConta($(this).val());
    });

    function handleBancoAgenciaConta(carteiraId) {
        for (let c in carteirasInfos) {
            if (carteirasInfos[c].id == carteiraId) {
                let carteira = carteirasInfos[c]
                $bancoAgenciaConta.val(carteira.banco.nome + ' - Agência: ' + carteira.agencia + ' - Conta: ' + carteira.conta);

            }
        }
    }

    let carteirasInfos;

    function buildCarteirasInfos() {
        $.ajax({
                dataType: "json",
                async: false,
                data: {filters: [['cheque', 'EQ_BOOL', true]]},
                url: Routing.generate('api_carteira_findByFilters'),
                type: 'POST'
            }
        ).done(function (results) {
            carteirasInfos = results.results;
        });
    }


    // -----------------

    function initializeForm() {
        buildCarteirasInfos();

        if ($carteira.val()) {
            handleBancoAgenciaConta($carteira.val());
        }
    }


    initializeForm();

    $carteira.select2('focus');


})
;
