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
import 'jquery-maskmoney/dist/jquery.maskMoney.js';
import toastrr from "toastr";

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

    let $entradaId = $('#entrada_id');
    let $produto = $('#item_produto');
    let $produto_helpText = $('#item_produto_helpText');
    let $unidade = $('#item_unidade');

    let $qtde = $('#item_qtde');


    $.fn.select2.defaults.set("theme", "bootstrap");
    $.fn.select2.defaults.set("language", "pt-BR");

    $produto.select2({
        minimumInputLength: 3,
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        ajax: {
            delay: 750,
            url: Routing.generate('est_entrada_findProdutos'),
            dataType: 'json'
        }
    }).on('select2:select', function () {
        let o = $produto.select2('data')[0];


        $qtde.removeClass();
        $qtde.addClass('form-control').addClass('crsr-dec' + o.unidade_casas_decimais);
        $('#item_unidade_append_label').html(o.unidade_label);

        $unidade.empty().trigger("change");

        $unidade.select2({
            'data': o.unidades
        });

        $unidade.val(o.unidade_id).trigger('change');

        CrosierMasks.maskDecs();

        showPreco();
    });


    $unidade.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        tags: true,
        data: $unidade.data('options')
    }).on('select2:select', function () {
        let o = $unidade.select2('data')[0];

        $qtde.removeClass();
        $qtde.addClass('form-control').addClass('crsr-dec' + o.casas_decimais);
        $('#item_unidade_append_label').html(o.text);
        CrosierMasks.maskDecs();
        showPreco();
    });


    function showPreco() {
        let unidade_text = $unidade.select2('data')[0]?.text;
        if ($produto.val() && unidade_text) {
            let o = $produto.select2('data')[0];
            let helpText = '';
            for (let i in o.precos) {
                let preco = o.precos[i];
                if (String(preco.unidade) === String(unidade_text)) {
                    let precoVenda = parseFloat(preco.preco_prazo).toFixed(2);
                    helpText += preco.lista + ': R$ ' + Numeral(parseFloat(precoVenda)).format('0.0,[00]') + ' . ';
                }
            }
            $produto_helpText.html(helpText);
        }

    }

    $('#btnInserirItem').click(function () {

        if (!$produto.val() || !$qtde.val() || !$unidade.val()) {
            toastrr.error('"É necessário informar o "Produto", a "Qtde" e a "Unidade"');
            return;
        }

        let item = {
            'item': {
                "produto": $produto.val(),
                "qtde": $qtde.val(),
                "unidade": $unidade.val()
            }
        };

        $.ajax({
                dataType: "json",
                data: item,
                url: Routing.generate('est_entrada_formItem', {'entrada': $entradaId.val()}),
                type: 'POST'
            }
        ).done(function (data) {
            if (data.result === 'OK') {
                $('#divTbItens').html(data.divTbItens);
                $produto.val('').trigger('change');
                $qtde.val('');
                $unidade.val('').trigger('change');
                toastrr.success('Item salvo com sucesso');
            } else {
                toastrr.error(data.msg ? data.msg : 'Erro ao salvar item');
            }

        });
    });


    if ($produto.hasClass('focusOnReady')) {
        $produto.select2('focus');
    }


});



