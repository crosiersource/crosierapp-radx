'use strict';

import Moment from 'moment';


import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';


import $ from "jquery";

Numeral.locale('pt-br');

let listId = "#movimentacaoList";

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e',
            title: 'Id',
            render: function (data, type, row) {
                let str = data.id;
                str += '<br /><input type="checkbox" class="movSel" style="width:17px;height:17px" name="movsSelecionadas[' + data.id + ']" />';
                return str;
            },
            className: 'text-center'
        },
        {
            name: 'e.carteira',
            data: 'e',
            title: 'Carteira<br />Categoria<br />Modo',
            render: function (data, type, row) {
                let str = '<b>' + data.carteira.descricaoMontada + '</b>';
                str += '<br />' + data.categoria.descricaoMontada;
                str += '<br />' + data.modo.descricaoMontada;

                return str;
            },
        },
        {
            name: 'e.descricao',
            data: 'e',
            title: 'Descrição<br/>Cedente',
            render: function (data, type, row) {

                let sLeft = "<b>" + data.descricaoMontada + "</b>";
                if (data.categoria.codigoSuper === 1 && data.sacado) {
                    sLeft += '<br /><small>' + data.sacado + '</small>';
                }
                if (data.categoria.codigoSuper === 2 && data.cedente) {
                    sLeft += '<br /><small>' + data.cedente + '</small>';
                }

                let sRight = '';

                if (data.chequeNumCheque) {
                    sRight += '<span class="badge badge-pill badge-danger">Cheque</span><br /> ';
                }


                if (data.recorrente) {
                    sRight += '<span class="badge badge-pill badge-info">Recorrente</span><br /> ';

                } else if (data.parcelamento) {
                    let routeParcelamento = Routing.generate('movimentacao_listCadeia', {'cadeia': data.cadeia.id});
                    sRight += '<span class="badge badge-pill badge-info">Parcelamento</span><br /> ';
                    sRight += ' <a href="' + routeParcelamento + '" class="btn btn-sm btn-outline-secondary" ' +
                        'role="button" target="_blank" aria-pressed="true"><i class="fas fa-ellipsis-h" aria-hidden="true"></i></a><br />';
                } else if (data.cadeia) {
                    let routeCadeia = Routing.generate('movimentacao_listCadeia', {'cadeia': data.cadeia.id});
                    sRight += '<span class="badge badge-pill badge-info">Em cadeia</span>';
                    sRight += ' <a href="' + routeCadeia + '" class="btn btn-sm btn-outline-secondary" ' +
                        'role="button" target="_blank" aria-pressed="true"><i class="fas fa-ellipsis-h" aria-hidden="true"></i></a><br />';


                    if (data.transferenciaEntreCarteiras && data.movimentacaoOposta && data.movimentacaoOposta.categoria) {
                        sRight += '<span class="badge badge-pill badge-light">' +
                            (data.movimentacaoOposta.categoria.codigo == 199 ? '<i class="fas fa-sign-out-alt"></i> Para: ' : '<i class="fas fa-sign-in-alt"></i> De: ') +

                            data.movimentacaoOposta.carteira.descricaoMontada + '</span>';
                    }

                }
                return '<div style="float: left;">' + sLeft + '</div><div class="text-right">' + sRight + '</div>';
            }
        },
        {
            name: 'e.dtUtil',
            data: 'e',
            title: 'Data',
            render: function (data, type, row) {
                let r = '<span title="Dt Vencto: ' + Moment(data.dtVencto).format('DD/MM/YYYY') + '">' +
                    Moment(data.dtUtil).format('DD/MM/YYYY') +
                    '</span><br />';

                if (data.status === 'REALIZADA') {
                    r += '<span class="badge badge-pill badge-success" style="width: 82px">' +
                        '<i class="fas fa-check-double" title="Movimentação realizada"></i> Realizada</span>';
                } else {
                    r += ' <span class="badge badge-pill badge-success" style="width: 82px">' +
                        '<i class="fas fa-hourglass-half" title="Movimentação abera"></i> Aberta</span>';
                }
                return r;
            },
            className: 'text-center'

        },
        {
            name: 'e.valorTotal',
            data: 'e',
            title: 'Valor',
            render: function (data, type, row) {
                let val = parseFloat(data.valorTotal);
                let styleId = new String(data.categoria.descricaoMontada).charAt(0) === '1' ? 'linhaValorPositivo' : 'linhaValorNegativo';
                return '<span id="' + styleId + '">' + Numeral(val).format('$ 0.0,[00]') + '</span>';
            },
            className: 'text-right'
        },
        {
            name: 'e.updated',
            data: 'e',
            title: '',
            render: function (data, type, row) {
                let colHtml = "";

                if (data.status === 'ABERTA') {
                    let pagUrl = Routing.generate('movimentacao_form_pagto', {id: data.id});
                    colHtml += '<a role="button" class="btn btn-warning btn-sm" href="' + pagUrl + '" title="Registro de Pagamento">' +
                        '<i class="fas fa-dollar-sign"></i>&nbsp;</a> ';
                }

                if ($(listId).data('routeedit')) {
                    let routeedit = Routing.generate($(listId).data('routeedit'), {id: data.id});
                    colHtml += DatatablesJs.makeEditButton(routeedit);
                }
                if ($(listId).data('routedelete')) {
                    let deleteUrl = Routing.generate($(listId).data('routedelete'), {id: data.id});
                    let csrfTokenDelete = $(listId).data('crsf-token-delete');
                    colHtml += DatatablesJs.makeDeleteButton(deleteUrl, csrfTokenDelete);
                }

                colHtml += '<br /><span class="badge badge-pill badge-info">' + Moment(data.updated).format('DD/MM/YYYY HH:mm:ss') + '</span> ';

                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

$(document).ready(function () {

    DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());

    let datatables = $(listId);


    datatables.on('draw.dt', function () {
        $('span#linhaValorPositivo').css('color', 'white').parent().css('background-color', 'dodgerblue');
        $('span#linhaValorNegativo').css('color', 'white').parent().css('background-color', 'indianred');
    });


    let $selTodasMovs = $('#selTodasMovs');
    let $btnAlterarEmLote = $('#btnAlterarEmLote');
    let $btnImprimirFichas = $('#btnImprimirFichas');

    $selTodasMovs.click(function () {
        $('.movSel').not(this).prop('checked', this.checked);
    });

    $btnAlterarEmLote.click(function () {
        let url = $(this).data('url');
        let form = $('<form>').attr("method", "post").attr(
            "action", url);
        form.append($('.movSel'));
        // simuland o clique
        form.append('<input type="hidden" name="btnAlterarEmLote" value="1" />');
        $(form).appendTo('body').submit();

    });


    $btnImprimirFichas.click(function () {
        let url = $(this).data('url');
        let form = $('<form target="_blank">').attr("method", "post").attr(
            "action", url);
        form.append($('.movSel').clone());
        // simuland o clique
        form.append('<input type="hidden" name="btnAlterarEmLote" value="1" />');
        $(form).appendTo('body').submit();

    });


});

