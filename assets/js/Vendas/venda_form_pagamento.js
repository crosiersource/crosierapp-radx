'use strict';

import $ from "jquery";

import Numeral from 'numeral';

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

import hotkeys from 'hotkeys-js';


$(document).ready(function () {


    let $planoPagto = $('#pagto_planoPagto');
    let $planoPagto_carteira = $('#pagto_carteira');
    let $planoPagto_carteira_destino = $('#pagto_carteira_destino');
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


        $planoPagto_carteira_destino.prop('disabled', true);
        $planoPagto_carteira_destino.empty().trigger("change");
        if (o?.carteirasDestino) {

            $.map(o.carteirasDestino, function (obj) {
                obj.text = String(obj['codigo']).padStart(2, '0') + ' - ' + obj['descricao'];
                return obj;
            });

            $planoPagto_carteira_destino.select2({
                width: '100%',
                dropdownAutoWidth: true,
                placeholder: '...',
                allowClear: true,
                tags: true,
                data: o.carteirasDestino
            });

            $planoPagto_carteira_destino.prop('disabled', false);
        }

        $planoPagto_numParcelas.val('');

        $planoPagto_numParcelas.prop('disabled', o.json_data?.aceita_parcelas === false);

    });


    $planoPagto.select2('focus');

    let k = hotkeys.noConflict();
    k('ctrl+1', function (event, handler) {
        event.preventDefault();
        $('#aDados')[0].click();
    });
    k('ctrl+2', function (event, handler) {
        event.preventDefault();
        $('#aItens')[0].click();
    });
    k('ctrl+3', function (event, handler) {
        event.preventDefault();
        $('#aPagto')[0].click();
    });
    k('ctrl+4', function (event, handler) {
        event.preventDefault();
        $('#aResumo')[0].click();
    });


});
