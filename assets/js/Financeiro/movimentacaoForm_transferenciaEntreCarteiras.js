/* eslint-disable */


import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';

$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");

import Moment from 'moment';


Routing.setRoutingData(routes);

/**
 * Este script é utilizado tanto para lançamentos de movimentações em carteiras comuns quanto em caixas.
 */
$(document).ready(function () {

  let $movimentacao_id = $('#movimentacao_id');
  let $tipoLancto = $('#movimentacao_tipoLancto');
  let $descricao = $('#movimentacao_descricao');
  let $carteira = $('#movimentacao_carteira');
  let $carteiraDestino = $('#movimentacao_carteiraDestino');
  let $modo = $("#movimentacao_modo");
  let $valor = $("#movimentacao_valor");
  let $dtMoviment = $("#movimentacao_dtMoviment");

  let $divCamposCheque = $('#divCamposCheque');
  let $chequeBanco = $('#movimentacao_chequeBanco');
  let $chequeAgencia = $('#movimentacao_chequeAgencia');
  let $chequeConta = $('#movimentacao_chequeConta');


  /**
   * Desativa a opção da carteira selecionada no campo "Carteira"
   */
  function handleCarteiraDestino() {
    $carteiraDestino.find('option').attr('disabled', null);
    $carteiraDestino.find('option[value="' + $carteira.val() + '"]').attr('disabled', null);
    $carteiraDestino.select2({data: $carteiraDestino.select2('data')});
  }

  $descricao.on('focus', function () {
    if ($descricao.val() === '') {
      $descricao.val('TRANSFERÊNCIA ENTRE CARTEIRAS');
    }
  });

  $dtMoviment.on('focus', function () {
    if ($dtMoviment.val() === '') {
      $dtMoviment.val(Moment().format('DD/MM/YYYY'));
    }
  });

  $carteira.on('select2:select', function () {
    handleCarteiraDestino();
  });

  $modo.on('select2:select', function () {
    handleModoRules();
  });

  /**
   * Regras de acordo com o campo modo.
   */
  function handleModoRules() {
    let modo = $modo.find(':selected').text();

    $divCamposCheque.css('display', 'none');

    if (modo.includes('CHEQUE')) {
      $divCamposCheque.css('display', '');
    }
    $chequeBanco.val('').trigger('change');
    $chequeAgencia.val('');
    $chequeConta.val('');
  }

  $carteira.select2({
    width: '100%',
    dropdownAutoWidth: true,
    placeholder: '...',
    allowClear: true
  });

  $carteiraDestino.select2({
    width: '100%',
    dropdownAutoWidth: true,
    placeholder: '...',
    allowClear: true
  });

  $modo.select2({
    width: '100%',
    dropdownAutoWidth: true,
    placeholder: '...',
    allowClear: true
  });


  // -----------------

  function initializeForm() {
    handleModoRules();
    $carteira.select2('focus');
  }


  initializeForm();

})
;
