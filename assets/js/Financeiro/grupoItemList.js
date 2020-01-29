'use strict';

import Moment from "moment";
import Numeral from "numeral";
import $ from "jquery";

let listId = "#grupoItemList";


function getDatatablesColumns() {
    return [
        {
            name: 'e.id',
            data: 'e.id',
            title: 'Id'
        },
        {
            name: 'e.descricao',
            data: 'e.descricao',
            title: 'Descrição'
        },
        {
            name: 'e.dtVencto',
            data: 'e.dtVencto',
            title: 'Dt Vencto',
            render: function (data, type, row) {
                return Moment.unix(data.timestamp).format('DD/MM/YYYY');
            },
            className: 'text-center'

        },
        {
            name: 'e.valorInformado',
            data: 'e.valorInformado',
            title: 'Valor Inf',
            render: function (data, type, row) {
                let val = parseFloat(data);
                return Numeral(val).format('$ 0.0,[00]');
            }
        },
        {
            name: 'e.fechado',
            data: 'e.fechado',
            title: 'Fechado',
            render: function (data, type, row) {
                return data ? 'SIM' : 'NÃO';
            }
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
                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());



