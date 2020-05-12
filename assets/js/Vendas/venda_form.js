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

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

    let $item_produto = $('#item_produto');

    $.fn.select2.defaults.set("theme", "bootstrap");
    $.fn.select2.defaults.set("language", "pt-BR");

    $item_produto.select2({
        minimumInputLength: 4,
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        ajax: {
            delay: 750,
            url: Routing.generate('est_produto_findProdutosByIdOuNomeJson'),
            dataType: 'json',
            processResults: function (data) {
                let mapped = $.map(data.results, function (obj) {
                    return {
                        'id': obj.id,
                        'text': obj.nome,
                        'precoVenda': obj.jsonData.preco_tabela,
                        'unidade': obj.jsonData.unidade
                    };
                });
                return {
                    results: mapped
                };
            },
            cache: true
        }
    }).on('select2:select', function () {
        let o = $item_produto.select2('data')[0];
        $('#item_unidade').html(o.unidade);
        $('#item_precoVenda').val(o.precoVenda);
    });


});

