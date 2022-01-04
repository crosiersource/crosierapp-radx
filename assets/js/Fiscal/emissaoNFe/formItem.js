/* eslint-disable */

import $ from 'jquery';

import 'jquery-mask-plugin';
import 'jquery-maskmoney/dist/jquery.maskMoney.js';



$(document).ready(function () {

    let $qtde = $('#nota_fiscal_item_qtde');
    let $valorUnit = $('#nota_fiscal_item_valorUnit');
    let $subTotal = $('#nota_fiscal_item_subtotal');
    let $desconto = $('#nota_fiscal_item_valorDesconto');
    let $valorTotal = $('#nota_fiscal_item_valorTotal');

    function resValorTotal() {
      console.log('resValorTotal');
        let qtde = $qtde.maskMoney('unmasked')[0];
        let valorUnit = $valorUnit.maskMoney('unmasked')[0];

        let subTotal = (qtde * valorUnit);
        $subTotal.val(subTotal.toFixed(2).replace('.',',')).maskMoney('mask');

        let desconto = $desconto.maskMoney('unmasked')[0] ?? 0.00;
        let valorTotal = (subTotal - desconto).toFixed(2).replace('.',',');
        $valorTotal.val(valorTotal).maskMoney('mask');
        CrosierMasks.maskDecs();
        console.log('fim');
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


});

