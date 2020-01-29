'use strict';

import $ from 'jquery';


$(document).ready(function () {


    let $tipo = $("input[name*='nota_fiscal[tipoNotaFiscal]']");

    let $qtde = $('#nota_fiscal_item_qtde');
    let $valorUnit = $('#nota_fiscal_item_valor_unit');
    let $subTotal = $('#nota_fiscal_item_sub_total');
    let $desconto = $('#nota_fiscal_item_valor_desconto');
    let $valorTotal = $('#nota_fiscal_item_valor_total');

    function resValorTotal() {
        let qtde = $qtde.maskMoney('unmasked')[0];
        let valorUnit = $valorUnit.maskMoney('unmasked')[0];

        let subTotal = (qtde * valorUnit).toFixed(2);
        $subTotal.val(subTotal).maskMoney('mask');

        let desconto = $desconto.maskMoney('unmasked')[0];
        let valorTotal = (subTotal - desconto).toFixed(2);
        $valorTotal.val(valorTotal).maskMoney('mask');
    }

    $qtde.blur(function () {
        resValorTotal()
    });

    $valorUnit.blur(function () {
        resValorTotal()
    });

    $desconto.blur(function () {
        resValorTotal()
    });


});

