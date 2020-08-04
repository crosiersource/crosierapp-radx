'use strict';

import $ from "jquery";

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import Moment from "moment";


class MovimentacaoCaixaList {

    static consolidarDebitos() {
        let $btnConsolidarDebitos = $('#btnConsolidarDebitos');
        let $dtMoviment = $('#filterDtMoviment');
        let $filterCarteira = $('#filterCarteira');
        window.location.href = Routing.generate('movimentacao_caixa_consolidarDebitos') + '?carteira=' + $filterCarteira.val() + '&dtMoviment=' + $dtMoviment.val();
    }


}


$(document).ready(function () {

    let $filterCarteira = $('#filterCarteira');
    let $formPesquisar = $('#formPesquisar');

    let $dtMoviment = $('#filterDtMoviment');
    let $btnHoje = $('#btnHoje');
    let $btnAnterior = $('#btnAnterior');
    let $btnProximo = $('#btnProximo');

    $dtMoviment.on('apply.daterangepicker', function(ev, picker) {
        $formPesquisar.submit();
    });

    $btnHoje.on('click', function () {
        console.log('hoje');
        let dt = Moment().format('DD/MM/YYYY');
        $dtMoviment.val(dt);
        $formPesquisar.submit();
    });

    $btnAnterior.on('click', function () {
        $dtMoviment.val($(this).data('ante-data'));
        $formPesquisar.submit();
    });

    $btnProximo.on('click', function () {
        $dtMoviment.val($(this).data('prox-data'));
        $formPesquisar.submit();
    });

    $filterCarteira.on('select2:select', function (e) {
        $formPesquisar.submit();
    });

});

window.MovimentacaoCaixaList = MovimentacaoCaixaList;


