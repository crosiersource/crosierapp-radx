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

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

Numeral.locale('pt-br');

Routing.setRoutingData(routes);


$(document).ready(function () {

  let $formItem = $('#formItem');
  let $item_produto = $('#item_produto');

  let $unidade = $('#item_unidade');
  let $qtde = $('#item_qtde');
  let $precoVenda = $('#item_precoVenda');
  let $desconto = $('#item_desconto');
  let $valorTotal = $('#item_valorTotal');
  let $obs = $('#item_obs');

  let $item_id = $('#item_id');


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
          let o = data.results[0];
          $item_produto.val(data.results[0].id).trigger('change');
          let precoVenda = parseFloat(o.preco_venda).toFixed(2).replace('.', ',');
          $precoVenda.val(precoVenda);
          resValorTotal();
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




  $('.btnEditProduto').click(function () {
    let dados = $(this).data();
    $item_id.val(dados.itemId);

    $unidade.prop('disabled', true);

    $('#btnInserirItem').html('<i class="fas fa-save" aria-hidden="true"></i>  Alterar');

    $qtde.removeClass();
    $qtde.addClass('form-control').addClass('crsr-dec' + dados.itemUnidadeCasasDecimais);
    CrosierMasks.maskDecs();

    let qtde = parseFloat(dados.itemQtde).toFixed(dados.itemUnidadeCasasDecimais);
    $qtde.val(qtde.replace('.', ','));

    $('#item_unidade_append_label').html(dados.itemUnidadeLabel);

    let precoVenda = parseFloat(dados.itemPrecoVenda).toFixed(2);
    $precoVenda.val(precoVenda.replace('.', ','));

    let desconto = parseFloat(dados.itemDesconto).toFixed(2);
    $desconto.val(desconto.replace('.', ','));

    if (!$item_produto.find("option[value='" + dados.itemId + "']").length) {
        let newOption = new Option(dados.itemProdutoNome, dados.itemProdutoId, false, false);
        $item_produto.append(newOption);
    }
    $item_produto.val(dados.itemProdutoId).trigger('change');

    if (!$unidade.find("option[value='" + dados.itemUnidadeId + "']").length) {
      $unidade.append(new Option(dados.itemUnidadeLabel, dados.itemUnidadeId, false, false));
    }
    $unidade.val(dados.itemUnidadeId).trigger('change');

    $obs.val(dados.itemObs);

    $qtde.focus();

    $item_produto.prop('disabled', true);
    

  });


});

