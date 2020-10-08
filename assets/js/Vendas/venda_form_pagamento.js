'use strict';

import $ from "jquery";

import Numeral from 'numeral';

import 'print-js';

import 'numeral/locales/pt-br.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {


    let $planoPagto = $('#pagto_planoPagto');
    let $planoPagto_carteira = $('#pagto_carteira');
    let $planoPagto_numParcelas = $('#pagto_numParcelas');

    $planoPagto.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        tags: true,
        data: $planoPagto.data('options')
    }).on('select2:select', function () {
        let o = $planoPagto.select2('data')[0];

        $.map(o.carteiras, function (obj) {
            obj.text = obj['descricao'];
            return obj;
        });

        $planoPagto_carteira.empty().trigger("change");

        $planoPagto_carteira.select2({
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: '...',
            allowClear: true,
            tags: true,
            data: o.carteiras
        });

        $planoPagto_carteira.prop('disabled', false);

        $planoPagto_numParcelas.val('');

        $planoPagto_numParcelas.prop('disabled', o.json_data?.aceita_parcelas === false);

    });

    $planoPagto.select2('focus');


});

