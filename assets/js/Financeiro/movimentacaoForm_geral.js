'use strict';

import $ from "jquery";

import routes from '../../static/fos_js_routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import Moment from 'moment';

import 'select2/dist/css/select2.css';
import 'select2';
import 'select2/dist/js/i18n/pt-BR.js';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';
$.fn.select2.defaults.set("theme", "bootstrap");
$.fn.select2.defaults.set("language", "pt-BR");


Routing.setRoutingData(routes);

/**
 * Este script é utilizado tanto para lançamentos de movimentações em carteiras comuns quanto em caixas.
 */
$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');
    let $carteira = $('#movimentacao_carteira');
    let $carteiraDestino = $('#movimentacao_carteiraDestino');
    let $tipoLancto = $('#movimentacao_tipoLancto');
    let $divCamposGrupo = $('#divCamposGrupo');
    let $grupo = $('#grupo'); // não é um campo do form
    let $grupoItem = $('#movimentacao_grupoItem');
    let $categoria = $('#movimentacao_categoria');
    let $centroCusto = $('#movimentacao_centroCusto');
    let $modo = $("#movimentacao_modo");
    let $divCamposValores = $("#divCamposValores");

    let $valor = $("#movimentacao_valor");
    let $descontos = $("#movimentacao_descontos");
    let $acrescimos = $("#movimentacao_acrescimos");
    let $valorTotal = $("#movimentacao_valorTotal");

    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $divDtVVEP = $("#divDtVVEP");
    let $dtVenctoEfetiva = $("#movimentacao_dtVenctoEfetiva");
    let $dtPagto = $("#movimentacao_dtPagto");

    let $divCamposCheque = $('#divCamposCheque');
    let $chequeBanco = $('#movimentacao_chequeBanco');
    let $chequeAgencia = $('#movimentacao_chequeAgencia');
    let $chequeConta = $('#movimentacao_chequeConta');
    let $divCamposDocumento = $('#divCamposDocumento');
    let $documentoBanco = $('#movimentacao_documentoBanco');
    let $documentoNum = $('#movimentacao_documentoNum');
    let $sacado = $('#movimentacao_sacado');
    let $cedente = $('#movimentacao_cedente');
    let $divCamposRecorrencia = $('#divCamposRecorrencia');
    let $recorrente = $('#movimentacao_recorrente');
    let $divCamposCartao = $('#divCamposCartao');
    let $bandeiraCartao = $('#movimentacao_bandeiraCartao');
    let $operadoraCartao = $('#movimentacao_operadoraCartao');

    // "cachê" para grupos e itens
    let grupoData = null;

    // "cachê" para não precisar ir toda hora buscar via ajax
    let carteiraData = null;

    // Todos os campos
    let camposTodos = [
        $movimentacao_id,
        $carteira,
        $carteiraDestino,
        $tipoLancto,
        $divCamposGrupo,
        $categoria,
        $centroCusto,
        $modo,
        $divCamposValores,
        $valor,
        $descontos,
        $acrescimos,
        $valorTotal,
        $dtMoviment,
        $dtVencto,
        $divDtVVEP,
        $dtVenctoEfetiva,
        $dtPagto,
        $divCamposCheque,
        $chequeBanco,
        $chequeAgencia,
        $chequeConta,
        $divCamposDocumento,
        $documentoBanco,
        $documentoNum,
        $sacado,
        $cedente,
        $divCamposRecorrencia,
        $recorrente,
        $bandeiraCartao,
        $operadoraCartao,
    ];

    // Campos que não são mostrados em tal tipoLancto
    let camposEscond = {
        // GERAL
        '10': [
            $carteiraDestino,
            $divCamposGrupo
        ],
        // TRANSFERÊNCIA ENTRE CARTEIRAS
        '60': [
            $categoria,
            $divCamposGrupo,
            $divDtVVEP,
            $sacado,
            $cedente,
            $divCamposDocumento
        ],
        // MOVIMENTACAO DE GRUPO
        '70': [
            $modo,
            $carteira,
            $carteiraDestino,
            $divDtVVEP
        ]
    };

    /**
     * Método principal. Remonta o formulário de acordo com as regras.
     */
    function handleFormRules() {
        // São esses 3 campos que definem o comportamento do formulário.
        handleTipoLanctoRules();
        handleModoRules();
        handleCarteiraCaixaRules();
    }

    /**
     * Regras de acordo com o tipoLancto.
     */
    function handleTipoLanctoRules() {

        let $tipoLanctoSelected = $tipoLancto.find(':selected').val();

        // if ($tipoLancto.find(':selected').data('data')['route']) {
        //     let tipoLanctoRouteURL = Routing.generate($tipoLancto.find(':selected').data('data')['route']);
        //     if (tipoLanctoRouteURL !== window.location.pathname) {
        //         window.location.href = tipoLanctoRouteURL;
        //         return;
        //     }
        // }

        // Filtro por tipoLancto
        let camposVisiveis = $.grep(camposTodos, function (e) {
            return camposEscond[$tipoLanctoSelected] ? camposEscond[$tipoLanctoSelected].indexOf(e) === -1 : true;
        });
        console.dir(camposVisiveis);

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
                console.log(campo.prop('id') + ' é DIV');
                campo.css('display', '');
            } else {
                campo.closest('.form-group.row').css('display', '');
                console.log(campo.prop('id') + ' não é DIV');
            }
        });

        if ($tipoLanctoSelected === 'TRANSF_PROPRIA') {
            buildCarteiraDestino();
        }

    }

    /**
     * Regras de acordo com o campo modo.
     */
    function handleModoRules() {
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
     * Regras para carteiras que são caixas.
     */
    function handleCarteiraCaixaRules() {
        let caixa = null;
        if ($carteira.find(':selected').data('data')) {
            let carteiraData = $carteira.find(':selected').data('data');
            caixa = carteiraData['caixa'];
        } else {
            caixa = $carteira.data('caixa');
        }
        if (caixa) {
            $dtVencto.closest('.form-group.row').css('display', 'none');
            $dtVenctoEfetiva.closest('.form-group.row').css('display', 'none');
            $dtPagto.closest('.form-group.row').css('display', 'none');
            $divCamposRecorrencia.css('display', 'none');
            $divCamposDocumento.css('display', 'none');
            $centroCusto.closest('.form-group.row').css('display', 'none');
        } else {
            $dtVencto.closest('.form-group.row').css('display', '');
            $dtVenctoEfetiva.closest('.form-group.row').css('display', '');
            $dtPagto.closest('.form-group.row').css('display', '');
            $divCamposRecorrencia.css('display', '');
            $divCamposDocumento.css('display', '');
            $centroCusto.closest('.form-group.row').css('display', '');
        }
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
                console.log('getCarteiraData');
                console.dir(carteiraData);
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


    /**
     * Constrói o campo tipoLancto.
     */
    function buildTiposLancto() {
        // Se o valor já veio setado no data-val, então é porque só deve exibir um tipo
        if ($tipoLancto.data('val')) {
            $tipoLancto.select2();
        } else {
            $.ajax({
                    dataType: "json",
                    async: false,
                    url: Routing.generate('movimentacao_getTiposLanctos'),
                    data: {"formMovimentacao": $('form[name="movimentacao"]').serialize()},
                    type: 'POST'
                }
            ).done(function (results) {

                // o valor por ter vindo pelo value ou pelo data-val (ou por nenhum)
                let val = $tipoLancto.val() ? $tipoLancto.val() : $tipoLancto.data('val');

                results = $.map(results['tiposLanctos'], function (o) {
                    return {id: o.val, text: o.title, route: o.route};
                });

                $tipoLancto.empty().trigger("change");
                $tipoLancto.select2({
                        data: results,
                        width: '100%'
                    }
                );
                // Se veio o valor...
                if (val) {
                    $tipoLancto.val(val).trigger('change');
                }
            });
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

    /**
     * Constrói os campos de grupo e grupoItem.
     */
    function buildGrupos() {
        /**
         * Handler para evento no select2 #grupo
         */
        $grupo.on('select2:select', function () {
            $grupoItem.empty().trigger("change");
            let results = $(this).select2('data')[0]['itens'];
            results.unshift({"id": "", "text": "Selecione..."});
            $grupoItem.select2({
                data: results,
                width: '100%'
            });
            if ($grupoItem.data('val')) {
                $grupoItem.val($grupoItem.data('val')).trigger('change').trigger('select2:select');
            }
        });

        $.ajax({
            url: Routing.generate('grupo_select2json') + '?abertos=true',
            dataType: 'json',
            async: false
        }).done(function (result) {
            result.unshift({"id": "", "text": "Selecione..."});
            $grupo.empty().trigger("change");
            $grupo.select2({
                data: result,
                width: '100%'
            });
            if ($grupoItem.data('valpai')) {
                $grupo.val($grupoItem.data('valpai')).trigger('change').trigger('select2:select');
            }
        });

    }

    /**
     * Constrói o campo sacado de acordo com as regras.
     */
    function buildSacado() {
        if ($sacado.data('route-url')) {
            $sacado.select2({
                minimumInputLength: 2,
                ajax: {
                    delay: 750,
                    url: function (params) {
                        console.log('route: ' + $sacado.data('route-url'));
                        console.log(params.term);
                        let uri = $sacado.data('route-url') + params.term;
                        console.log(uri);
                        return uri;
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        console.dir(data);
                        let dataNew = $.map(data.results, function (obj) {
                            obj.text = obj['nome'];
                            return obj;
                        });
                        return {results: dataNew};
                    },
                    cache: true
                }
            });
        } else {
            $sacado.select2();
        }
    }

    /**
     * Constrói o campo cedente de acordo com as regras.
     */
    function buildCedente() {
        if ($cedente.data('route-url')) {
            $cedente.select2({
                minimumInputLength: 2,
                ajax: {
                    delay: 750,
                    url: function (params) {
                        console.log('route: ' + $cedente.data('route-url'));
                        console.log(params.term);
                        let uri = $cedente.data('route-url') + params.term;
                        console.log(uri);
                        return uri;
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        console.dir(data);
                        let dataNew = $.map(data.results, function (obj) {
                            obj.text = obj['nome'];
                            return obj;
                        });
                        return {results: dataNew};
                    },
                    cache: true
                }
            });
        } else {
            $cedente.select2();
        }
    }

    function resValorTotal() {
        let valor =  Number($valor.val().replace('.','').replace(',','.'));
        let descontos = Number($descontos.val().replace('.','').replace(',','.'));
        let acrescimos = Number($acrescimos.val().replace('.','').replace(',','.'));
        let valorTotal = (valor - descontos + acrescimos).toFixed(2).replace('.',',');
        $valorTotal.val(valorTotal).maskMoney('mask');
    }


    function checkExibirCamposRecorrente() {
        let selected = $("#movimentacao_recorrente option:selected").val();
        if (selected === true || selected > 0) {
            $('#camposRecorrente').css('display', '');
        } else {
            $('#camposRecorrente').css('display', 'none');
        }
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

    $dtMoviment.on('focus', function () {
        if ($dtMoviment.val() === '') {
            $dtMoviment.val(Moment().format('DD/MM/YYYY'));
        }
    });

    // Para poder verificar se mudou a dtVencto antes de chamar
    let dtVenctoSaved = null;
    $dtVenctoEfetiva.on('focus', function () {
        if (dtVenctoSaved != $dtVencto.val()) {
            let route = $dtVenctoEfetiva.data('route') + '/?financeiro=true&dt=' + encodeURIComponent($dtVencto.val());
            dtVenctoSaved = $dtVencto.val();
            $.ajax(
                route,
                {
                    crossDomain: true,
                    dataType: "json",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    xhrFields: {
                        withCredentials: true
                    }
                }
            ).done(
                function (data) {
                    let dtF = Moment(data['diaUtil']).format('DD/MM/YYYY')
                    $dtVenctoEfetiva.val(dtF);
                }
            ).fail(function (jqXHR, textStatus, errorThrown) {
                if (jqXHR) {
                    console.dir(jqXHR);
                }
                if (textStatus) {
                    console.dir(textStatus);
                }
            });
        }
    });

    $recorrente.on('change', function () {
        checkExibirCamposRecorrente();
    });

    $carteira.on('select2:select', function () {
        handleFormRules();
    });

    $tipoLancto.on('select2:select', function () {
        handleFormRules()
    });

    $modo.on('select2:select', function () {
        handleFormRules()
    });


    // -----------------

    function initializeForm() {

        $modo.select2({placeholder: "Selecione..."});
        $centroCusto.select2();
        $documentoBanco.select2();
        $chequeBanco.select2();
        $operadoraCartao.select2();
        $bandeiraCartao.select2();

        buildSacado();
        buildCedente();

        buildTiposLancto();
        buildCategoria();
        buildGrupos();
        handleFormRules();
    }


    initializeForm();

    $tipoLancto.focus();


})
;
