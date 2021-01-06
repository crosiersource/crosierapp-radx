'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Moment from 'moment';

import 'jquery-maskmoney/dist/jquery.maskMoney.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");


Routing.setRoutingData(routes);


$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');

    let $documentoNum = $('#movimentacao_documentoNum');

    let $modo = $('#movimentacao_modo');

    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');

    let $categoria = $('#movimentacao_categoria');

    let $carteira = $('#movimentacao_carteira');

    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $dtVenctoEfetiva = $("#movimentacao_dtVenctoEfetiva");
    let $dtPagto = $("#movimentacao_dtPagto");

    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    let $formPageTitle = $("#formPageTitle");

    let filiais;

    $.getJSON(Routing.generate('fin_movimentacao_filiais'), function (data) {
        filiais = data;
        handleSacadoCedente();
    });

    $categoria.select2({
            placeholder: "Selecione...",
            width: '100%',
            matcher: function (params, data) {
                // If there are no search terms, return all of the data
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Do not display the item if there is no 'text' property
                if (typeof data.text === 'undefined') {
                    return null;
                }

                // `params.term` should be the term that is used for searching
                // `data.text` is the text that is displayed for the data object
                if (data.text.replace(/\./g, '').toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    // You can return modified objects from here
                    // This includes matching the `children` how you want in nested data sets
                    return data;
                }

                // Return `null` if the term should not be displayed
                return null;
            }
        }
    ).on('select2:select', function () {
        handleSacadoCedente();
    });


    /**
     * Se a categoria for de "1 - ENTRADAS", o cedente é uma das filiais.
     * Se a categoria for de "2 - SAÍDAS", uma das filiais é o sacado.
     */
    function handleSacadoCedente() {

        let categoria = $categoria.select2('data')[0];

        let $campoComFiliais;
        let $campoComBusca;

        if (!categoria?.id) return;
        if (categoria.element.dataset.codigoSuper === '1') {
            $campoComFiliais = $cedente;
            $campoComBusca = $sacado;
            $formPageTitle.html('Movimentação a Receber');
        } else if (categoria.element.dataset.codigoSuper === '2') {
            $campoComFiliais = $sacado;
            $campoComBusca = $cedente;
            $formPageTitle.html('Movimentação a Pagar');
        } else {
            alert('Erro ao configurar sacado/cedente (' + categoria.element.dataset.codigoSuper + ')');
        }

        $campoComFiliais.select2({
            placeholder: "Selecione...",
            width: '100%',
            data: filiais
        });

        $campoComBusca.select2({
            placeholder: "Selecione...",
            width: '100%',
            dropdownAutoWidth: true,
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                delay: 750,
                url: function (params) {
                    return Routing.generate('fin_movimentacao_findSacadoOuCedente') + '?term=' + params.term;
                },
                dataType: 'json'
            }
        });

        $sacado.prop('disabled', false);
        $cedente.prop('disabled', false);
    }

    function resValorTotal() {
        let valor = Number($valor.val().replace('.', '').replace(',', '.'));
        let descontos = Number($descontos.val().replace('.', '').replace(',', '.'));
        let acrescimos = Number($acrescimos.val().replace('.', '').replace(',', '.'));
        let valorTotal = (valor - descontos + acrescimos).toFixed(2).replace('.', ',');
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

    // Para poder verificar se mudou a dtVencto antes de chamar
    let dtVenctoSaved = null;
    $dtVenctoEfetiva.on('focus', function () {
        if (dtVenctoSaved !== $dtVencto.val()) {
            let route = '/base/diaUtil/findDiaUtil/?financeiro=true&dt=' + encodeURIComponent($dtVencto.val());
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


    $categoria.select2('focus');

    $carteira.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true
    });



});
