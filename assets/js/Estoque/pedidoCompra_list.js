'use strict';

import Moment from 'moment';

import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';
Numeral.locale('pt-br');


let listId = "#pedidoCompra_list";

function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'ID'
        },
        {
            name: 'e.dtEmissao',
            data: 'e.dtEmissao',
            title: 'Dt Emissão',
            render: function (data, type, row) {
                return data ? Moment(data, Moment.ISO_8601, true).format('DD/MM/YYYY HH:mm:ss') : null;
            },
            className: 'text-center'

        },
        {
            name: 'e.clienteNome',
            data: 'e',
            title: 'Cliente',
            render: function (data, type, row) {
                return data.fornecedor.nome
            }
        },
        {
            name: 'e.total',
            data: 'e',
            title: 'Total',
            render: function (data, type, row) {
                let val = parseFloat(data.total);
                return Numeral(val).format('$ 0.0,[00]');
            },
            className: 'text-right'
        },
        {
            name: 'e.id',
            data: 'e',
            title: '',
            render: function (data, type, row) {
                let colHtml = "";
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


DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());

