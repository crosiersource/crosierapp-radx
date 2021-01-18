/* eslint-disable */

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
    let $descricao = $('#movimentacao_descricao');
    let $tipoLancto = $('#movimentacao_tipoLancto');
    let $carteira = $('#movimentacao_carteira');
    let $categoria = $('#movimentacao_categoria');
    let $modo = $("#movimentacao_modo");
    let $valor = $("#movimentacao_valor");
    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $bandeiraCartao = $('#movimentacao_bandeiraCartao');
    let $operadoraCartao = $('#movimentacao_operadoraCartao');
    let $qtdeParcelasCartao = $('#movimentacao_qtdeParcelasCartao');

    let $sacado = $('#movimentacao_sacado');


    $dtMoviment.on('focus', function () {
        if ($dtMoviment.val() === '') {
            $dtMoviment.val(Moment().format('DD/MM/YYYY'));
        }
    });

    $modo = $modo.select2({
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true,
    });

    $bandeiraCartao = $bandeiraCartao.select2({
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true,
    });

    $modo.on('select2:select', function () {
        handleFormRules()
    });

    $descricao.on('focus', function () {
        if ($descricao.val() === '') {
            if ($bandeiraCartao.select2('data')[0].text !== '') {
                $descricao.val($bandeiraCartao.select2('data')[0].text);
            }
        }
    });


    $sacado.select2({
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


    function handleFormRules() {

    }

    // -----------------

    function initializeForm() {
        handleFormRules();
    }


    initializeForm();


});
