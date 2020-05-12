'use strict';

import $ from "jquery";

import 'daterangepicker';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


Routing.setRoutingData(routes);


$(document).ready(function () {

    let $filterDia = $('#filterDia');

    $filterDia.daterangepicker(
        {
            singleDatePicker: true,
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
            "alwaysShowCalendars": true
        },
        function (start, end, label) {
            window.location = Routing.generate('ven_venda_listPorDia') + '/' + start.format('YYYY-MM-DD');
        }
    );

});