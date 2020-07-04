'use strict';

import $ from "jquery";

import Numeral from 'numeral';

import 'numeral/locales/pt-br.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

    let $item_produto = $('#item_produto');
    let $item_id = $('#item_id');

    let $qtde = $('#item_qtde');
    let $valorUnit = $('#item_precoVenda');
    let $desconto = $('#item_desconto');
    let $valorTotal = $('#item_valorTotal');


    function resValorTotal() {
        let qtde = $qtde.maskMoney('unmasked')[0];
        let valorUnit = $valorUnit.maskMoney('unmasked')[0];

        let subTotal = (qtde * valorUnit);
        // $subTotal.val(subTotal.toFixed(2).replace('.',',')).maskMoney('mask');

        let desconto = $desconto.maskMoney('unmasked')[0];
        let valorTotal = (subTotal - desconto).toFixed(2).replace('.', ',');
        $valorTotal.val(valorTotal).maskMoney('mask');
    }

    $qtde.blur(function () {
        resValorTotal();
    });

    $valorUnit.blur(function () {
        resValorTotal();
    });

    $desconto.blur(function () {
        resValorTotal();
    });

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
            url: Routing.generate('ven_venda_findProdutosByIdOuNomeJson'),
            dataType: 'json',
            processResults: function (data) {
                let mapped = $.map(data.results, function (obj) {
                    return {
                        'id': obj.id,
                        'text': obj.nome,
                        'precoVenda': obj.preco_venda,
                        'unidade': obj.unidade
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
        let precoVenda = parseFloat(o.precoVenda).toFixed(2);
        $valorUnit.val(precoVenda.replace('.', ',')).maskMoney('mask');
    });


    if ($item_produto.hasClass('focusOnReady')) {
        $item_produto.select2('focus');
    }


    $('.btnEditProduto').click(function () {
        let dados = $(this).data();

        $item_id.val(dados.itemId);

        $('#item_unidade').html(dados.itemUnidade);

        let qtde = parseFloat(dados.itemQtde).toFixed(3);
        $qtde.val(qtde.replace('.', ',')).maskMoney('mask');

        let precoVenda = parseFloat(dados.itemPrecoVenda).toFixed(2);
        $valorUnit.val(precoVenda.replace('.', ',')).maskMoney('mask');

        let desconto = parseFloat(dados.itemDesconto).toFixed(2);
        $desconto.val(desconto.replace('.', ',')).maskMoney('mask');

        resValorTotal();

        let data = {
            id: dados.itemProdutoId,
            text: dados.itemDescricao
        };

        // Set the value, creating a new option if necessary
        if (!$item_produto.find("option[value='" + data.id + "']").length) {
            let newOption = new Option(data.text, data.id, false, false);
            $item_produto.append(newOption);
        }
        $item_produto.val(data.id).trigger('change');

        $item_produto.select2('focus');

    });

});

