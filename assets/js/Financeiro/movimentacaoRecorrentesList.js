'use strict';

import Moment from 'moment';

import $ from "jquery";

import 'daterangepicker';

$(document).ready(function () {

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
                'Últimos 7 dias': [Moment().subtract(6, 'days'), Moment()],
                'Últimos 30 dias': [Moment().subtract(29, 'days'), Moment()],
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

    let $selTodasMovs = $('#selTodasMovs');

    $selTodasMovs.click(function () {
        $('.movSel').not(this).prop('checked', this.checked);
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


});