'use strict';

/**
 * Script que é utilizado em telas de extratos.
 */

import Moment from 'moment';

import $ from "jquery";


import 'daterangepicker';

$(document).ready(function () {

    // console.log(Moment().format('DD'));
    // console.log(Moment().date(1).format('YYYY-MM-DD'));
    // console.log(Moment().add(1, 'month').format('YYYY-MM-DD'));
    // console.log(Moment().add(1, 'month').date(1).format('YYYY-MM-DD'));

    let $filterCarteira = $('#filterCarteira');
    let $formPesquisar = $('#formPesquisar');
    let $btnHoje = $('#btnHoje');
    let $btnAnterior = $('#btnAnterior');
    let $btnProximo = $('#btnProximo');


    let $filterDts = $('#filterDts').daterangepicker(
        {
            opens: 'left',
            autoApply: true,
            locale: {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Aplicar",
                "cancelLabel": "Cancelar",
                "fromLabel": "De",
                "toLabel": "Até",
                "customRangeLabel": "Custom",
                "daysOfWeek": [
                    "Dom",
                    "Seg",
                    "Ter",
                    "Qua",
                    "Qui",
                    "Sex",
                    "Sáb"
                ],
                "monthNames": [
                    "Janeiro",
                    "Fevereiro",
                    "Março",
                    "Abril",
                    "Maio",
                    "Junho",
                    "Julho",
                    "Agosto",
                    "Setembro",
                    "Outubro",
                    "Novembro",
                    "Dezembro"
                ],
                "firstDay": 0
            },
            ranges: {
                'Hoje': [Moment(), Moment()],
                'Ontem': [Moment().subtract(1, 'days'), Moment().subtract(1, 'days')],
                'Esta quinzena': Moment().format('DD') < 16 ? [Moment().date(1), Moment().date(15)] : [Moment().date(16), Moment().endOf('month')],
                'Próxima quinzena': Moment().format('DD') < 16 ? [Moment().date(1), Moment().date(15)] : [Moment().add(1, 'month').date(1), Moment().add(1, 'month').date(15)],
                'Quinzena anterior': Moment().format('DD') < 16 ? [Moment().subtract(1, 'month').date(16), Moment().subtract(1, 'month').endOf('month')] : [Moment().date(1), Moment().date(15)],
                'Este mês': [Moment().startOf('month'), Moment().endOf('month')],
                'Mês passado': [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true
        },
        function (start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            $formPesquisar.submit();
        }
    ).on('apply.daterangepicker', function (ev, picker) {
        $formPesquisar.submit();
    });

    $btnHoje.on('click', function () {
        $filterDts.val(Moment().format('DD/MM/YYYY') + ' - ' + Moment().format('DD/MM/YYYY'));
        $formPesquisar.submit();
    });

    $btnAnterior.on('click', function () {
        let dtIni = Moment($(this).data('ante-periodoi')).format('DD/MM/YYYY');
        let dtFim = Moment($(this).data('ante-periodof')).format('DD/MM/YYYY');
        $filterDts.val(dtIni + ' - ' + dtFim);
        $formPesquisar.submit();
    });

    $btnProximo.on('click', function () {
        let dtIni = Moment($(this).data('prox-periodoi')).format('DD/MM/YYYY');
        let dtFim = Moment($(this).data('prox-periodof')).format('DD/MM/YYYY');
        $filterDts.val(dtIni + ' - ' + dtFim);
        $formPesquisar.submit();
    });

    let $filterCarteiraSelectAll = $('#filterCarteiraSelectAll');

    $filterCarteiraSelectAll.click(function(){
        $('.filterCarteira').not(this).prop('checked', this.checked);
    });

    let $btnCarteirasPesquisar = $('#btnCarteirasPesquisar');

    // Como não está no mesmo form, adiciona os elementos ao formPesquisar e submit
    $btnCarteirasPesquisar.on('click', function () {
        $formPesquisar.append($('.filterCarteira'));
        $formPesquisar.submit();
    });


    let $btnImprimir = $('#btnImprimir');

    $btnImprimir.on('click', function () {
        let url = $(this).data('url');
        let form = $('<form target="_blank">').attr("method", "post").attr(
            "action", url);
        form.append($('.filterCarteira').clone());
        form.append($('#filterDts').clone());
        // simuland o clique
        $(form).appendTo('body').submit();
    });

});