'use strict';

import $ from "jquery";

import 'daterangepicker';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'datatables';
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
import 'datatables/media/css/jquery.dataTables.css';

Routing.setRoutingData(routes);

import Moment from 'moment';

$(document).ready(function () {

    let $datatable = $('#vendasPorDia_table');

    let $filter_dtVenda = $('#filter_dtVenda');

    let $btnObterVendasECommerce = $('#btnObterVendasECommerce');

    let $form_vendasPorDia_list = $('#form_vendasPorDia_list');

    $filter_dtVenda.daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1901,
        maxYear: parseInt(Moment().format('YYYY'), 10),
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
    });


    $filter_dtVenda.on('apply.daterangepicker', function (ev, picker) {
        $form_vendasPorDia_list.submit();
    });


    // declaro antes para poder sobreescrever ali com o extent, no caso de querer mudar alguma coisa (ex.: movimentacaoRecorrentesList.js)
    let defaultParams = {
        paging: false,
        serverSide: false,
        stateSave: true,
        searching: false,
        language: {
            "url": "/build/static/datatables-Portuguese-Brasil.json"
        }
    };

    let datatable = $datatable.DataTable(defaultParams);

});