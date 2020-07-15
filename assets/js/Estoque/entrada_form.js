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

import 'jquery-maskmoney/dist/jquery.maskMoney.js';

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

    let $item_produto = $('#item_produto');
    let $item_unidade = $('#item_unidade');
    let $item_id = $('#item_id');
    let $item_precoVenda = $('#item_precoVenda');

    let $qtde = $('#item_qtde');


    $.fn.select2.defaults.set("theme", "bootstrap");
    $.fn.select2.defaults.set("language", "pt-BR");

    $item_produto.select2({
        minimumInputLength: 3,
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        ajax: {
            delay: 750,
            url: Routing.generate('est_produto_findProdutosByNomeOuFinalCodigo'),
            dataType: 'json',
            cache: true
        }
    }).on('select2:select', function () {
        let o = $item_produto.select2('data')[0];
        let precoVenda = parseFloat(o.preco_prazo).toFixed(2);
        $item_precoVenda.val(precoVenda.replace('.', ','));
        CrosierMasks.maskDecs();
    });


    if ($item_produto.hasClass('focusOnReady')) {
        $item_produto.select2('focus');
    }


    $item_unidade.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        tags: true,
        data: $item_unidade.data('options')
    }).on('select2:select', function () {
        let o = $item_unidade.select2('data')[0];
        $qtde.removeClass();
        $qtde.addClass('form-control').addClass('crsr-dec' + o.casas_decimais);
        $('#item_unidade_append_label').html(o.text);
        CrosierMasks.maskDecs();
    });

});



