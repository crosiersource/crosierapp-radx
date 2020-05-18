'use strict';

import $ from "jquery";

import 'daterangepicker';

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'datatables';
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
import 'datatables/media/css/jquery.dataTables.css';

Routing.setRoutingData(routes);


$(document).ready(function () {

    let $datatable = $('#vendasPorDia_table');

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