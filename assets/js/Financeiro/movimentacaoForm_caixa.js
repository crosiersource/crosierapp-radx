'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Moment from 'moment';


Routing.setRoutingData(routes);

/**
 * Este script é utilizado tanto para lançamentos de movimentações em carteiras comuns quanto em caixas.
 */
$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');
    let $descricao = $('#movimentacao_descricao');
    let $tipoLancto = $('#movimentacao_tipoLancto');
    let $carteira = $('#movimentacao_carteira');
    let $carteiraDestino = $('#movimentacao_carteiraDestino');
    let $categoria = $('#movimentacao_categoria');
    let $modo = $("#movimentacao_modo");
    let $valor = $("#movimentacao_valor");
    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $divCamposCheque = $('#divCamposCheque');
    let $chequeBanco = $('#movimentacao_chequeBanco');
    let $chequeAgencia = $('#movimentacao_chequeAgencia');
    let $chequeConta = $('#movimentacao_chequeConta');
    let $divCamposCartao = $('#divCamposCartao');
    let $bandeiraCartao = $('#movimentacao_bandeiraCartao');
    let $operadoraCartao = $('#movimentacao_operadoraCartao');

    // "cachê" para não precisar ir toda hora buscar via ajax
    let carteiraData = null;

    // Todos os campos
    let camposTodos = [
        $movimentacao_id,
        $tipoLancto,
        $carteira,
        $carteiraDestino,
        $categoria,
        $modo,
        $valor,
        $dtMoviment,
        $divCamposCheque,
        $chequeBanco,
        $chequeAgencia,
        $chequeConta,
        $bandeiraCartao,
        $operadoraCartao,
    ];

    // Campos que não são mostrados em tal tipoLancto
    let camposEscond = {
        // TRANSFERÊNCIA ENTRE CARTEIRAS
        '10': [
            $carteiraDestino,
        ],
        '60': [
            $categoria,
            $modo,
        ],
    };

    /**
     * Método principal. Remonta o formulário de acordo com as regras.
     */
    function handleFormRules() {


        let $tipoLanctoSelected = $tipoLancto.find(':selected').val();
        // Filtro por tipoLancto
        let camposVisiveis = $.grep(camposTodos, function (e) {
            return camposEscond[$tipoLanctoSelected] ? camposEscond[$tipoLanctoSelected].indexOf(e) === -1 : true;
        });

        // Esconde todos
        camposTodos.forEach(function (campo) {
            if (campo.prop('nodeName') === 'DIV') { // para os casos das divCampos...
                campo.css('display', 'none');
            } else {
                campo.closest('.form-group.row').css('display', 'none');
            }
        });

        // Mostra só os correspondentes ao tipoLancto
        camposVisiveis.forEach(function (campo) {
            if (campo.prop('nodeName') === 'DIV') { // para os casos das divCampos...
                campo.css('display', '');
            } else {
                campo.closest('.form-group.row').css('display', '');
            }
        });

        if ($tipoLanctoSelected == '60') {
            $categoria.val(7).trigger('change');
            buildCarteiraDestino();
        } else if ($tipoLanctoSelected == '61') {
            $categoria.val(3).trigger('change');
            buildCarteiraDestino();
        }


        let modo = $modo.find(':selected').text();


        $divCamposCheque.css('display', 'none');
        $divCamposCartao.css('display', 'none');

        if (modo.includes('CHEQUE')) {
            $divCamposCheque.css('display', '');

            // se for 'CHEQUE PRÓPRIO' já preenche com os dados da carteira.
            if (modo.includes('PRÓPRIO')) {
                let carteira = $carteira.find(':selected').data('data');
                $chequeBanco.val(carteira['bancoId']).trigger('change');
                $chequeAgencia.val(carteira['agencia']);

                $chequeConta.val(carteira['conta']);

                // Reconstrói o campo carteira com somente carteiras que possuem cheques
                buildCarteira({'cheque': true});
                return; // para não executar as linhas abaixo.
            }
        } else if (modo.includes('CARTÃO')) {
            $divCamposCartao.css('display', '');
            buildCarteira();
            return; // para não executar as linhas abaixo.
        }

        // Não sendo nem CHEQUE nem CARTÃO, limpa tudo
        buildCarteira(); // se não for cheque, monta novamente com todas as carteiras
        $chequeBanco.val('').trigger('change');
        $chequeAgencia.val('');
        $chequeConta.val('');

        $bandeiraCartao.val('').trigger('change');
        $operadoraCartao.val('').trigger('change');
    }


    /**
     * Busca os dados de carteiras via ajax, ou apenas retorna se já buscado.
     * @param params
     * @returns {*}
     */
    function getCarteiraData(params) {
        if (!params) params = {};
        // Para chamar por ajax apenas 1 vez
        if (!carteiraData) {
            $.ajax({
                    dataType: "json",
                    async: false,
                    data: params,
                    url: Routing.generate('carteira_select2json'),
                    type: 'POST'
                }
            ).done(function (results) {
                results.unshift({"id": '', "text": ''});
                carteiraData = results;
            });
        }
        return carteiraData;

    }

    /**
     * (Re)constrói o campo carteira de acordo com os parâmetros passados (se foram passados).
     * @param params
     */
    function buildCarteira(params) {
        if (!params) params = {};

        let $carteiraSelected = $carteira.find(':selected');
        let val = $carteiraSelected.val() ? $carteiraSelected.val() : $carteiraSelected.data('val');

        $carteira.empty().trigger("change");

        $carteira.select2({
                placeholder: "Selecione...",
                data: getCarteiraData(params),
                width: '100%'
            }
        );
        // Se já estava setado ou se veio o valor do PHP...
        if (val) {
            $carteira.select2("val", val);
            $carteira.trigger('change');
        }
    }


    /**
     * (Re)constrói o campo carteiraDestino (removendo o valor selecionado no campo carteira).
     */
    function buildCarteiraDestino() {
        if (!$carteiraDestino.is(":visible")) return;

        getCarteiraData(); // chamo para inicializar o carteiraData caso ainda não tenha sido
        let carteiraDataDestino = [];

        for (let i = 0; i < carteiraData.length; i++) {
            if (carteiraData[i].id && carteiraData[i].id !== $carteira.val()) {
                carteiraDataDestino.push(carteiraData[i]);
            }
        }
        carteiraDataDestino.unshift({"id": '', "text": ''})

        $carteiraDestino.empty().trigger("change");
        $carteiraDestino.select2({
                placeholder: "Selecione...",
                data: carteiraDataDestino,
                width: '100%'
            }
        );

        if ($carteiraDestino.data('val')) {
            $carteiraDestino.val($carteiraDestino.data('val'));
            $carteiraDestino.trigger('change');
        }
    }

    function buildCategoria() {
        $.ajax({
                dataType: "json",
                async: false,
                url: Routing.generate('categoria_select2json') + '?somenteFolhas=false&tipoLancto=' + $tipoLancto.val(),
                type: 'GET'
            }
        ).done(function (results) {
            // o valor por ter vindo pelo value ou pelo data-val (ou por nenhum)
            let val = $categoria.val() ? $categoria.val() : $categoria.data('val');
            $categoria.empty().trigger("change");

            results.unshift({"id": "", "text": ""});

            $categoria.select2({
                    placeholder: "Selecione...",
                    data: results,
                    width: '100%'
                }
            );
            // Se veio o valor do PHP...
            if (val) {
                $categoria.val(val).trigger('change');
            }
        });
    }


    $tipoLancto.on('select2:select', function () {
        handleFormRules()
    });

    $dtMoviment.on('focus', function () {
        if ($dtMoviment.val() === '') {
            $dtMoviment.val(Moment().format('DD/MM/YYYY'));
        }
    });

    $carteira.on('select2:select', function () {
        handleFormRules();
    });

    $modo.on('select2:select', function () {
        handleFormRules()
    });

    $descricao.on('focus', function () {
        if ($descricao.val() === '') {
            if ($bandeiraCartao.select2('data')[0].text !== '') {
                $descricao.val($bandeiraCartao.select2('data')[0].text);
            }
        }
    });


    // -----------------

    function initializeForm() {

        buildCategoria();
        handleFormRules();

        $tipoLancto.select2('focus');
    }


    initializeForm();




})
;
