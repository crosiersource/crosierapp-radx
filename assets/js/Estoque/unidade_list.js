/* eslint-disable */

import $ from "jquery";


import 'datatables';
import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
import 'datatables/media/css/jquery.dataTables.css';

$(document).ready(function () {


    let $datatable = $('#unidade_list');

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