/* eslint-disable */

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
import hotkeys from 'hotkeys-js';
import Sortable from "sortablejs";
import toastrr from "toastr";

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

  let $formItem = $('#formItem');
  let $item_produto = $('#item_produto');

  let $qtde = $('#item_qtde');
  let $precoVenda = $('#item_precoVenda');
  let $desconto = $('#item_desconto');
  let $valorTotal = $('#item_valorTotal');


  function resValorTotal() {
    let produto = $item_produto.select2('data')[0];
    if (produto) {
      
      let qtde = $qtde.val().replace('.', '').replace(',', '.');
      let preco_prazo = $precoVenda.val().replace('.', '').replace(',', '.');
      let valorUnit = parseFloat(preco_prazo);
      $precoVenda.val(valorUnit.toFixed(2).replace('.', ','));

      let subTotal = (qtde * valorUnit);
      let desconto = $desconto.val().replace('.', '').replace(',', '.');
      let valorTotal = (subTotal - desconto).toFixed(2).replace('.', ',');
      $valorTotal.val(valorTotal);
      CrosierMasks.maskDecs();
    }
  }

  $qtde.blur(function () {
    resValorTotal();
  });

  $precoVenda.blur(function () {
    resValorTotal();
  });

  $desconto.blur(function () {
    resValorTotal();
  });


  $item_produto.select2({
    minimumInputLength: 3,
    width: '100%',
    dropdownAutoWidth: true,
    placeholder: '...',
    allowClear: true,
    templateResult: function (data) {
      return data.text;
    },
    escapeMarkup: function (markup) {
      return markup;
    },
    ajax: {
      delay: 750,
      url: Routing.generate('ven_venda_findProdutosByCodigoOuNomeJson'),
      dataType: 'json',
      cache: true,
      processResults: function (data) {
        if (data.results.length === 1 && data.results[0].codigoExato) {
          $item_produto.empty().trigger("change");
          $item_produto.select2({
              data: data.results,
              dropdownAutoWidth: true,
              width: '100%'
            }
          );
          $item_produto.val(data.results[0].id).trigger('change');
          $formItem.submit();
        }
        return data;
      }
    }
  }).on('select2:select', function () {
    let o = $item_produto.select2('data')[0];
    let precoVenda = parseFloat(o.preco_venda).toFixed(2).replace('.', ',');
    $precoVenda.val(precoVenda);
    resValorTotal();
  }).on('select2:close', function () {
    let o = $item_produto.select2('data')[0];
    // se não selecionou nenhum produto, volta para o campo de qtde
    if (typeof o === 'undefined') {
      $qtde.focus();
    }
  });


});

