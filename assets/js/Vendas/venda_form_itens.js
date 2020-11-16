'use strict';

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
    let $item_id = $('#item_id');

    let $qtde = $('#item_qtde');
    let $unidade = $('#item_unidade');
    let $precoVenda = $('#item_precoVenda');
    let $desconto = $('#item_desconto');
    let $valorTotal = $('#item_valorTotal');
    let $devolucao = $('#item_devolucao');

    let $canal = $('#venda_canal');


    function resValorTotal() {
        let produto = $item_produto.select2('data')[0];
        if (produto) {
            let unidade = $unidade.select2('data')[0].text;

            let qtde = $qtde.val().replace('.', '').replace(',', '.');

            let lista = 'VAREJO';
            if ($canal.val() == 'ATACADO') {
                lista = 'ATACADO';
            } else if (qtde && produto.qtde_min_para_atacado > 0) {
                lista = parseFloat(qtde) < parseFloat(produto.qtde_min_para_atacado) ? 'VAREJO' : 'ATACADO';
            }
            // let preco_prazo = produto.precos[unidade][lista]['preco_prazo'];
            let preco_prazo = $precoVenda.val().replace('.', '').replace(',', '.');
            let valorUnit = parseFloat(preco_prazo);
            $precoVenda.val(valorUnit.toFixed(2).replace('.', ','));
            $('#item_precoVenda_help').html('* ' + lista + ' (Mín: ' + produto.qtde_min_para_atacado + ')');

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
        ajax: {
            delay: 750,
            url: Routing.generate('ven_venda_findProdutosByCodigoOuNomeJson'),
            dataType: 'json',
            cache: true,
            processResults: function (data) {
                if (data.results.length == 1 && data.results[0].codigoExato) {
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
        $qtde.removeClass();
        $qtde.addClass('form-control').addClass('crsr-dec' + o.unidade_casas_decimais);

        $('#item_unidade_append_label').html(o.unidade_label);

        $unidade.empty().trigger("change");

        $unidade.select2({
            'data': o.unidades
        });

        $unidade.val(o.unidade_id).trigger('change');

        let precoVenda = parseFloat(o.preco_venda).toFixed(2).replace('.', ',');
        $precoVenda.val(precoVenda);

        resValorTotal();
    }).on('select2:close', function () {
        let o = $item_produto.select2('data')[0];
        // se não selecionou nenhum produto, volta para o campo de qtde
        if (typeof o === 'undefined') {
            $qtde.focus();
        }
    }).select2('open');


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

        // if (!$item_produto.find("option[value='" + data.id + "']").length) {
        //     let newOption = new Option(dados.itemProdutoNome, dados.itemProdutoId, false, false);
        //     $item_produto.append(newOption);
        // }
        // $item_produto.val(dados.itemProdutoId).trigger('change');

        if (!$unidade.find("option[value='" + dados.itemUnidadeId + "']").length) {
            $unidade.append(new Option(dados.itemUnidadeLabel, dados.itemUnidadeId, false, false));
        }
        $unidade.val(dados.itemUnidadeId).trigger('change');

        $devolucao.prop('checked', dados.itemDevolucao === 1);

        $qtde.focus();


        // Remonta o select2 do produto já com o produto selecionado
        // (necessário para que possa acessar os dados referentes a preço atacado/varejo, unidade, etc no resValorTotal()
        $.ajax({
                dataType: "json",
                url: Routing.generate('ven_venda_findProdutoById') + '?term=' + dados.itemProdutoId,
                type: 'GET'
            }
        ).done(function (data) {
            let results = data.results;
            $item_produto.empty().trigger("change");
            $item_produto.select2({
                    data: results,
                    dropdownAutoWidth: true,
                    width: '100%'
                }
            );
            $item_produto.val(dados.itemProdutoId).trigger('change');
            $item_produto.prop('disabled', true);
            resValorTotal();
        });

    });

    $unidade.select2({
        width: '100%',
        dropdownAutoWidth: true,
        placeholder: '...',
        allowClear: true,
        tags: true,
        data: $unidade.data('options')
    }).on('select2:select', function () {
        let o = $unidade.select2('data')[0];

        $qtde.removeClass();
        $qtde.addClass('form-control').addClass('crsr-dec' + o.casas_decimais);
        $('#item_unidade_append_label').html(o.text);
        let precoVenda = parseFloat(o.preco_prazo).toFixed(2).replace('.', ',');
        $precoVenda.val(precoVenda);
        resValorTotal();
    });


    if ($item_produto.hasClass('focusOnReady')) {
        $item_produto.select2('focus');
    }



    function createSortableItens() {
        if ($('#tbodySortableItens').length) {
            Sortable.create(tbodySortableItens,
                {
                    animation: 150,
                    onEnd:
                        function (/**Event*/evt) {
                            let ids = '';
                            $('#tbodySortableItens > tr').each(function () {
                                ids += $(this).data('id') + ',';
                            });

                            $.ajax({
                                    dataType: "json",
                                    data: {'ids': ids},
                                    url: Routing.generate('ven_venda_saveOrdemItens'),
                                    type: 'POST'
                                }
                            ).done(function (data) {
                                if (data.result === 'OK') {
                                    toastrr.success('Itens ordenados com sucesso');

                                    $.each(data.ids, function (id, ordem) {
                                        $('#tbodySortableItens > tr[data-id="' + id + '"] > td[id="ordem"]').html(ordem);
                                    });

                                } else {
                                    toastrr.error('Erro ao ordenar itens');
                                }
                            });
                        }
                });
        }
    }


    let k = hotkeys.noConflict();
    k('ctrl+1', function (event, handler) {
        event.preventDefault();
        $('#aDados')[0].click();
    });
    k('ctrl+2', function (event, handler) {
        event.preventDefault();
        $('#aItens')[0].click();
    });
    k('ctrl+3', function (event, handler) {
        event.preventDefault();
        $('#aPagto')[0].click();
    });
    k('ctrl+4', function (event, handler) {
        event.preventDefault();
        $('#aResumo')[0].click();
    });


    createSortableItens();


});

