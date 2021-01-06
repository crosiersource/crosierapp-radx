'use strict';

/**
 * Script que é utilizado em telas de extratos.
 */

import Moment from 'moment';

import $ from "jquery";

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import 'daterangepicker';

$(document).ready(function () {

    let $categoria = $('#movimentacao_categoria');

    $categoria.select2('focus');

});
