'use strict';

import $ from "jquery";
import Numeral from "numeral";

class ParcelamentoForm {

    static gerarParcelas() {
        let qtdeParcelas = Numeral($("#parcelamento_qtdeParcelas").val()).value();
        let valorParcela = Numeral($("#parcelamento_valorParcela").val()).value();
        let valorTotal = Numeral($("#parcelamento_valorTotal").val()).value();
        let diaFixo = $("#parcelamento_diaFixo").val();
        let primeiroVencto = $("#parcelamento_primeiroVencto").val();

        let tbody = $('#tbody_parcelas');
        tbody.html('');

        let params = {
            "qtdeParcelas": qtdeParcelas,
            "valorTotal": valorTotal,
            "valorParcela": valorParcela,
            "diaFixo": diaFixo,
            "primeiroVencto": primeiroVencto,
            "movimentacao": $('form[name="movimentacao"]').serialize()
        };

        $.ajax({
                dataType: "json",
                url: Routing.generate('parcelamento_gerarParcelas'),
                data: params,
                type: 'POST',
                success: function (results) {

                    $.each(results, function (i, parcela) {
                        let tr = $('<tr>');
                        tr.append($('<td>').html(parcela.numParcela)); // numParcela
                        tr.append($('<td>').html(
                            '<input type="text" name="parcela[' + i + '][dtVencto]" required="required" class="crsr-date form-control" value="' + parcela.dtVencto + '" maxlength="10">'
                        )); // dtVencto
                        tr.append($('<td>').html(
                            '<input type="text" name="parcela[' + i + '][dtVenctoEfetiva]" required="required" class="crsr-date form-control" value="' + parcela.dtVenctoEfetiva + '" maxlength="10">'
                        )); // dtVenctoEfetiva
                        tr.append($('<td>').html(
                            '<input type="text" name="parcela[' + i + '][documentoNum]" required="required" class="form-control" value="' + parcela.documentoNum + '">'
                        )); // numDocumento

                        tr.append($('<td class="text-right">').html(
                            `<div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$ </span>
                                </div>
                                <input type="text" name="parcela[` + i + `][valor]" required="required" class="crsr-money form-control" value="` + parcela.valor + `">
                            </div>`
                        )); // Valor
                        tbody.append(tr);
                    });

                    CrosierMasks.maskAll();
                }
            }
        );
    }

    static calcularValorTotal() {
        let qtdeParcelas = Numeral($("#parcelamento_qtdeParcelas").val()).value();
        let valorParcela = Numeral($("#parcelamento_valorParcela").val()).value();
        let valorTotal = Numeral($("#parcelamento_valorTotal").val()).value();

        if (qtdeParcelas) {
            // Se o valorParcela estiver setado, o valorTotal é calculado por ele
            if (valorParcela) {
                valorTotal = valorParcela * qtdeParcelas;
                $("#parcelamento_valorTotal").val(Numeral(valorTotal).format('0,0.00'));
            } else {
                valorParcela = parseFloat((valorTotal / qtdeParcelas).toFixed(2));
                let numeralValorParcela = Numeral(parseFloat(valorParcela));
                $("#parcelamento_valorParcela").val(Numeral(valorParcela).format('0,0.00'));

                // Já refaz a conta para o valorTotal corresponder
                valorTotal = valorParcela * qtdeParcelas;
                $("#parcelamento_valorTotal").val(Numeral(valorTotal).format('0,0.00'));
            }
        }
    }

}

$(document).ready(function () {

    let $movimentacao_id = $('#movimentacao_id');
    let $carteira = $('#movimentacao_carteira');
    let $divCamposGrupo = $('#divCamposGrupo');
    let $grupo = $('#grupo'); // não é um campo do form
    let $grupoItem = $('#movimentacao_grupoItem');
    let $categoria = $('#movimentacao_categoria');
    let $centroCusto = $('#movimentacao_centroCusto');
    let $modo = $("#movimentacao_modo");
    let $dtMoviment = $("#movimentacao_dtMoviment");
    let $dtVencto = $("#movimentacao_dtVencto");
    let $divCamposCheque = $('#divCamposCheque');
    let $chequeBanco = $('#movimentacao_chequeBanco');
    let $chequeAgencia = $('#movimentacao_chequeAgencia');
    let $chequeConta = $('#movimentacao_chequeConta');
    let $divCamposDocumento = $('#divCamposDocumento');
    let $documentoBanco = $('#movimentacao_documentoBanco');
    let $documentoNum = $('#movimentacao_documentoNum');
    let $pessoa = $('#movimentacao_pessoa');

    // "cachê" para grupos e itens
    let grupoData = null;

    // "cachê" para não precisar ir toda hora buscar via ajax
    let carteiraData = null;


    /**
     * Método principal. Remonta o formulário de acordo com as regras.
     */
    function handleFormRules() {
        // São esses 3 campos que definem o comportamento do formulário.
        handleModoRules();
    }

    /**
     * Regras de acordo com o campo modo.
     */
    function handleModoRules() {
        let modo = $modo.find(':selected').text();

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
        }

        // Não sendo nem CHEQUE nem CARTÃO, limpa tudo
        $divCamposCheque.css('display', 'none');
        buildCarteira(); // se não for cheque, monta novamente com todas as carteiras
        $chequeBanco.val('').trigger('change');
        $chequeAgencia.val('');
        $chequeConta.val('');
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

        let _carteira_data =
            // percorre o carteira_data vai colocando no _carteira_data se retornar true
            $.grep(getCarteiraData(params), function (e) {
                // se não foi passado params
                if ($.isEmptyObject(params)) {
                    return true;
                } else {
                    // se todos os params passados corresponderem
                    let match = true;
                    $.each(params, function (key, value) {
                        match = match && e[key] === value;
                    });
                    return match;
                }
            });


        $carteira.select2({
                placeholder: "Selecione...",
                data: _carteira_data,
                width: '100%'
            }
        );
        // Se já estava setado ou se veio o valor do PHP...
        if (val) {
            $carteira.select2("val", val);
            $carteira.trigger('change');
        }
    }


    function buildCategoria() {
        $.ajax({
                dataType: "json",
                async: false,
                url: Routing.generate('categoria_select2json'),
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

    $carteira.on('select2:select', function () {
        handleFormRules();
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

        $pessoa.select2({
            ajax: {
                delay: 250,
                url: function (params) {
                    return Routing.generate('bse_pessoa_findByNome') + '/' + params.term;
                },
                dataType: 'json',
                processResults: function (data) {
                    let dataNew = $.map(data.results, function (obj) {
                        obj.text = obj['nome'];
                        return obj;
                    });
                    return {results: dataNew};
                },
                cache: true
            },
            minimumInputLength: 1
        });

        buildCategoria();
        buildGrupos();
        handleFormRules();
    }


    initializeForm();



    $('#parcelamento_btnGerar').click(function () {
        ParcelamentoForm.gerarParcelas()
    });

    $("#parcelamento_valorParcela,#parcelamento_valorTotal").focus(function () {
        ParcelamentoForm.calcularValorTotal()
    });

    $("#parcelamento_valorTotal,#parcelamento_valorParcela,#parcelamento_qtdeParcelas").blur(function () {
        if ($(this).val() === '' || parseFloat($(this).val()) === 0.0) {
            $("#parcelamento_valorParcela").val('0,00');
            $("#parcelamento_valorTotal").val('0,00');
        }
    });

});

// Defino como global para que possa ser executado no layout.js > $('#confirmationModal') > executeFunctionByName...
global.ParcelamentoForm = ParcelamentoForm;



