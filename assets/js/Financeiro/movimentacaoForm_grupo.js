'use strict';

/**
 * Script que é utilizado em telas de extratos.
 */

import Moment from 'moment';

import $ from "jquery";


import 'daterangepicker';

$(document).ready(function () {

    let $categoria = $('#movimentacao_categoria');

    $categoria.select2('focus');

});