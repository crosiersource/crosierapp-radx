/* eslint-disable */

import Moment from 'moment';

import $ from "jquery";

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import 'jquery-maskmoney/dist/jquery.maskMoney.js';

import 'daterangepicker';

$(document).ready(function () {

  let $carteira = $('#movimentacao_carteira');
  
  let $sacado = $('#movimentacao_sacado');
  let $cedente = $('#movimentacao_cedente');

  let $categoria = $('#movimentacao_categoria');

  let $valor = $("#movimentacao_valor");
  let $descontos = $("#movimentacao_descontos");
  let $acrescimos = $("#movimentacao_acrescimos");
  let $valorTotal = $("#movimentacao_valorTotal");


  let filiais;

  $.getJSON(Routing.generate('fin_movimentacao_filiais'), function (data) {
    filiais = data;
  });


  $categoria.select2({
      placeholder: "Selecione...",
      width: '100%',
      matcher: function (params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
          return data;
        }

        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') {
          return null;
        }

        // `params.term` should be the term that is used for searching
        // `data.text` is the text that is displayed for the data object
        if (data.text.replace(/\./g, '').toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
          // You can return modified objects from here
          // This includes matching the `children` how you want in nested data sets
          return data;
        }

        // Return `null` if the term should not be displayed
        return null;
      }
    }
  ).on('select2:select', function () {
    handleSacadoCedente();
  });


  /**
   * Se a categoria for de "1 - ENTRADAS", o cedente é uma das filiais.
   * Se a categoria for de "2 - SAÍDAS", uma das filiais é o sacado.
   */
  function handleSacadoCedente() {

    let categoria = $categoria.select2('data')[0];

    let $campoComFiliais;
    let $campoComBusca;

    if (categoria.element.dataset.codigoSuper === '1') {
      $campoComFiliais = $cedente;
      $campoComBusca = $sacado;
    } else if (categoria.element.dataset.codigoSuper === '2') {
      $campoComFiliais = $sacado;
      $campoComBusca = $cedente;
    } else {
      alert('Erro ao configurar sacado/cedente (' + categoria.element.dataset.codigoSuper + ')');
    }

    $campoComFiliais.select2({
      placeholder: "Selecione...",
      width: '100%',
      data: filiais
    });

    $campoComBusca.select2({
      placeholder: "Selecione...",
      width: '100%',
      dropdownAutoWidth: true,
      allowClear: true,
      minimumInputLength: 2,
      ajax: {
        delay: 750,
        url: function (params) {
          return Routing.generate('fin_movimentacao_findSacadoOuCedente') + '?term=' + params.term;
        },
        dataType: 'json'
      }
    });

    $sacado.prop('disabled', false);
    $cedente.prop('disabled', false);
  }


  $('#filter_dts').daterangepicker(
    {
      opens: 'left',
      autoApply: true,
      locale: {
        "format": "DD/MM/YYYY",
        "separator": " - ",
        "applyLabel": "Aplicar",
        "cancelLabel": "Cancelar",
        "fromLabel": "De",
        "toLabel": "Até",
        "customRangeLabel": "Custom",
        "daysOfWeek": [
          "Dom",
          "Seg",
          "Ter",
          "Qua",
          "Qui",
          "Sex",
          "Sáb"
        ],
        "monthNames": [
          "Janeiro",
          "Fevereiro",
          "Março",
          "Abril",
          "Maio",
          "Junho",
          "Julho",
          "Agosto",
          "Setembro",
          "Outubro",
          "Novembro",
          "Dezembro"
        ],
        "firstDay": 0
      },
      ranges: {
        'Hoje': [Moment(), Moment()],
        'Ontem': [Moment().subtract(1, 'days'), Moment().subtract(1, 'days')],
        'Últimos 7 dias': [Moment().subtract(6, 'days'), Moment()],
        'Últimos 30 dias': [Moment().subtract(29, 'days'), Moment()],
        'Este mês': [Moment().startOf('month'), Moment().endOf('month')],
        'Mês passado': [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')]
      },
      "alwaysShowCalendars": true
    }
  );


  function resValorTotal() {
    let valor = Number($valor.val().replace('.', '').replace(',', '.'));
    let descontos = Number($descontos.val().replace('.', '').replace(',', '.'));
    let acrescimos = Number($acrescimos.val().replace('.', '').replace(',', '.'));
    let valorTotal = (valor - descontos + acrescimos).toFixed(2).replace('.', ',');
    $valorTotal.val(valorTotal).maskMoney('mask');
  }

  $valor.on('blur', function () {
    resValorTotal()
  });
  $descontos.on('blur', function () {
    resValorTotal()
  });
  $acrescimos.on('blur', function () {
    resValorTotal()
  });

  $carteira.select2({
    width: '100%',
    dropdownAutoWidth: true,
    placeholder: '...',
    allowClear: true
  });

  handleSacadoCedente();


});
