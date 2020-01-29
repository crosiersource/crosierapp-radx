'use strict';

import Moment from "moment";
import Numeral from "numeral";
import 'numeral/locales/pt-br.js';
Numeral.locale('pt-br');


let listId = "#registroConferenciaList";

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
            name: 'e.dtRegistro',
            data: 'e.dtRegistro',
            title: 'Dt Registro',
            render: function (data, type, row) {
                return Moment(data).format('DD/MM/YYYY');
            },
            className: 'text-center'

        },
        {
            name: 'c.descricao',
            data: 'e.carteira',
            title: 'Carteira',
            render: function (data, type, row) {
                return data ? data.descricaoMontada : null;
            }
        },
        {
            name: 'e.valor',
            data: 'e.valor',
            title: 'Valor',
            render: function (data, type, row) {
                return Numeral(parseFloat(data)).format('$ 0.0,[00]')
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
                return colHtml;
            },
            className: 'text-right'
        }
    ];
}

DatatablesJs.makeDatatableJs(listId, getDatatablesColumns());