/* eslint-disable */

import Moment from 'moment';
import 'moment/locale/pt-br';


import Numeral from 'numeral';
import 'numeral/locales/pt-br.js';
Numeral.locale('pt-br');



let listId = "#carteiraList";

function getDatatablesColumns() {
    return [
        {
            name: 'e.codigo',
            data: 'e.codigo',
            title: 'Código'
        },
        {
            name: 'e.descricao',
            data: 'e.descricao',
            title: 'Descrição'
        },
        {
            name: 'e.atual',
            data: 'e.atual',
            title: 'Atual',
            render: function (data, type, row) {
                return data ? 'Sim' : 'Não';
            },
            className: 'text-center'
        },
        {
            name: 'e.dtConsolidado',
            data: 'e.dtConsolidado',
            title: 'Dt Consolidado',
            render: function (data, type, row) {
                return Moment.utc(data, Moment.ISO_8601, true).format('DD/MM/YYYY');
            },
            className: 'text-center'

        },
        {
            name: 'e.limite',
            data: 'e.limite',
            title: 'Limite',
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

