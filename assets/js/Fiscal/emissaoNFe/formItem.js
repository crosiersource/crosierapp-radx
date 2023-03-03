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

  let $icmsValorBc = $('#nota_fiscal_item_icmsValorBc');
  let $icmsValorAliquota = $('#nota_fiscal_item_icmsAliquota');
  let $icmsValor = $('#nota_fiscal_item_icmsValor');

  let $pisValorBc = $('#nota_fiscal_item_pisValorBc');
  let $pisValorAliquota = $('#nota_fiscal_item_pisAliquota');
  let $pisValor = $('#nota_fiscal_item_pisValor');
  
  let $cofinsValorBc = $('#nota_fiscal_item_cofinsValorBc');
  let $cofinsValorAliquota = $('#nota_fiscal_item_cofinsAliquota');
  let $cofinsValor = $('#nota_fiscal_item_cofinsValor');

  function resValorTotal() {
    let qtde = $qtde.maskMoney('unmasked')[0];
    let valorUnit = $valorUnit.maskMoney('unmasked')[0];

    let subTotal = (qtde * valorUnit);
    $subTotal.val(subTotal.toFixed(2).replace('.', ',')).maskMoney('mask');

    let desconto = $desconto.maskMoney('unmasked')[0] ?? 0.00;
    let valorTotal = (subTotal - desconto).toFixed(2);
    
    let valorTotalString = valorTotal.replace('.', ',');
    
    $valorTotal.val(valorTotalString).maskMoney('mask');

    $icmsValorBc.val(valorTotalString).maskMoney('mask');
    let icmsValorAliquota = $icmsValorAliquota.maskMoney('unmasked')[0];
    let icmsValor = valorTotal * (icmsValorAliquota / 100.0);
    $icmsValor.val(icmsValor.toFixed(2).replace('.', ',')).maskMoney('mask');

    $pisValorBc.val(valorTotalString).maskMoney('mask');
    let pisValorAliquota = $pisValorAliquota.maskMoney('unmasked')[0];
    let pisValor = valorTotal * (pisValorAliquota / 100.0);
    $pisValor.val(pisValor.toFixed(2).replace('.', ',')).maskMoney('mask');

    $cofinsValorBc.val(valorTotalString).maskMoney('mask');
    let cofinsValorAliquota = $cofinsValorAliquota.maskMoney('unmasked')[0];
    let cofinsValor = valorTotal * (cofinsValorAliquota / 100.0);
    $cofinsValor.val(cofinsValor.toFixed(2).replace('.', ',')).maskMoney('mask');
    
    CrosierMasks.maskDecs();
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

